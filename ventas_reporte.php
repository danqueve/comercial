<?php
// ventas_reporte.php
require_once 'lib/config.php';
require_once 'lib/functions.php';
check_login();

// Seguridad: solo supervisores y superusuario pueden acceder
if (!in_array($_SESSION['user_rol'], ['supervisor', 'superusuario'])) {
    redirect('dashboard.php');
}

// --- Manejo de Fechas ---
$fecha_desde = $_POST['fecha_desde'] ?? date('Y-m-d', strtotime('-30 days'));
$fecha_hasta = $_POST['fecha_hasta'] ?? date('Y-m-d');

// Se llama a la nueva función para obtener los datos del reporte
$reporte_ventas = get_sales_report_by_date($pdo, $fecha_desde, $fecha_hasta);

include 'partials/header.php';
?>

<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <h2 class="text-2xl font-bold text-gray-200 mb-4">Reporte General de Ventas</h2>
        
        <!-- Formulario para el filtro de fechas -->
        <form method="POST" class="flex flex-wrap items-end gap-4 bg-gray-900/50 p-4 rounded-lg border border-gray-700 mb-6">
            <div>
                <label for="fecha_desde" class="block text-sm font-medium text-gray-400 mb-1">Desde:</label>
                <input type="date" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde) ?>">
            </div>
            <div>
                <label for="fecha_hasta" class="block text-sm font-medium text-gray-400 mb-1">Hasta:</label>
                <input type="date" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300 inline-flex items-center"><i class="fas fa-filter mr-2"></i> Filtrar</button>
                <button type="button" class="bg-green-600 text-white font-bold py-2 px-4 rounded-md hover:bg-green-700 transition duration-300 inline-flex items-center" id="printSalesReportBtn"><i class="fas fa-print mr-2"></i> Imprimir Reporte</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">#</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vendedor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha Carga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha Estado</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Estado Final</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($reporte_ventas)): ?>
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No se encontraron ventas en el rango de fechas.</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($reporte_ventas as $venta): ?>
                            <tr class="hover:bg-gray-700/50 report-row"
                                data-id="<?= $venta['id'] ?>"
                                data-vendedor="<?= htmlspecialchars($venta['vendedor_nombre']) ?>"
                                data-cliente="<?= htmlspecialchars($venta['cliente_apellido_nombre']) ?>"
                                data-articulo="<?= htmlspecialchars($venta['articulo_venta'] ?? 'N/A') ?>"
                                data-fecha-carga="<?= date('d/m/Y H:i', strtotime($venta['fecha_creacion'])) ?>"
                                data-fecha-estado="<?= !empty($venta['fecha_actualizacion_estado']) ? date('d/m/Y H:i', strtotime($venta['fecha_actualizacion_estado'])) : 'N/A' ?>"
                                data-estado="<?= $venta['estado'] ?>"
                                data-estado-entrega="<?= $venta['estado_entrega'] ?>"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= $i++ ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['vendedor_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['cliente_apellido_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= date('d/m/Y H:i', strtotime($venta['fecha_creacion'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= !empty($venta['fecha_actualizacion_estado']) ? date('d/m/Y H:i', strtotime($venta['fecha_actualizacion_estado'])) : 'N/A' ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $estado_final = '';
                                        $badge_classes = 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full';
                                        if ($venta['estado'] == 'rechazado') { $estado_final = 'Rechazado'; $badge_classes .= ' bg-red-900/70 text-red-300'; } 
                                        elseif ($venta['estado'] == 'aprobado' && $venta['estado_entrega'] == 'entregado') { $estado_final = 'Entregado'; $badge_classes .= ' bg-green-900/70 text-green-300'; } 
                                        elseif ($venta['estado'] == 'aprobado') { $estado_final = 'Aprobado'; $badge_classes .= ' bg-blue-900/70 text-blue-300'; } 
                                        else { $estado_final = 'En Revisión'; $badge_classes .= ' bg-yellow-900/70 text-yellow-300'; }
                                    ?>
                                    <span class="<?= $badge_classes ?>"><?= $estado_final ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view_form.php?id=<?= $venta['id'] ?>" class="text-blue-400 hover:text-blue-300" title="Ver Ficha Completa"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>