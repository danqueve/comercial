<?php
// views/dashboard_vendedor.php
// Este archivo es incluido por dashboard.php y ya tiene acceso a las variables $stats y $formularios.
?>

<!-- Tarjetas de Estadísticas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Card: Aprobados -->
    <div class="bg-green-600/80 text-white p-6 rounded-xl shadow-lg flex items-center justify-between">
        <div>
            <div class="text-lg font-semibold">Aprobados</div>
            <div class="text-4xl font-bold"><?= $stats['aprobado'] ?? 0 ?></div>
        </div>
        <i class="fas fa-check-circle text-5xl opacity-30"></i>
    </div>
    <!-- Card: En Revisión -->
    <div class="bg-yellow-500/80 text-white p-6 rounded-xl shadow-lg flex items-center justify-between">
        <div>
            <div class="text-lg font-semibold">En Revisión</div>
            <div class="text-4xl font-bold"><?= $stats['en revision'] ?? 0 ?></div>
        </div>
        <i class="fas fa-hourglass-half text-5xl opacity-30"></i>
    </div>
    <!-- Card: Rechazados -->
    <div class="bg-red-600/80 text-white p-6 rounded-xl shadow-lg flex items-center justify-between">
        <div>
            <div class="text-lg font-semibold">Rechazados</div>
            <div class="text-4xl font-bold"><?= $stats['rechazado'] ?? 0 ?></div>
        </div>
        <i class="fas fa-times-circle text-5xl opacity-30"></i>
    </div>
</div>

<!-- Tabla de Formularios -->
<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <h3 class="text-xl font-semibold text-gray-200 mb-4">Mis Formularios Cargados</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">#ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha y Hora</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Estado</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($formularios)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Aún no has cargado ningún formulario.</td></tr>
                    <?php else: ?>
                        <?php foreach ($formularios as $form): ?>
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($form['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($form['cliente_apellido_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= date('d/m/Y H:i', strtotime($form['fecha_creacion'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $estado = htmlspecialchars($form['estado']);
                                        $badge_classes = 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full';
                                        if ($estado == 'aprobado') $badge_classes .= ' bg-green-900/70 text-green-300';
                                        elseif ($estado == 'rechazado') $badge_classes .= ' bg-red-900/70 text-red-300';
                                        else $badge_classes .= ' bg-yellow-900/70 text-yellow-300';
                                    ?>
                                    <span class="<?= $badge_classes ?>"><?= ucfirst($estado) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view_form.php?id=<?= $form['id'] ?>" class="text-blue-400 hover:text-blue-300" title="Ver Detalles">
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