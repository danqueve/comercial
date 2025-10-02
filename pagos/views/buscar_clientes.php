<?php
// Este script es el backend para la búsqueda en vivo.
// No produce una página completa, solo los datos que se necesitan actualizar.
require_once '../config.php';

// --- LÓGICA DE BÚSQUEDA Y PAGINACIÓN ---
$search_term = $_GET['search'] ?? '';
$limit = isset($_GET['limit']) && in_array($_GET['limit'], [10, 20, 50, 100]) ? (int)$_GET['limit'] : 10;
$page_num = isset($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

try {
    // 1. OBTENER EL NÚMERO TOTAL DE CRÉDITOS COINCIDENTES
    $sql_count = "SELECT COUNT(cr.id) FROM creditos cr JOIN clientes c ON cr.cliente_id = c.id WHERE c.nombre LIKE :search";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([':search' => '%' . $search_term . '%']);
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $limit);

    // 2. OBTENER LOS CRÉDITOS PARA LA PÁGINA ACTUAL
    $sql = "SELECT c.id as cliente_id, c.nombre, c.direccion, cr.id as credito_id, cr.zona, cr.dia_pago, cr.cuotas_pagadas, cr.total_cuotas, cr.monto_cuota
            FROM creditos cr
            JOIN clientes c ON cr.cliente_id = c.id
            WHERE c.nombre LIKE :search
            ORDER BY c.nombre ASC, cr.id ASC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', '%' . $search_term . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $creditos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- CONSTRUIR EL HTML DE RESPUESTA ---
    
    // a) HTML para el cuerpo de la tabla (tbody)
    $table_body_html = '';
    if (empty($creditos)) {
        $table_body_html = '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-400 table-row-dark"><i class="fas fa-search-minus fa-3x mb-3"></i><p>No se encontraron registros.</p></td></tr>';
    } else {
        foreach ($creditos as $credito) {
            $nombre = htmlspecialchars($credito['nombre']);
            $zona = 'Zona ' . htmlspecialchars($credito['zona']);
            $direccion = htmlspecialchars($credito['direccion'] ?? 'No especificada');
            $dia_pago = htmlspecialchars($credito['dia_pago']);
            $cuotas = $credito['cuotas_pagadas'] . ' / ' . $credito['total_cuotas'];
            $monto = formatCurrency($credito['monto_cuota']);
            $credito_id = $credito['credito_id'];
            $cliente_id = $credito['cliente_id'];

            $table_body_html .= "<tr class='table-row-dark'>";
            $table_body_html .= "<td data-label='Nombre' class='px-6 py-4 whitespace-nowrap font-medium text-gray-100'>{$nombre}</td>";
            $table_body_html .= "<td data-label='Zona' class='px-6 py-4 whitespace-nowrap text-center text-gray-300'>{$zona}</td>";
            $table_body_html .= "<td data-label='Dirección' class='px-6 py-4 whitespace-nowrap text-gray-300'>{$direccion}</td>";
            $table_body_html .= "<td data-label='Día de Pago' class='px-6 py-4 whitespace-nowrap text-center text-gray-300'>{$dia_pago}</td>";
            $table_body_html .= "<td data-label='Cuotas' class='px-6 py-4 whitespace-nowrap text-center text-gray-300'>{$cuotas}</td>";
            $table_body_html .= "<td data-label='Monto Cuota' class='px-6 py-4 whitespace-nowrap text-right text-gray-300'>{$monto}</td>";
            $table_body_html .= "<td data-label='Acciones' class='px-6 py-4 whitespace-nowrap text-center no-print'>";
            $table_body_html .= "<a href='index.php?page=editar_cliente&id={$credito_id}' class='text-blue-400 hover:text-blue-300' title='Editar'><i class='fas fa-pencil-alt'></i></a>";
            $table_body_html .= "<a href='index.php?page=eliminar_cliente&id={$cliente_id}' class='text-red-500 hover:text-red-400 ml-4' title='Eliminar Cliente' onclick=\"return confirm('¿Estás seguro de que quieres eliminar a este cliente? Se borrarán TODOS sus créditos.')\"><i class='fas fa-trash-alt'></i></a>";
            $table_body_html .= "</td></tr>";
        }
    }

    // b) HTML para el contador de resultados
    $start_item = $offset + 1;
    $end_item = min($offset + $limit, $total_records);
    $results_count_html = ($total_records > 0) ? "Mostrando $start_item a $end_item de $total_records créditos" : "";

    // c) HTML para la paginación
    $pagination_html = '';
    if ($total_pages > 1) {
        $pagination_html .= "<a href='#' data-page='" . ($page_num - 1) . "' class='page-link " . ($page_num <= 1 ? 'pointer-events-none text-gray-600' : 'text-blue-400 hover:text-blue-300') . "'><i class='fas fa-chevron-left'></i> Anterior</a>";
        $pagination_html .= "<div class='flex gap-2'>";
        for ($i = 1; $i <= $total_pages; $i++) {
            $pagination_html .= "<a href='#' data-page='{$i}' class='page-link px-3 py-1 rounded-md " . ($i == $page_num ? 'bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600') . "'>{$i}</a>";
        }
        $pagination_html .= "</div>";
        $pagination_html .= "<a href='#' data-page='" . ($page_num + 1) . "' class='page-link " . ($page_num >= $total_pages ? 'pointer-events-none text-gray-600' : 'text-blue-400 hover:text-blue-300') . "'>Siguiente <i class='fas fa-chevron-right'></i></a>";
    }

    // Devolver los resultados como un objeto JSON
    header('Content-Type: application/json');
    echo json_encode([
        'tableBody' => $table_body_html,
        'pagination' => $pagination_html,
        'resultsCounter' => $results_count_html
    ]);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
