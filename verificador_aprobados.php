<?php
// verificador_aprobados.php
require_once 'lib/config.php';
require_once 'lib/functions.php';
check_login();

// Seguridad: solo verificadores pueden acceder
if ($_SESSION['user_rol'] !== 'verificador') {
    redirect('dashboard.php');
}

$user_id = $_SESSION['user_id'];
// Llamamos a la nueva función para obtener solo las ventas aprobadas por este usuario
$aprobados = get_aprobados_by_verificador($pdo, $user_id);

include 'partials/header.php';
?>

<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <h2 class="text-2xl font-bold text-gray-200 mb-4"><i class="fas fa-check-double mr-2"></i>Mis Aprobaciones</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"># Venta</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vendedor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha de Aprobación</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($aprobados)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Aún no has aprobado ningún formulario.</td></tr>
                    <?php else: ?>
                        <?php foreach ($aprobados as $venta): ?>
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($venta['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['vendedor_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($venta['cliente_apellido_nombre']) ?></td>
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
    </div>
</div>

<?php include 'partials/footer.php'; ?>
