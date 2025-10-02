<?php
// views/dashboard_supervisor.php
// Este archivo es incluido por dashboard.php y ya tiene acceso a la variable $formularios.
?>
<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h3 class="text-xl font-semibold text-gray-200">Gestionar Formularios</h3>
            <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition-colors" id="printInReviewBtn">
                <i class="fas fa-print mr-2"></i>Imprimir en Revisión
            </button>
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
                                data-id="<?= $form['id'] ?>"
                                data-vendedor="<?= htmlspecialchars($form['vendedor_nombre']) ?>"
                                data-cliente="<?= htmlspecialchars($form['cliente_apellido_nombre']) ?>"
                                data-articulo="<?= htmlspecialchars($form['articulo_venta'] ?? 'N/A') ?>"
                                data-domicilio="<?= htmlspecialchars($form['cliente_domicilio'] ?? 'N/A') ?>"
                                data-contacto="<?= htmlspecialchars($form['cliente_whatsapp'] ?? 'N/A') ?>"
                                data-fecha="<?= date('d/m/Y H:i', strtotime($form['fecha_creacion'])) ?>">
                                
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
