<?php
// views/dashboard_verificador.php
// Este archivo es incluido por dashboard.php y ya tiene acceso a la variable $formularios.
?>
<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="p-4 sm:p-6">
        <h3 class="text-xl font-semibold text-gray-200">Formularios Pendientes de Verificación</h3>
        <p class="text-sm text-gray-400 mt-1">Haz clic en el nombre de un cliente para ver la ficha completa y tomar una acción.</p>

        <div class="overflow-x-auto mt-6">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900 text-gray-300">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">#ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vendedor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Domicilio</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Celular</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Fecha</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    <?php if (empty($formularios)): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">¡Excelente! No hay formularios pendientes de revisión.</td></tr>
                    <?php else: ?>
                        <?php foreach ($formularios as $form): ?>
                            <tr class="hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-200"><?= htmlspecialchars($form['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($form['vendedor_nombre']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="view_form.php?id=<?= $form['id'] ?>" class="text-blue-400 hover:underline" title="Ver Ficha Completa">
                                        <?= htmlspecialchars($form['cliente_apellido_nombre']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($form['cliente_domicilio']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($form['cliente_celular_llamada'] ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= date('d/m/Y H:i', strtotime($form['fecha_creacion'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
