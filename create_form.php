<?php
// create_form.php
require_once 'lib/config.php';
require_once 'lib/functions.php';
check_login();

if (!in_array($_SESSION['user_rol'], ['vendedor', 'supervisor'])) {
    redirect('dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear datos
    $cliente_dni = filter_input(INPUT_POST, 'cliente_dni', FILTER_SANITIZE_STRING);
    $cliente_apellido_nombre = filter_input(INPUT_POST, 'cliente_apellido_nombre', FILTER_SANITIZE_STRING);
    $cliente_domicilio = filter_input(INPUT_POST, 'cliente_domicilio', FILTER_SANITIZE_STRING);
    $cliente_localidad = filter_input(INPUT_POST, 'cliente_localidad', FILTER_SANITIZE_STRING);
    $cliente_barrio = filter_input(INPUT_POST, 'cliente_barrio', FILTER_SANITIZE_STRING);
    $cliente_whatsapp = filter_input(INPUT_POST, 'cliente_whatsapp', FILTER_SANITIZE_STRING);
    $cliente_celular_llamada = filter_input(INPUT_POST, 'cliente_celular_llamada', FILTER_SANITIZE_STRING);
    $cliente_tipo_empleo = filter_input(INPUT_POST, 'cliente_tipo_empleo', FILTER_SANITIZE_STRING);
    $cliente_domicilio_trabajo = filter_input(INPUT_POST, 'cliente_domicilio_trabajo', FILTER_SANITIZE_STRING);
    $cliente_de_que_trabaja = filter_input(INPUT_POST, 'cliente_de_que_trabaja', FILTER_SANITIZE_STRING);
    $cliente_nombre_trabajo = filter_input(INPUT_POST, 'cliente_nombre_trabajo', FILTER_SANITIZE_STRING);
    $articulo_detalles = filter_input(INPUT_POST, 'articulo_detalles', FILTER_SANITIZE_STRING);
    $articulo_venta = filter_input(INPUT_POST, 'articulo_venta', FILTER_SANITIZE_STRING);
    $financiacion = filter_input(INPUT_POST, 'financiacion', FILTER_SANITIZE_STRING);
    $fecha_entrega_deseada = filter_input(INPUT_POST, 'fecha_entrega_deseada', FILTER_SANITIZE_STRING);
    
    if (empty($fecha_entrega_deseada)) {
        $fecha_entrega_deseada = null;
    }

    if (empty($cliente_dni) || empty($cliente_apellido_nombre) || empty($cliente_whatsapp)) {
        $errors[] = 'DNI, Apellido y Nombre, y WhatsApp del cliente son obligatorios.';
    }

    if (empty($errors)) {
        $sql = "INSERT INTO formularios (vendedor_id, cliente_dni, cliente_apellido_nombre, cliente_domicilio, cliente_localidad, cliente_barrio, cliente_whatsapp, cliente_celular_llamada, cliente_tipo_empleo, cliente_domicilio_trabajo, cliente_de_que_trabaja, cliente_nombre_trabajo, articulo_detalles, articulo_venta, financiacion, fecha_entrega_deseada) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([
                $_SESSION['user_id'], $cliente_dni, $cliente_apellido_nombre, $cliente_domicilio, $cliente_localidad, $cliente_barrio, $cliente_whatsapp, $cliente_celular_llamada, $cliente_tipo_empleo, $cliente_domicilio_trabajo, $cliente_de_que_trabaja, $cliente_nombre_trabajo, $articulo_detalles, $articulo_venta, $financiacion, $fecha_entrega_deseada
            ]);
            $success = "Formulario cargado con éxito. Será revisado a la brevedad.";
        } catch (PDOException $e) {
            $errors[] = "Error al guardar el formulario: " . $e->getMessage();
        }
    }
}

include 'partials/header.php';
?>

<div class="flex justify-center">
    <div class="w-full max-w-4xl">
        <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-center text-gray-200 mb-6">Cargar Nueva Venta</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-900/50 border-l-4 border-red-500 text-red-300 p-4 mb-6" role="alert">
                    <p class="font-bold">Error</p>
                    <ul><?php foreach ($errors as $error) echo "<li class='list-disc ml-4'>" . htmlspecialchars($error) . "</li>"; ?></ul>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-900/50 border-l-4 border-green-500 text-green-300 p-4 text-center" role="alert">
                    <p class="font-bold text-lg">¡Éxito!</p>
                    <p><?= htmlspecialchars($success) ?></p>
                    <div class="mt-4">
                        <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-6">
                    <!-- Datos del Cliente -->
                    <fieldset class="border border-gray-700 p-4 rounded-lg">
                        <legend class="text-lg font-semibold text-gray-300 px-2">Datos del Cliente</legend>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div><label for="cliente_dni" class="block text-sm font-medium text-gray-400 mb-1">DNI Cliente <span class="text-red-400">*</span></label><input type="text" id="cliente_dni" name="cliente_dni" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
                            <div><label for="cliente_apellido_nombre" class="block text-sm font-medium text-gray-400 mb-1">Apellido y Nombre <span class="text-red-400">*</span></label><input type="text" id="cliente_apellido_nombre" name="cliente_apellido_nombre" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
                            <div class="md:col-span-2"><label for="cliente_domicilio" class="block text-sm font-medium text-gray-400 mb-1">Domicilio</label><input type="text" id="cliente_domicilio" name="cliente_domicilio" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label for="cliente_localidad" class="block text-sm font-medium text-gray-400 mb-1">Localidad</label><input type="text" id="cliente_localidad" name="cliente_localidad" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label for="cliente_barrio" class="block text-sm font-medium text-gray-400 mb-1">Barrio</label><input type="text" id="cliente_barrio" name="cliente_barrio" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label for="cliente_whatsapp" class="block text-sm font-medium text-gray-400 mb-1">WhatsApp <span class="text-red-400">*</span></label><input type="text" id="cliente_whatsapp" name="cliente_whatsapp" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required></div>
                            <div><label for="cliente_celular_llamada" class="block text-sm font-medium text-gray-400 mb-1">Celular para Llamadas</label><input type="text" id="cliente_celular_llamada" name="cliente_celular_llamada" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                        </div>
                    </fieldset>

                    <!-- Datos Laborales -->
                    <fieldset class="border border-gray-700 p-4 rounded-lg">
                        <legend class="text-lg font-semibold text-gray-300 px-2">Datos Laborales</legend>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div><label for="cliente_tipo_empleo" class="block text-sm font-medium text-gray-400 mb-1">Tipo de Empleo</label><input type="text" id="cliente_tipo_empleo" name="cliente_tipo_empleo" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label for="cliente_de_que_trabaja" class="block text-sm font-medium text-gray-400 mb-1">Ocupación</label><input type="text" id="cliente_de_que_trabaja" name="cliente_de_que_trabaja" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label for="cliente_nombre_trabajo" class="block text-sm font-medium text-gray-400 mb-1">Nombre del Trabajo</label><input type="text" id="cliente_nombre_trabajo" name="cliente_nombre_trabajo" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label for="cliente_domicilio_trabajo" class="block text-sm font-medium text-gray-400 mb-1">Domicilio del Trabajo</label><input type="text" id="cliente_domicilio_trabajo" name="cliente_domicilio_trabajo" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                        </div>
                    </fieldset>

                    <!-- Detalles de la Venta -->
                    <fieldset class="border border-gray-700 p-4 rounded-lg">
                        <legend class="text-lg font-semibold text-gray-300 px-2">Detalles de la Venta</legend>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <div><label for="articulo_venta" class="block text-sm font-medium text-gray-400 mb-1">Artículo de Venta</label><input type="text" id="articulo_venta" name="articulo_venta" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                            <div><label for="financiacion" class="block text-sm font-medium text-gray-400 mb-1">Financiación</label><input type="text" id="financiacion" name="financiacion" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: 12 cuotas, Contado"></div>
                        </div>
                        <div class="mt-4"><label for="fecha_entrega_deseada" class="block text-sm font-medium text-gray-400 mb-1">Fecha de Entrega Deseada</label><input type="date" id="fecha_entrega_deseada" name="fecha_entrega_deseada" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                        <div class="mt-4"><label for="articulo_detalles" class="block text-sm font-medium text-gray-400 mb-1">Observaciones / Detalles</label><textarea id="articulo_detalles" name="articulo_detalles" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"></textarea></div>
                    </fieldset>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300">Guardar Formulario</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>