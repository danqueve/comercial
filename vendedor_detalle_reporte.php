<?php
// vendedor_detalle_reporte.php
require_once 'lib/config.php';
require_once 'lib/functions.php';
check_login();

// Seguridad: solo supervisores y superusuario pueden acceder
if (!in_array($_SESSION['user_rol'], ['supervisor', 'superusuario'])) {
    redirect('dashboard.php');
}

// --- OBTENER PARÁMETROS DE LA URL ---
$vendedor_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$fecha_desde = filter_input(INPUT_GET, 'desde', FILTER_SANITIZE_STRING);
$fecha_hasta = filter_input(INPUT_GET, 'hasta', FILTER_SANITIZE_STRING);

// Validar que todos los parámetros necesarios estén presentes
if (!$vendedor_id || !$fecha_desde || !$fecha_hasta) {
    redirect('vendedores_reporte.php');
}

// Obtener los datos del vendedor para mostrar su nombre
$vendedor = get_user_by_id($pdo, $vendedor_id);
if (!$vendedor) {
    redirect('vendedores_reporte.php');
}

// Obtener el detalle de las ventas
$ventas_detalle = get_sales_details_for_vendor_report($pdo, $vendedor_id, $fecha_desde, $fecha_hasta);

include 'partials/header.php';
?>

<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <div class="border-b border-gray-700 pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-200">
                Detalle de Ventas de: <span class="text-blue-400"><?= htmlspecialchars($vendedor['apellido'] . ', ' . $vendedor['nombre']) ?></span>
            </h2>
            <p class="text-sm text-gray-400 mt-1">
                Mostrando ventas cargadas entre el <strong><?= date('d/m/Y', strtotime($fecha_desde)) ?></strong> y el <strong><?= date('d/m/Y', strtotime($fecha_hasta)) ?></strong>.
            </p>
        </div>
        
        <!-- Tabla de Detalle de Ventas -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <!-- =================================================================== -->
                    <!-- == INICIO DEL CAMBIO: Actualizar las columnas de la tabla        == -->
                    <!-- =================================================================== -->
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Apellido y Nombre</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Domicilio</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">WhatsApp</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Artículo de Venta</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha de Carga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Estado</th>
                    </tr>
                    <!-- =================================================================== -->
                    <!-- == FIN DEL CAMBIO == -->
                    <!-- =================================================================== -->
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($ventas_detalle)): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Este vendedor no cargó ventas en el rango de fechas seleccionado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ventas_detalle as $venta): ?>
                            <tr class="hover:bg-gray-700/50">
                                <!-- =================================================================== -->
                                <!-- == INICIO DEL CAMBIO: Mostrar los nuevos datos                 == -->
                                <!-- =================================================================== -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($venta['cliente_apellido_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['cliente_domicilio']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['cliente_whatsapp']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['articulo_venta'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= date('d/m/Y H:i', strtotime($venta['fecha_creacion'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $estado = htmlspecialchars($venta['estado']);
                                        $badge_classes = 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full';
                                        if ($estado == 'aprobado') $badge_classes .= ' bg-green-900/70 text-green-300';
                                        elseif ($estado == 'rechazado') $badge_classes .= ' bg-red-900/70 text-red-300';
                                        else $badge_classes .= ' bg-yellow-900/70 text-yellow-300';
                                    ?>
                                    <span class="<?= $badge_classes ?>"><?= ucfirst($estado) ?></span>
                                </td>
                                <!-- =================================================================== -->
                                <!-- == FIN DEL CAMBIO == -->
                                <!-- =================================================================== -->
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="bg-gray-900/50 border-t border-gray-700 p-4 text-right">
        <a href="vendedores_reporte.php" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i>Volver al Reporte General
        </a>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
