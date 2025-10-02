<?php
// --- LÓGICA DE LA VISTA DE CLIENTES CON PAGINACIÓN ---
// Esta parte de PHP ahora solo se usa para la carga inicial de la página.

$success = '';
$error = '';
$search_term = $_GET['search'] ?? '';

$limit_options = [10, 20, 50, 100];
$limit = isset($_GET['limit']) && in_array($_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 10;
$page_num = isset($_GET['p']) && $_GET['p'] > 0 ? (int)$_GET['p'] : 1;
$offset = ($page_num - 1) * $limit;

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') $success = "¡Cliente y crédito agregados exitosamente!";
    if ($_GET['status'] == 'deleted') $success = "¡Cliente eliminado correctamente!";
    if ($_GET['status'] == 'updated') $success = "¡Cliente y crédito actualizados correctamente!";
    if ($_GET['status'] == 'notfound') $error = "Error: No se encontró el registro especificado.";
    if ($_GET['status'] == 'error') $error = "Error: " . htmlspecialchars($_GET['msg']);
}

try {
    // --- CONSULTAS CORREGIDAS ---
    $sql_count = "SELECT COUNT(id) FROM clientes WHERE nombre LIKE :search";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([':search' => '%' . $search_term . '%']);
    $total_clientes = $stmt_count->fetchColumn();
    $total_pages = ceil($total_clientes / $limit);

    // Se unificaron los marcadores a solo tipo "named" (:param)
    $sql = "SELECT c.id, c.nombre, c.direccion,
                   (SELECT cr.zona FROM creditos cr WHERE cr.cliente_id = c.id ORDER BY cr.id DESC LIMIT 1) as zona
            FROM clientes c
            WHERE c.nombre LIKE :search
            ORDER BY c.nombre ASC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', '%' . $search_term . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al obtener los clientes: " . $e->getMessage());
}
?>

<h2 class="text-2xl font-bold text-gray-200 mb-4">Gestión de Clientes</h2>

<?php if(!empty($success)): ?>
    <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded-md mb-4" role="alert"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if(!empty($error)): ?>
    <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded-md mb-4" role="alert"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Barra de Búsqueda y Botón de Agregar -->
<div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 no-print">
    <div class="w-full sm:w-1/2 lg:w-1/3">
        <div class="relative">
            <input type="text" id="search-input" name="search" class="w-full pl-10 pr-4 py-2 rounded-lg form-element-dark" placeholder="Buscar cliente por nombre..." value="<?= htmlspecialchars($search_term) ?>">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>
    <a href="index.php?page=agregar_cliente" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex-shrink-0">
        <i class="fas fa-plus mr-2"></i>Agregar Cliente
    </a>
</div>

<!-- Controles de Paginación (Superior) -->
<div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4 no-print">
    <div id="results-counter" class="text-sm text-gray-400">
        <?php
            $start_item = $offset + 1;
            $end_item = min($offset + $limit, $total_clientes);
            if ($total_clientes > 0) {
                echo "Mostrando $start_item a $end_item de $total_clientes clientes";
            }
        ?>
    </div>
    <div class="flex items-center gap-2">
        <label for="limit-select" class="text-sm text-gray-400">Mostrar:</label>
        <select id="limit-select" name="limit" class="text-sm rounded-md form-element-dark">
            <?php foreach($limit_options as $option): ?>
                <option value="<?= $option ?>" <?= $limit == $option ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Contenedor de la tabla -->
<div class="overflow-x-auto rounded-lg shadow border border-gray-700">
    <table class="min-w-full divide-y divide-gray-700">
        <thead class="table-header-custom">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider">Zona</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Dirección</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-300 uppercase tracking-wider no-print">Acciones</th>
            </tr>
        </thead>
        <tbody id="clientes-table-body" class="divide-y divide-gray-700">
            <?php if (empty($clientes)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 table-row-dark">
                        <i class="fas fa-search-minus fa-3x mb-3"></i>
                        <p>No se encontraron clientes.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($clientes as $cliente): ?>
                <tr class="table-row-dark">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-100"><?= htmlspecialchars($cliente['nombre']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-gray-300"><?= $cliente['zona'] ? 'Zona ' . htmlspecialchars($cliente['zona']) : 'N/A' ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= htmlspecialchars($cliente['direccion'] ?? 'No especificada') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-center no-print">
                        <a href="index.php?page=editar_cliente&id=<?= $cliente['id'] ?>" class="text-blue-400 hover:text-blue-300" title="Editar Cliente"><i class="fas fa-pencil-alt"></i></a>
                        <a href="index.php?page=eliminar_cliente&id=<?= $cliente['id'] ?>" class="text-red-500 hover:text-red-400 ml-4" title="Eliminar Cliente" onclick="return confirm('¿Estás seguro de que quieres eliminar a este cliente? Se borrarán también todos sus créditos asociados.')"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Navegación de Paginación (Inferior) -->
<div id="pagination-container" class="mt-6 flex justify-center items-center gap-2 no-print">
    <?php if($total_pages > 1): ?>
        <a href="#" data-page="<?= $page_num - 1 ?>" class="page-link <?= $page_num <= 1 ? 'pointer-events-none text-gray-600' : 'text-blue-400 hover:text-blue-300' ?>"><i class="fas fa-chevron-left"></i> Anterior</a>
        <div class="flex gap-2">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="#" data-page="<?= $i ?>" class="page-link px-3 py-1 rounded-md <?= $i == $page_num ? 'bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <a href="#" data-page="<?= $page_num + 1 ?>" class="page-link <?= $page_num >= $total_pages ? 'pointer-events-none text-gray-600' : 'text-blue-400 hover:text-blue-300' ?>">Siguiente <i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const limitSelect = document.getElementById('limit-select');
    const tableBody = document.getElementById('clientes-table-body');
    const paginationContainer = document.getElementById('pagination-container');
    const resultsCounter = document.getElementById('results-counter');
    
    let currentPage = 1;
    let debounceTimer;

    // Función principal para buscar y actualizar la tabla
    function fetchClientes() {
        const searchTerm = searchInput.value;
        const limit = limitSelect.value;
        
        // Muestra un indicador de carga
        tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-gray-400 table-row-dark"><i class="fas fa-spinner fa-spin fa-2x"></i></td></tr>';

        fetch(`views/buscar_clientes.php?search=${encodeURIComponent(searchTerm)}&limit=${limit}&p=${currentPage}`)
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-12 text-center text-red-400">${data.error}</td></tr>`;
                    return;
                }
                // Actualiza el contenido de la tabla, la paginación y el contador
                tableBody.innerHTML = data.tableBody;
                paginationContainer.innerHTML = data.pagination;
                resultsCounter.innerHTML = data.resultsCounter;
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-12 text-center text-red-400">Ocurrió un error al cargar los datos.</td></tr>';
            });
    }

    // Función "debounce" para evitar demasiadas llamadas a la API mientras se escribe
    function debounce(func, delay) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(func, delay);
    }

    // Evento para el campo de búsqueda
    searchInput.addEventListener('keyup', () => {
        currentPage = 1; // Resetea a la primera página con cada nueva búsqueda
        debounce(fetchClientes, 300); // Espera 300ms después de dejar de escribir
    });

    // Evento para el selector de límite de resultados
    limitSelect.addEventListener('change', () => {
        currentPage = 1; // Resetea a la primera página
        fetchClientes();
    });

    // Evento para los clics en la paginación
    paginationContainer.addEventListener('click', function(e) {
        e.preventDefault();
        const target = e.target.closest('.page-link');
        if (target && target.dataset.page) {
            const page = parseInt(target.dataset.page, 10);
            if (page > 0) { // Asegura que no vayamos a página 0 o negativa
                currentPage = page;
                fetchClientes();
            }
        }
    });
});
</script>