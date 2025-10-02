<?php
// --- LÓGICA DE LA VISTA PARA EDITAR CLIENTES Y CRÉDITOS ---

$error = '';
// El ID que recibimos ahora es el ID del crédito específico.
$credito_id = $_GET['id'] ?? null;
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

// Si no se proporciona un ID, redirigimos al listado de clientes.
if (!$credito_id) {
    header("Location: index.php?page=clientes");
    exit;
}

// --- PROCESAR EL FORMULARIO AL GUARDAR CAMBIOS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del cliente
    $cliente_id = $_POST['cliente_id'];
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    // Recoger datos del crédito
    $zona = filter_input(INPUT_POST, 'zona', FILTER_VALIDATE_INT);
    $dia_pago = $_POST['dia_pago'] ?? '';
    $cuotas_pagadas = filter_input(INPUT_POST, 'cuotas_pagadas', FILTER_VALIDATE_INT);
    $total_cuotas = filter_input(INPUT_POST, 'total_cuotas', FILTER_VALIDATE_INT);
    $monto_cuota = filter_input(INPUT_POST, 'monto_cuota', FILTER_VALIDATE_FLOAT);

    // Validación
    if (empty($nombre) || empty($zona) || empty($dia_pago) || $total_cuotas === false || $monto_cuota === false) {
        $error = "Los campos marcados con * son obligatorios.";
    } elseif ($cuotas_pagadas > $total_cuotas) {
        $error = "Las cuotas pagadas no pueden ser mayores que el total de cuotas.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Actualizar la tabla 'clientes'
            $sql_cliente = "UPDATE clientes SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?";
            $stmt_cliente = $pdo->prepare($sql_cliente);
            $stmt_cliente->execute([$nombre, $telefono, $direccion, $cliente_id]);

            // 2. Determinar el nuevo estado y monto total del crédito
            $nuevo_estado = ($cuotas_pagadas >= $total_cuotas) ? 'Pagado' : 'Activo';
            $monto_total = $total_cuotas * $monto_cuota;

            // 3. Actualizar la tabla 'creditos'
            $sql_credito = "UPDATE creditos SET zona = ?, dia_pago = ?, cuotas_pagadas = ?, total_cuotas = ?, monto_cuota = ?, monto_total = ?, estado = ? WHERE id = ?";
            $stmt_credito = $pdo->prepare($sql_credito);
            $stmt_credito->execute([$zona, $dia_pago, $cuotas_pagadas, $total_cuotas, $monto_cuota, $monto_total, $nuevo_estado, $credito_id]);

            $pdo->commit();
            header("Location: index.php?page=clientes&status=updated");
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error al actualizar los datos: " . $e->getMessage();
        }
    }
}

// --- OBTENER DATOS DEL CLIENTE Y SU CRÉDITO PARA MOSTRAR EN EL FORMULARIO ---
try {
    $sql = "SELECT c.id as cliente_id, c.nombre, c.telefono, c.direccion, 
                   cr.id as credito_id, cr.zona, cr.dia_pago, cr.cuotas_pagadas, cr.total_cuotas, cr.monto_cuota
            FROM clientes c 
            JOIN creditos cr ON c.id = cr.cliente_id
            WHERE cr.id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$credito_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        header("Location: index.php?page=clientes&status=notfound");
        exit;
    }
} catch (PDOException $e) {
    die("Error al obtener los datos del registro: " . $e->getMessage());
}
?>

<h2 class="text-2xl font-bold text-gray-200 mb-4">Editar Cliente y Crédito</h2>

<?php if(!empty($error)): ?>
    <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded-md mb-4" role="alert"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
    <form action="index.php?page=editar_cliente&id=<?= $credito_id ?>" method="POST">
        <input type="hidden" name="cliente_id" value="<?= $data['cliente_id'] ?>">

        <!-- Sección de Datos del Cliente -->
        <fieldset class="border border-gray-600 p-4 rounded-md mb-6">
            <legend class="px-2 text-lg font-semibold text-gray-300">Datos Personales</legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-300">Nombre Completo <span class="text-red-500">*</span></label>
                    <input type="text" id="nombre" name="nombre" required class="mt-1 block w-full rounded-md form-element-dark" value="<?= htmlspecialchars($data['nombre']) ?>">
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-300">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="mt-1 block w-full rounded-md form-element-dark" value="<?= htmlspecialchars($data['telefono']) ?>">
                </div>
                <div class="md:col-span-2">
                    <label for="direccion" class="block text-sm font-medium text-gray-300">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="mt-1 block w-full rounded-md form-element-dark" value="<?= htmlspecialchars($data['direccion']) ?>">
                </div>
            </div>
        </fieldset>

        <!-- Sección de Datos del Crédito -->
        <fieldset class="border border-gray-600 p-4 rounded-md">
            <legend class="px-2 text-lg font-semibold text-gray-300">Datos del Crédito</legend>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="zona" class="block text-sm font-medium text-gray-300">Zona <span class="text-red-500">*</span></label>
                    <select id="zona" name="zona" required class="mt-1 block w-full rounded-md form-element-dark">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?= $i ?>" <?= ($data['zona'] == $i) ? 'selected' : '' ?>>Zona <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="dia_pago" class="block text-sm font-medium text-gray-300">Día de Pago <span class="text-red-500">*</span></label>
                    <select id="dia_pago" name="dia_pago" required class="mt-1 block w-full rounded-md form-element-dark">
                        <?php foreach ($dias_semana as $d): ?>
                            <option value="<?= $d ?>" <?= (ucfirst($data['dia_pago']) == $d) ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="monto_cuota" class="block text-sm font-medium text-gray-300">Monto de Cuota <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" id="monto_cuota" name="monto_cuota" required class="mt-1 block w-full rounded-md form-element-dark" value="<?= htmlspecialchars($data['monto_cuota']) ?>">
                </div>
                <div>
                    <label for="cuotas_pagadas" class="block text-sm font-medium text-gray-300">Cuotas Abonadas</label>
                    <input type="number" id="cuotas_pagadas" name="cuotas_pagadas" class="mt-1 block w-full rounded-md form-element-dark" value="<?= htmlspecialchars($data['cuotas_pagadas']) ?>">
                </div>
                <div>
                    <label for="total_cuotas" class="block text-sm font-medium text-gray-300">Total de Cuotas <span class="text-red-500">*</span></label>
                    <input type="number" id="total_cuotas" name="total_cuotas" required class="mt-1 block w-full rounded-md form-element-dark" value="<?= htmlspecialchars($data['total_cuotas']) ?>">
                </div>
            </div>
        </fieldset>

        <!-- Botones de Acción -->
        <div class="mt-6 flex justify-end gap-4">
            <a href="index.php?page=clientes" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300"><i class="fas fa-save mr-2"></i>Guardar Cambios</button>
        </div>
    </form>
</div>
