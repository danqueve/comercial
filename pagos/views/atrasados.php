<?php
// --- LÓGICA DE LA VISTA DE CLIENTES ATRASADOS ---

$zona_seleccionada = $_GET['zona'] ?? 'all';

// --- LÓGICA DE ORDENAMIENTO ---
$sort_options = ['nombre', 'zona', 'ultimo_pago', 'dias_atraso'];
$sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $sort_options) ? $_GET['sort_by'] : 'dias_atraso';
$sort_order = isset($_GET['sort_order']) && in_array(strtoupper($_GET['sort_order']), ['ASC', 'DESC']) ? strtoupper($_GET['sort_order']) : 'DESC';

// --- LÓGICA DE PAGINACIÓN ---
$limit_options = [10, 15, 25, 30]; // Opciones de paginación
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 10;
$page_num = isset($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

try {
    // --- CONSULTA PARA CONTAR EL TOTAL DE REGISTROS ATRASADOS (ACTUALIZADA) ---
    // Se añade la condición para mostrar solo los que tienen más de 12 días de atraso.
    $sql_count = "SELECT COUNT(cr.id) FROM creditos cr WHERE cr.estado = 'Activo' AND cr.ultimo_pago IS NOT NULL AND DATE_ADD(cr.ultimo_pago, INTERVAL 7 DAY) < CURDATE() AND DATEDIFF(CURDATE(), DATE_ADD(cr.ultimo_pago, INTERVAL 7 DAY)) > 12";
    $params_count = [];
    if ($zona_seleccionada != 'all') {
        $sql_count .= " AND cr.zona = :zona";
        $params_count[':zona'] = $zona_seleccionada;
    }
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params_count);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // --- CONSULTA PARA OBTENER LOS CLIENTES ATRASADOS (ACTUALIZADA) ---
    $sql = "SELECT c.nombre, c.telefono, cr.zona, cr.ultimo_pago, 
                   DATEDIFF(CURDATE(), DATE_ADD(cr.ultimo_pago, INTERVAL 7 DAY)) as dias_atraso
            FROM creditos cr
            JOIN clientes c ON cr.cliente_id = c.id
            WHERE cr.estado = 'Activo' AND cr.ultimo_pago IS NOT NULL AND DATE_ADD(cr.ultimo_pago, INTERVAL 7 DAY) < CURDATE() AND DATEDIFF(CURDATE(), DATE_ADD(cr.ultimo_pago, INTERVAL 7 DAY)) > 12";
    
    if ($zona_seleccionada != 'all') {
        $sql .= " AND cr.zona = :zona";
    }
    // Añadimos el ordenamiento dinámico a la consulta
    $sql .= " ORDER BY $sort_by $sort_order LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    
    if ($zona_seleccionada != 'all') {
        $stmt->bindValue(':zona', $zona_seleccionada, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $atrasados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al obtener los clientes atrasados: " . $e->getMessage());
}

// Función para generar los enlaces de ordenamiento
function sort_link($column, $text, $current_sort, $current_order) {
    $order = ($current_sort == $column && $current_order == 'ASC') ? 'DESC' : 'ASC';
    $icon = '';
    if ($current_sort == $column) {
        $icon = $current_order == 'ASC' ? '<i class="fas fa-arrow-up ml-1"></i>' : '<i class="fas fa-arrow-down ml-1"></i>';
    }
    // Se añaden los parámetros de paginación al enlace de ordenamiento para mantener el estado
    $limit_param = isset($_GET['limit']) ? "&limit=" . $_GET['limit'] : "";
    $zona_param = isset($_GET['zona']) ? "&zona=" . $_GET['zona'] : "";
    return "<a href='?page=atrasados&sort_by=$column&sort_order=$order$limit_param$zona_param'>$text $icon</a>";
}
?>

<!-- ESTILOS PARA LA IMPRESIÓN -->
<style>
    @media print {
        .no-print { display: none !important; }
        body { background-color: white !important; color: black !important; padding: 20px !important; margin: 0 !important; }
        .print-area { box-shadow: none !important; border: none !important; }
        .print-container { background-color: white !important; border: none !important; padding: 0 !important; color: black !important; }
        .print-title { color: black !important; text-align: center; font-size: 1.5rem; margin-bottom: 1.5rem; }
        table, thead, tbody, tfoot, tr, th, td { color: black !important; border: 1px solid #ccc !important; }
        th { background-color: #f2f2f2 !important; }
        .text-red-500 { color: black !important; font-weight: normal !important; }
    }
</style>

<div class="print-area">
    <h2 class="text-2xl font-bold text-gray-200 mb-4 print-title">Listado de Clientes Atrasados mas de 12 dias</h2>

    <div class="bg-gray-800 p-6 rounded-lg border border-gray-700 print-container">
        <!-- Controles de Filtro y Paginación -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 no-print">
            <form action="index.php" method="GET" class="flex items-center gap-4">
                <input type="hidden" name="page" value="atrasados">
                <label for="zona" class="text-sm font-medium text-gray-300">Filtrar por Zona:</label>
                <select name="zona" id="zona" onchange="this.form.submit()" class="text-sm rounded-md form-element-dark">
                    <option value="all" <?= $zona_seleccionada == 'all' ? 'selected' : '' ?>>Todas las Zonas</option>
                    <?php for($i = 1; $i <= 4; $i++): ?>
                        <option value="<?= $i ?>" <?= $zona_seleccionada == $i ? 'selected' : '' ?>>Zona <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </form>
            <button type="button" onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-md">
                <i class="fas fa-print mr-2"></i>Imprimir
            </button>
        </div>
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 no-print">
            <div class="text-sm text-gray-400">
                <?php
                    $start_item = $offset + 1;
                    $end_item = min($offset + $limit, $total_records);
                    if ($total_records > 0) {
                        echo "Mostrando $start_item a $end_item de $total_records clientes atrasados";
                    }
                ?>
            </div>
            <form action="index.php" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="page" value="atrasados">
                <input type="hidden" name="zona" value="<?= htmlspecialchars($zona_seleccionada) ?>">
                <label for="limit" class="text-sm text-gray-400">Mostrar:</label>
                <select name="limit" id="limit" onchange="this.form.submit()" class="text-sm rounded-md form-element-dark">
                    <?php foreach($limit_options as $option): ?>
                        <option value="<?= $option ?>" <?= $limit == $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Tabla de Clientes Atrasados -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="table-header-custom">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider"><?= sort_link('nombre', 'Cliente', $sort_by, $sort_order) ?></th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Zona</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Último Pago</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider"><?= sort_link('dias_atraso', 'Días de Atraso', $sort_by, $sort_order) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if (empty($atrasados)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 table-row-dark">
                                <i class="fas fa-check-circle fa-3x mb-3 text-green-500"></i>
                                <p>¡Excelente! No hay clientes con más de 12 días de atraso.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($atrasados as $cliente): ?>
                        <tr class="table-row-dark">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-100"><?= htmlspecialchars($cliente['nombre']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-300">Zona <?= htmlspecialchars($cliente['zona']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= htmlspecialchars($cliente['telefono'] ?? 'N/A') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-300"><?= (new DateTime($cliente['ultimo_pago']))->format('d/m/Y') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-red-500"><?= $cliente['dias_atraso'] ?> días</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Navegación de Paginación (Inferior) -->
        <?php if($total_pages > 1): ?>
        <div class="mt-6 flex justify-center items-center gap-2 no-print">
            <a href="?page=atrasados&p=<?= $page_num - 1 ?>&limit=<?= $limit ?>&zona=<?= $zona_seleccionada ?>&sort_by=<?= $sort_by ?>&sort_order=<?= $sort_order ?>" class="<?= $page_num <= 1 ? 'pointer-events-none text-gray-600' : 'text-blue-400 hover:text-blue-300' ?>">
                <i class="fas fa-chevron-left"></i> Anterior
            </a>
            <div class="flex gap-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=atrasados&p=<?= $i ?>&limit=<?= $limit ?>&zona=<?= $zona_seleccionada ?>&sort_by=<?= $sort_by ?>&sort_order=<?= $sort_order ?>" class="px-3 py-1 rounded-md <?= $i == $page_num ? 'bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
            <a href="?page=atrasados&p=<?= $page_num + 1 ?>&limit=<?= $limit ?>&zona=<?= $zona_seleccionada ?>&sort_by=<?= $sort_by ?>&sort_order=<?= $sort_order ?>" class="<?= $page_num >= $total_pages ? 'pointer-events-none text-gray-600' : 'text-blue-400 hover:text-blue-300' ?>">
                Siguiente <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
