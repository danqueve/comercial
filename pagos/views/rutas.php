<?php
// --- LÓGICA DE LA VISTA DE RUTAS CON LÓGICA DE PAGO AVANZADA ---

$error = '';
$success = '';

// --- PROCESAR REGISTRO DE PAGO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_pago'])) {
    
    $credito_id = $_POST['credito_id'];
    $fecha_pago = $_POST['fecha_pago'];
    $monto_cobrado = filter_input(INPUT_POST, 'monto_cobrado', FILTER_VALIDATE_FLOAT);

    if (empty($monto_cobrado) || $monto_cobrado <= 0) {
        $error = "Debe ingresar un monto válido para registrar el pago.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Obtenemos los datos actuales del crédito.
            $stmt_check = $pdo->prepare("SELECT cuotas_pagadas, total_cuotas, monto_cuota, saldo_cuota_actual FROM creditos WHERE id = ? FOR UPDATE");
            $stmt_check->execute([$credito_id]);
            $credito = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($credito) {
                $monto_restante_pago = $monto_cobrado;
                $nuevas_cuotas_pagadas = $credito['cuotas_pagadas'];
                $nuevo_saldo_cuota = $credito['saldo_cuota_actual'];

                // 2. Bucle para procesar el pago a través de una o más cuotas.
                while ($monto_restante_pago > 0 && $nuevas_cuotas_pagadas < $credito['total_cuotas']) {
                    // Determinar el saldo a pagar de la cuota actual.
                    $saldo_a_pagar_esta_cuota = ($nuevo_saldo_cuota > 0) ? $nuevo_saldo_cuota : $credito['monto_cuota'];

                    if ($monto_restante_pago >= $saldo_a_pagar_esta_cuota) {
                        // El pago cubre esta cuota por completo.
                        $monto_restante_pago -= $saldo_a_pagar_esta_cuota;
                        $nuevas_cuotas_pagadas++;
                        $nuevo_saldo_cuota = 0; // La cuota está saldada.
                    } else {
                        // El pago es parcial para esta cuota.
                        $nuevo_saldo_cuota = $saldo_a_pagar_esta_cuota - $monto_restante_pago;
                        $monto_restante_pago = 0; // Todo el monto se usó.
                    }
                }
                
                // Si después del bucle aún queda dinero (saldo a favor), se aplica a la siguiente cuota.
                if ($monto_restante_pago > 0 && $nuevas_cuotas_pagadas >= $credito['total_cuotas']) {
                    // El crédito ya está pagado, pero hay un excedente.
                    $nuevo_saldo_cuota = 0;
                } elseif ($monto_restante_pago > 0) {
                    // El crédito no está pagado y hay saldo a favor para la siguiente cuota.
                    $nuevo_saldo_cuota = $credito['monto_cuota'] - $monto_restante_pago;
                }

                $nuevo_estado = ($nuevas_cuotas_pagadas >= $credito['total_cuotas']) ? 'Pagado' : 'Activo';
                $fecha_a_registrar = !empty($fecha_pago) ? $fecha_pago : date('Y-m-d');

                // Insertar el registro en la tabla 'pagos'.
                $sql_pago = "INSERT INTO pagos (credito_id, usuario_id, monto_pagado, fecha_pago) VALUES (?, ?, ?, ?)";
                $stmt_pago = $pdo->prepare($sql_pago);
                $stmt_pago->execute([$credito_id, $_SESSION['user_id'], $monto_cobrado, $fecha_a_registrar]);
                
                // Actualizar la tabla 'creditos' con los nuevos valores.
                $sql_update = "UPDATE creditos SET 
                                cuotas_pagadas = ?, 
                                ultimo_pago = ?, 
                                estado = ?,
                                saldo_cuota_actual = ?
                               WHERE id = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$nuevas_cuotas_pagadas, $fecha_a_registrar, $nuevo_estado, $nuevo_saldo_cuota, $credito_id]);

                $pdo->commit();
                $success = "¡Pago Cargado OK!";
            } else {
                $error = "No se encontró el crédito especificado.";
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error al registrar el pago: " . $e->getMessage();
        }
    }
}

// El resto de la lógica para mostrar la página sigue aquí...
$zona_seleccionada = $_GET['zona'] ?? 2;
$dia_seleccionado = $_GET['dia'] ?? 'Lunes';
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

$sql = "SELECT c.nombre, cr.* FROM creditos cr JOIN clientes c ON cr.cliente_id = c.id WHERE cr.zona = ? AND cr.dia_pago = ? ORDER BY c.nombre ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$zona_seleccionada, $dia_seleccionado]);
$clientes_filtrados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cobranza_estimada = 0;
foreach ($clientes_filtrados as $cliente) {
    if ($cliente['estado'] !== 'Pagado') {
        $saldo_pendiente_cuota = ($cliente['saldo_cuota_actual'] > 0) ? $cliente['saldo_cuota_actual'] : $cliente['monto_cuota'];
        $cobranza_estimada += $saldo_pendiente_cuota;
    }
}
?>

<!-- MENSAJES DE ÉXITO O ERROR -->
<?php if(!empty($error)): ?>
    <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded-md mb-4" role="alert"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- FORMULARIO DE FILTROS Y BOTÓN DE IMPRESIÓN -->
<div class="bg-gray-800 p-4 rounded-lg shadow-md mb-6 border border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4 no-print">
    <form method="GET" action="index.php" class="flex flex-col sm:flex-row items-center gap-4">
        <input type="hidden" name="page" value="rutas">
        <div>
            <label for="zona" class="block text-sm font-medium text-gray-300">Zona:</label>
            <select id="zona" name="zona" class="mt-1 block w-full pl-3 pr-10 py-2 text-base rounded-md form-element-dark">
                <?php for ($i = 1; $i <= 4; $i++): ?><option value="<?= $i ?>" <?= $zona_seleccionada == $i ? 'selected' : '' ?>>Zona <?= $i ?></option><?php endfor; ?>
            </select>
        </div>
        <div>
            <label for="dia" class="block text-sm font-medium text-gray-300">Día:</label>
            <select id="dia" name="dia" class="mt-1 block w-full pl-3 pr-10 py-2 text-base rounded-md form-element-dark">
                <?php foreach ($dias_semana as $d): ?><option value="<?= $d ?>" <?= $dia_seleccionado == $d ? 'selected' : '' ?>><?= $d ?></option><?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="w-full sm:w-auto mt-4 sm:mt-0 self-end bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md"><i class="fas fa-filter"></i> Filtrar</button>
    </form>
    <a href="views/imprimir_planilla.php?zona=<?= $zona_seleccionada ?>&dia=<?= $dia_seleccionado ?>" target="_blank" class="w-full sm:w-auto bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md text-center">
        <i class="fas fa-print mr-2"></i>Imprimir Planilla
    </a>
</div>

<!-- TARJETAS DE RESUMEN -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 no-print">
    <div class="summary-card"><h3 class="text-lg font-medium text-gray-400">Cobranza Estimada</h3><p id="total-estimado" data-valor="<?= $cobranza_estimada ?>" class="text-3xl font-bold text-gray-100 mt-2">$0</p></div>
    <div class="summary-card"><h3 class="text-lg font-medium text-gray-400">Total Cobrado</h3><p id="total-cobrado" class="text-3xl font-bold text-green-400 mt-2">$0</p></div>
    <div class="summary-card"><h3 class="text-lg font-medium text-gray-400">Faltante</h3><p id="total-faltante" class="text-3xl font-bold text-red-400 mt-2">$0</p></div>
</div>

<!-- TABLA DE COBROS -->
<div class="overflow-x-auto rounded-lg shadow border border-gray-700">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="table-header-custom">
             <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Cliente</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Cuotas</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Saldo Cuota</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Saldo Total</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Cuota</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Días Atraso</th>
                <th class="px-4 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider no-print">Acciones de Cobro</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            <?php if (empty($clientes_filtrados)): ?>
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 table-row-dark"><i class="fas fa-folder-open fa-3x mb-3"></i><p>No hay clientes para esta ruta.</p></td></tr>
            <?php else: ?>
                <?php foreach ($clientes_filtrados as $cliente): ?>
                    <?php
                    $cuotas_restantes = $cliente['total_cuotas'] - $cliente['cuotas_pagadas'];
                    $saldo_pendiente_cuota = ($cliente['saldo_cuota_actual'] > 0) ? $cliente['saldo_cuota_actual'] : $cliente['monto_cuota'];
                    $saldo_total_credito = ($cuotas_restantes > 0) ? ($cliente['monto_cuota'] * ($cuotas_restantes - 1)) + $saldo_pendiente_cuota : 0;
                    $atraso = calcularAtraso($cliente['ultimo_pago'], $cliente['estado']);
                    ?>
                    <tr class="table-row-dark">
                        <td class="px-4 py-4 whitespace-nowrap font-medium text-gray-100"><?= htmlspecialchars($cliente['nombre']) ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-gray-300"><?= $cliente['cuotas_pagadas'] ?> / <?= $cliente['total_cuotas'] ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-right font-semibold text-yellow-400"><?= formatCurrency($saldo_pendiente_cuota) ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-right font-semibold text-blue-400"><?= formatCurrency($saldo_total_credito) ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-right text-gray-300"><?= formatCurrency($cliente['monto_cuota']) ?></td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <?php if ($atraso['estado'] == 'Pagado'): ?><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900 text-green-300">Pagado</span>
                            <?php elseif ($atraso['estado'] == 'Atrasado'): ?><span class="late-payment"><?= $atraso['dias'] ?> días</span>
                            <?php else: ?><span class="on-time">Al día</span><?php endif; ?>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap no-print">
                            <?php if ($atraso['estado'] !== 'Pagado'): ?>
                                <form action="index.php?page=rutas&zona=<?= $zona_seleccionada ?>&dia=<?= $dia_seleccionado ?>" method="POST" class="flex items-center gap-2 justify-center">
                                    <input type="hidden" name="credito_id" value="<?= $cliente['id'] ?>">
                                    <input type="date" name="fecha_pago" class="w-32 rounded-md shadow-sm form-element-dark" title="Fecha de Pago">
                                    <input type="number" name="monto_cobrado" class="monto-cobrado-input w-32 rounded-md shadow-sm form-element-dark" placeholder="<?= number_format($saldo_pendiente_cuota, 0, '', '') ?>" autocomplete="off">
                                    <button type="submit" name="registrar_pago" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-md text-sm transition duration-300">
                                        <i class="fas fa-check"></i> Registrar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- POPUP DE NOTIFICACIÓN DE ÉXITO -->
<div id="success-popup" class="hidden fixed bottom-5 right-5 bg-green-600 text-white py-3 px-5 rounded-lg shadow-lg flex items-center">
    <i class="fas fa-check-circle mr-3"></i>
    <span id="popup-message"></span>
</div>

<!-- SCRIPT PARA MANEJAR EL POPUP -->
<?php if(!empty($success)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const popup = document.getElementById('success-popup');
        const messageEl = document.getElementById('popup-message');
        if (popup && messageEl) {
            messageEl.textContent = "<?= htmlspecialchars($success) ?>";
            popup.classList.remove('hidden');
            setTimeout(() => {
                popup.classList.add('hidden');
            }, 3000);
        }
    });
</script>
<?php endif; ?>
