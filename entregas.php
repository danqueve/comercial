<?php
// entregas.php
require_once 'lib/config.php';
require_once 'lib/functions.php';
check_login();

// Seguridad: solo supervisores, superusuario y verificadores pueden acceder
if (!in_array($_SESSION['user_rol'], ['supervisor', 'superusuario', 'verificador'])) {
    redirect('dashboard.php');
}

// Obtener todos los campos necesarios para la visualización y la impresión
$stmt = $pdo->prepare("
    SELECT 
        f.id, f.cliente_apellido_nombre, f.articulo_venta, f.estado_entrega, 
        f.fecha_entrega_realizada, f.fecha_creacion, f.cliente_domicilio,
        f.cliente_whatsapp, f.financiacion,
        CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre
    FROM formularios f
    JOIN usuarios u ON f.vendedor_id = u.id
    WHERE f.estado = 'aprobado' 
    ORDER BY f.estado_entrega ASC, f.fecha_actualizacion_estado DESC
");
$stmt->execute();
$ventas_aprobadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'partials/header.php';
?>

<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h2 class="text-2xl font-bold text-gray-200"><i class="fas fa-truck mr-2"></i>Gestión de Entregas</h2>
            <div class="flex gap-2">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center" id="printPendingBtn">
                    <i class="fas fa-print mr-2"></i>Imprimir Pendientes
                </button>
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center" id="printDeliveredBtn">
                    <i class="fas fa-check-double mr-2"></i>Imprimir Entregados
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto mt-6">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider"># Venta</th>
                        <th scope="col" class="px-3 lg:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-3 lg:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Artículo</th>
                        <th scope="col" class="px-3 lg:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha Solicitud</th>
                        <th scope="col" class="px-3 lg:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Estado Entrega</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($ventas_aprobadas)): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay ventas aprobadas para gestionar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ventas_aprobadas as $venta): ?>
                            <tr 
                                class="hover:bg-gray-700/50 <?= $venta['estado_entrega'] === 'pendiente' ? 'pendiente-row' : 'entregado-row' ?>"
                                data-id="<?= $venta['id'] ?>"
                                data-vendedor="<?= htmlspecialchars($venta['vendedor_nombre']) ?>"
                                data-cliente="<?= htmlspecialchars($venta['cliente_apellido_nombre']) ?>"
                                data-articulo="<?= htmlspecialchars($venta['articulo_venta'] ?? 'N/A') ?>"
                                data-domicilio="<?= htmlspecialchars($venta['cliente_domicilio'] ?? 'N/A') ?>"
                                data-contacto="<?= htmlspecialchars($venta['cliente_whatsapp'] ?? 'N/A') ?>"
                                data-financiacion="<?= htmlspecialchars($venta['financiacion'] ?? 'N/A') ?>"
                            >
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($venta['id']) ?></td>
                                <td class="px-3 lg:px-6 py-4 text-sm text-gray-400"><?= htmlspecialchars($venta['cliente_apellido_nombre']) ?></td>
                                <td class="px-3 lg:px-6 py-4 text-sm text-gray-400"><?= htmlspecialchars($venta['articulo_venta'] ?? 'N/A') ?></td>
                                <td class="px-3 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= date('d/m/Y H:i', strtotime($venta['fecha_creacion'])) ?></td>
                                <td class="px-3 lg:px-6 py-4 whitespace-nowrap">
                                    <?php if ($venta['estado_entrega'] === 'entregado'): ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/70 text-green-300">Entregado</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-900/70 text-yellow-300">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-medium space-x-4">
                                    <a href="view_form.php?id=<?= $venta['id'] ?>" class="text-blue-400 hover:text-blue-300" title="Ver Detalles"><i class="fas fa-eye"></i></a>
                                    <?php if ($venta['estado_entrega'] === 'pendiente'): ?>
                                        <button class="text-green-400 hover:text-green-300 text-xl mark-delivered-btn" data-form-id="<?= $venta['id'] ?>" title="Marcar como Entregado"><i class="fas fa-check-circle"></i></button>
                                        <button class="text-yellow-400 hover:text-yellow-300 text-xl cancel-approval-btn" data-form-id="<?= $venta['id'] ?>" title="Cancelar Aprobación"><i class="fas fa-undo-alt"></i></button>
                                    <?php endif; ?>
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