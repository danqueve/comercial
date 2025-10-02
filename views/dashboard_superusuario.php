<?php
// views/dashboard_superusuario.php
// Este archivo es incluido por dashboard.php y ya tiene acceso a las variables
// $formularios, $usuarios y $log_actividad.
?>
<!-- Pestañas de Navegación con Tailwind -->
<div class="mb-4 border-b border-gray-700">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="superUserTab" role="tablist">
        <li class="mr-2" role="presentation">
            <button class="inline-block p-4 border-b-2 rounded-t-lg" id="forms-tab" type="button" role="tab">Gestionar Formularios</button>
        </li>
        <li class="mr-2" role="presentation">
            <button class="inline-block p-4 border-b-2 rounded-t-lg" id="users-tab" type="button" role="tab">Gestionar Usuarios</button>
        </li>
        <li role="presentation">
            <button class="inline-block p-4 border-b-2 rounded-t-lg" id="log-tab" type="button" role="tab">Log de Actividad</button>
        </li>
    </ul>
</div>

<!-- Contenido de las Pestañas -->
<div id="superUserTabContent">
    <!-- Pestaña 1: Gestionar Formularios -->
    <div id="forms-content" role="tabpanel">
        <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                <h3 class="text-xl font-semibold text-gray-200">Formularios Activos</h3>
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition-colors" id="printInReviewBtn"><i class="fas fa-print mr-2"></i>Imprimir en Revisión</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900 text-gray-300">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">#ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vendedor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        <?php if (empty($formularios)): ?>
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No hay formularios activos para gestionar.</td></tr>
                        <?php else: ?>
                            <?php foreach ($formularios as $form): ?>
                                <tr class="hover:bg-gray-700/50 <?= $form['estado'] === 'en revision' ? 'en-revision-row' : '' ?>"
                                    data-id="<?= $form['id'] ?>" data-vendedor="<?= htmlspecialchars($form['vendedor_nombre']) ?>" data-cliente="<?= htmlspecialchars($form['cliente_apellido_nombre']) ?>" data-articulo="<?= htmlspecialchars($form['articulo_venta'] ?? 'N/A') ?>" data-domicilio="<?= htmlspecialchars($form['cliente_domicilio'] ?? 'N/A') ?>" data-contacto="<?= htmlspecialchars($form['cliente_whatsapp'] ?? 'N/A') ?>" data-fecha="<?= date('d/m/Y H:i', strtotime($form['fecha_creacion'])) ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($form['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($form['vendedor_nombre']) ?></td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-4">
                                        <a href="view_form.php?id=<?= $form['id'] ?>" class="text-blue-400 hover:text-blue-300" title="Ver Detalles"><i class="fas fa-eye"></i></a>
                                        <?php if ($form['estado'] === 'en revision'): ?>
                                            <button class="text-green-400 hover:text-green-300 form-action-btn" data-form-id="<?= $form['id'] ?>" data-action="aprobado" title="Aprobar"><i class="fas fa-check"></i></button>
                                            <button class="text-red-400 hover:text-red-300 reject-modal-btn" data-form-id="<?= $form['id'] ?>" title="Rechazar"><i class="fas fa-times"></i></button>
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
    <!-- Pestaña 2: Gestionar Usuarios (inicialmente oculta) -->
    <div id="users-content" class="hidden" role="tabpanel">
        <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl p-6 overflow-x-auto">
            <h3 class="text-xl font-semibold text-gray-200 mb-4">Gestionar Roles de Usuario</h3>
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nombre Completo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Celular</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">DNI</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Rol</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">No hay vendedores o supervisores registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($usuario['apellido'] . ', ' . $usuario['nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($usuario['celular']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($usuario['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($usuario['dni']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                    <select class="w-full p-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 user-role-selector" data-user-id="<?= $usuario['id'] ?>">
                                        <option value="vendedor" <?= $usuario['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                                        <option value="supervisor" <?= $usuario['rol'] === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                                        <option value="verificador" <?= $usuario['rol'] === 'verificador' ? 'selected' : '' ?>>Verificador</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pestaña 3: Log de Actividad (inicialmente oculta) -->
    <div id="log-content" class="hidden" role="tabpanel">
        <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl p-6 overflow-x-auto">
            <h3 class="text-xl font-semibold text-gray-200 mb-4">Historial de Cambios de Estado</h3>
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">#Form</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nuevo Estado</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha y Hora</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Realizado por</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($log_actividad)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Aún no hay actividad registrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($log_actividad as $log): ?>
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($log['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($log['cliente_apellido_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                     <?php
                                        $estado_log = htmlspecialchars($log['estado']);
                                        $badge_classes_log = 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full';
                                        if ($estado_log == 'aprobado') $badge_classes_log .= ' bg-green-900/70 text-green-300';
                                        else $badge_classes_log .= ' bg-red-900/70 text-red-300';
                                    ?>
                                    <span class="<?= $badge_classes_log ?>"><?= ucfirst($estado_log) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= date('d/m/Y H:i:s', strtotime($log['fecha_actualizacion_estado'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($log['supervisor_nombre']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// JS para manejar las pestañas de Tailwind
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('#superUserTab button');
    const tabContents = document.querySelectorAll('#superUserTabContent > div');

    const activeClasses = 'text-blue-400 border-blue-400';
    const inactiveClasses = 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500';

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => {
                t.classList.remove(...activeClasses.split(' '));
                t.classList.add(...inactiveClasses.split(' '));
            });
            tabContents.forEach(c => c.classList.add('hidden'));

            tab.classList.remove(...inactiveClasses.split(' '));
            tab.classList.add(...activeClasses.split(' '));
            tabContents[index].classList.remove('hidden');
        });
    });

    // Activar la primera pestaña por defecto
    tabs[0].classList.remove(...inactiveClasses.split(' '));
    tabs[0].classList.add(...activeClasses.split(' '));
    tabContents[0].classList.remove('hidden');
});
</script>
