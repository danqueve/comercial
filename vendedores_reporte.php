<?php
// vendedores_reporte.php
require_once 'lib/config.php';
require_once 'lib/functions.php';
check_login();

if (!in_array($_SESSION['user_rol'], ['supervisor', 'superusuario'])) {
    redirect('dashboard.php');
}

$fecha_desde = $_POST['fecha_desde'] ?? date('Y-m-d', strtotime('-30 days'));
$fecha_hasta = $_POST['fecha_hasta'] ?? date('Y-m-d');

$reporte = get_vendedores_report($pdo, $fecha_desde, $fecha_hasta);

include 'partials/header.php';
?>

<style>
    /* Agrega un cursor de puntero a las filas para indicar que son clickeables */
    .clickable-row {
        cursor: pointer;
    }
</style>

<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <h2 class="text-2xl font-bold text-gray-200 mb-4">Reporte de Actividad de Vendedores</h2>
        
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
            <div>
                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300 inline-flex items-center">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
            </div>
        </form>

        <!-- Tabla de Reporte -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vendedor</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Total Cargadas <i class="fas fa-sort-down"></i></th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">En Revisión</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Aprobadas</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Rechazadas</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($reporte)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">No hay vendedores o no se encontraron datos en el rango de fechas seleccionado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reporte as $vendedor): ?>
                            <tr class="hover:bg-gray-700/50 clickable-row" onclick="window.location='vendedor_detalle_reporte.php?id=<?= $vendedor['id'] ?>&desde=<?= $fecha_desde ?>&hasta=<?= $fecha_hasta ?>'">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200">
                                    <?= htmlspecialchars($vendedor['apellido'] . ', ' . $vendedor['nombre']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 text-sm font-bold text-white bg-blue-600 rounded-full"><?= $vendedor['cargadas'] ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-900/70 text-yellow-300"><?= $vendedor['en_revision'] ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/70 text-green-300"><?= $vendedor['aprobadas'] ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-900/70 text-red-300"><?= $vendedor['rechazadas'] ?></span>
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