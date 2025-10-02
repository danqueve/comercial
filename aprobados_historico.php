<?php
// aprobados_historico.php
require_once 'lib/config.php';
require_once 'lib/functions.php';
check_login();

// Seguridad: solo supervisores y superusuario pueden acceder
if (!in_array($_SESSION['user_rol'], ['supervisor', 'superusuario'])) {
    redirect('dashboard.php');
}

// --- LÓGICA DE PAGINACIÓN ---
$items_per_page = 15;

// 1. Contar el total de registros aprobados
$total_items_query = $pdo->query("SELECT COUNT(*) FROM formularios WHERE estado = 'aprobado'");
$total_items = $total_items_query->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// 2. Determinar la página actual
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;
if ($current_page < 1) $current_page = 1;

// 3. Calcular el OFFSET para la consulta SQL
$offset = ($current_page - 1) * $items_per_page;

// --- OBTENER LOS DATOS PAGINADOS ---
$stmt = $pdo->prepare("
    SELECT f.id, f.cliente_apellido_nombre, f.articulo_venta, f.fecha_actualizacion_estado,
           CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre
    FROM formularios f
    JOIN usuarios u ON f.vendedor_id = u.id
    WHERE f.estado = 'aprobado' 
    ORDER BY f.fecha_actualizacion_estado DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$ventas_aprobadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'partials/header.php';
?>

<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <h2 class="text-2xl font-bold text-gray-200 mb-4"><i class="fas fa-archive mr-2"></i>Historial de Ventas Aprobadas</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"># Venta</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vendedor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Artículo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha de Aprobación</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($ventas_aprobadas)): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay ventas aprobadas en el historial.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ventas_aprobadas as $venta): ?>
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($venta['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['vendedor_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['cliente_apellido_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['articulo_venta'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= date('d/m/Y H:i', strtotime($venta['fecha_actualizacion_estado'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view_form.php?id=<?= $venta['id'] ?>" class="text-blue-400 hover:text-blue-300" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- --- NAVEGACIÓN DE PAGINACIÓN CON TAILWIND (DARK MODE) --- -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Botón Anterior -->
                <a href="?page=<?= $current_page - 1 ?>" class="<?= ($current_page <= 1) ? 'pointer-events-none text-gray-500' : 'text-gray-300 hover:bg-gray-700' ?> relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-700 bg-gray-800 text-sm font-medium">
                    <span class="sr-only">Anterior</span>
                    <i class="fas fa-chevron-left h-5 w-5"></i>
                </a>
                
                <!-- Números de Página -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= ($i == $current_page) ? 'z-10 bg-blue-900/50 border-blue-500 text-blue-300' : 'bg-gray-800 border-gray-700 text-gray-400 hover:bg-gray-700' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                    <?= $i ?>
                </a>
                <?php endfor; ?>

                <!-- Botón Siguiente -->
                <a href="?page=<?= $current_page + 1 ?>" class="<?= ($current_page >= $total_pages) ? 'pointer-events-none text-gray-500' : 'text-gray-300 hover:bg-gray-700' ?> relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-700 bg-gray-800 text-sm font-medium">
                    <span class="sr-only">Siguiente</span>
                    <i class="fas fa-chevron-right h-5 w-5"></i>
                </a>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
