<?php
// --- LÓGICA DE LA VISTA PARA AGREGAR CLIENTES Y CRÉDITOS ---

$error = '';
$success = '';
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y limpiar los datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    
    $zona = filter_input(INPUT_POST, 'zona', FILTER_VALIDATE_INT);
    $dia_pago = $_POST['dia_pago'] ?? '';
    $total_cuotas = filter_input(INPUT_POST, 'total_cuotas', FILTER_VALIDATE_INT);
    $monto_cuota = filter_input(INPUT_POST, 'monto_cuota', FILTER_VALIDATE_FLOAT);
    $ultimo_pago = $_POST['ultimo_pago'] ?? null; // Fecha de inicio del crédito

    // --- Validaciones ---
    if (empty($nombre) || empty($zona) || empty($dia_pago) || empty($total_cuotas) || empty($monto_cuota)) {
        $error = "Los campos de nombre, zona, día de pago, total de cuotas y monto son obligatorios.";
    } else {
        try {
            // Usamos una transacción para asegurar la integridad de los datos.
            // O se insertan ambos (cliente y crédito), o no se inserta ninguno.
            $pdo->beginTransaction();

            // 1. Insertar el nuevo cliente
            $sql_cliente = "INSERT INTO clientes (nombre, telefono, direccion) VALUES (?, ?, ?)";
            $stmt_cliente = $pdo->prepare($sql_cliente);
            $stmt_cliente->execute([$nombre, $telefono, $direccion]);
            
            // Obtener el ID del cliente recién creado
            $cliente_id = $pdo->lastInsertId();

            // 2. Calcular el monto total del crédito
            $monto_total = $total_cuotas * $monto_cuota;

            // 3. Insertar el nuevo crédito asociado al cliente
            $sql_credito = "INSERT INTO creditos (cliente_id, zona, dia_pago, monto_total, total_cuotas, monto_cuota, ultimo_pago) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_credito = $pdo->prepare($sql_credito);
            $stmt_credito->execute([$cliente_id, $zona, $dia_pago, $monto_total, $total_cuotas, $monto_cuota, $ultimo_pago]);
            
            // Si todo fue bien, confirmamos los cambios en la base de datos
            $pdo->commit();
            
            // Redirigir a la lista de clientes con un mensaje de éxito
            header("Location: index.php?page=clientes&status=success");
            exit;

        } catch (PDOException $e) {
            // Si algo falla, revertimos todos los cambios
            $pdo->rollBack();
            $error = "Error al guardar el cliente: " . $e->getMessage();
        }
    }
}
?>

<h2 class="text-2xl font-bold text-gray-200 mb-4">Agregar Nuevo Cliente y Crédito</h2>

<?php if(!empty($error)): ?>
    <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded-md mb-4" role="alert">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="bg-gray-800 p-6 rounded-lg border border-gray-700">
    <form action="index.php?page=agregar_cliente" method="POST">
        <!-- Sección de Datos del Cliente -->
        <fieldset class="border border-gray-600 p-4 rounded-md mb-6">
            <legend class="px-2 text-lg font-semibold text-gray-300">Datos Personales</legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-300">Nombre Completo <span class="text-red-500">*</span></label>
                    <input type="text" id="nombre" name="nombre" required class="mt-1 block w-full rounded-md form-element-dark">
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-300">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" class="mt-1 block w-full rounded-md form-element-dark">
                </div>
                <div class="md:col-span-2">
                    <label for="direccion" class="block text-sm font-medium text-gray-300">Dirección</label>
                    <input type="text" id="direccion" name="direccion" class="mt-1 block w-full rounded-md form-element-dark">
                </div>
            </div>
        </fieldset>

        <!-- Sección de Datos del Crédito -->
        <fieldset class="border border-gray-600 p-4 rounded-md">
            <legend class="px-2 text-lg font-semibold text-gray-300">Datos del Crédito</legend>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="zona" class="block text-sm font-medium text-gray-300">Zona <span class="text-red-500">*</span></label>
                    <select id="zona" name="zona" required class="mt-1 block w-full rounded-md form-element-dark">
                        <option value="">Seleccione...</option>
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?= $i ?>">Zona <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label for="dia_pago" class="block text-sm font-medium text-gray-300">Día de Pago <span class="text-red-500">*</span></label>
                    <select id="dia_pago" name="dia_pago" required class="mt-1 block w-full rounded-md form-element-dark">
                        <option value="">Seleccione...</option>
                        <?php foreach ($dias_semana as $d): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="total_cuotas" class="block text-sm font-medium text-gray-300">Total de Cuotas <span class="text-red-500">*</span></label>
                    <input type="number" id="total_cuotas" name="total_cuotas" required class="mt-1 block w-full rounded-md form-element-dark">
                </div>
                <div>
                    <label for="monto_cuota" class="block text-sm font-medium text-gray-300">Monto de Cuota <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" id="monto_cuota" name="monto_cuota" required class="mt-1 block w-full rounded-md form-element-dark">
                </div>
                 <div>
                    <label for="ultimo_pago" class="block text-sm font-medium text-gray-300">Fecha de Inicio (Primer Pago)</label>
                    <input type="date" id="ultimo_pago" name="ultimo_pago" class="mt-1 block w-full rounded-md form-element-dark">
                </div>
            </div>
        </fieldset>

        <!-- Botones de Acción -->
        <div class="mt-6 flex justify-end gap-4">
            <a href="index.php?page=clientes" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-300">
                <i class="fas fa-save mr-2"></i>Guardar Cliente
            </button>
        </div>
    </form>
</div>
