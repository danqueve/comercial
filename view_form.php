<?php
// view_form.php
require_once 'lib/config.php';
require_once 'lib/functions.php';
check_login();

$form_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$form_id) {
    redirect('dashboard.php');
}

$sql = "
    SELECT f.*, 
           CONCAT(v.nombre, ' ', v.apellido) AS vendedor_nombre,
           CONCAT(s.nombre, ' ', s.apellido) AS supervisor_nombre
    FROM formularios f
    JOIN usuarios v ON f.vendedor_id = v.id
    LEFT JOIN usuarios s ON f.supervisor_id_accion = s.id
    WHERE f.id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$form_id]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) {
    include 'partials/header.php';
    echo "<div class='bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded' role='alert'>Error: Formulario no encontrado.</div>";
    include 'partials/footer.php';
    exit;
}

if ($_SESSION['user_rol'] === 'vendedor' && $form['vendedor_id'] != $_SESSION['user_id']) {
    redirect('dashboard.php');
}

include 'partials/header.php';
?>

<div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl overflow-hidden">
    <div class="bg-gray-900/50 text-white p-4 sm:p-6">
        <h2 class="text-2xl font-bold">Detalle del Formulario #<?= htmlspecialchars($form['id']) ?></h2>
    </div>
    <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna Principal de Datos -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Sección Datos del Cliente -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-300 border-b border-gray-700 pb-2 mb-4 flex items-center"><i class="fas fa-user-circle text-blue-400 mr-3"></i>Datos del Cliente</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><strong class="block text-gray-500">Apellido y Nombre:</strong><p class="text-gray-200"><?= htmlspecialchars($form['cliente_apellido_nombre']) ?></p></div>
                        <div><strong class="block text-gray-500">DNI:</strong><p class="text-gray-200"><?= htmlspecialchars($form['cliente_dni']) ?></p></div>
                        <div class="sm:col-span-2"><strong class="block text-gray-500">Domicilio:</strong><p class="text-gray-200"><?= htmlspecialchars($form['cliente_domicilio']) ?>, <?= htmlspecialchars($form['cliente_localidad']) ?> (<?= htmlspecialchars($form['cliente_barrio'] ?? 'N/A') ?>)</p></div>
                        <div><strong class="block text-gray-500">WhatsApp:</strong><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $form['cliente_whatsapp']) ?>" target="_blank" class="text-blue-400 hover:underline"><?= htmlspecialchars($form['cliente_whatsapp']) ?></a></div>
                        <div><strong class="block text-gray-500">Llamadas:</strong><p class="text-gray-200"><?= htmlspecialchars($form['cliente_celular_llamada'] ?? 'N/A') ?></p></div>
                    </div>
                </div>

                <!-- Sección Datos Laborales -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-300 border-b border-gray-700 pb-2 mb-4 flex items-center"><i class="fas fa-briefcase text-blue-400 mr-3"></i>Datos Laborales</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><strong class="block text-gray-500">Tipo de Empleo:</strong><p class="text-gray-200"><?= htmlspecialchars($form['cliente_tipo_empleo'] ?? 'No especificado') ?></p></div>
                        <div><strong class="block text-gray-500">Ocupación:</strong><p class="text-gray-200"><?= htmlspecialchars($form['cliente_de_que_trabaja'] ?? 'No especificado') ?></p></div>
                        <div class="sm:col-span-2"><strong class="block text-gray-500">Lugar de Trabajo:</strong><p class="text-gray-200"><?= htmlspecialchars($form['cliente_nombre_trabajo'] ?? 'No especificado') ?> (<?= htmlspecialchars($form['cliente_domicilio_trabajo'] ?? 'N/A') ?>)</p></div>
                    </div>
                </div>

                <!-- Sección Detalles de la Venta -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-300 border-b border-gray-700 pb-2 mb-4 flex items-center"><i class="fas fa-box-open text-blue-400 mr-3"></i>Detalles de la Venta</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><strong class="block text-gray-500">Artículo:</strong><p class="text-gray-200"><?= htmlspecialchars($form['articulo_venta'] ?? 'No especificado') ?></p></div>
                        <div><strong class="block text-gray-500">Financiación:</strong><p class="text-gray-200"><?= htmlspecialchars($form['financiacion'] ?? 'No especificado') ?></p></div>
                        <div><strong class="block text-gray-500">Fecha de Entrega Deseada:</strong><p class="text-gray-200"><?= !empty($form['fecha_entrega_deseada']) ? date('d/m/Y', strtotime($form['fecha_entrega_deseada'])) : 'No especificada' ?></p></div>
                    </div>
                    <div class="mt-4"><strong class="block text-gray-500 text-sm">Observaciones:</strong><p class="text-gray-300 text-sm italic bg-gray-900/50 p-3 rounded-md mt-1"><?= !empty($form['articulo_detalles']) ? nl2br(htmlspecialchars($form['articulo_detalles'])) : 'Sin observaciones.' ?></p></div>
                </div>
            </div>

            <!-- Columna Lateral de Información -->
            <div class="bg-gray-900/50 border border-gray-700 p-4 rounded-lg space-y-4 h-fit">
                <h3 class="text-lg font-semibold text-gray-300 flex items-center"><i class="fas fa-info-circle mr-2"></i>Información</h3>
                <div class="text-sm"><strong class="block text-gray-500">Vendedor:</strong><p class="text-gray-200"><?= htmlspecialchars($form['vendedor_nombre']) ?></p></div>
                <div class="text-sm"><strong class="block text-gray-500">Fecha de Carga:</strong><p class="text-gray-200"><?= date('d/m/Y H:i', strtotime($form['fecha_creacion'])) ?></p></div>
                <hr class="border-gray-700">
                <div>
                    <strong class="block text-gray-500 text-sm mb-1">Estado de Venta:</strong>
                    <?php
                        $estado = htmlspecialchars($form['estado']);
                        $badge_classes = 'w-full text-center px-3 py-2 inline-flex text-sm leading-5 font-bold rounded-lg';
                        if ($estado == 'aprobado') $badge_classes .= ' bg-green-900/70 text-green-300';
                        elseif ($estado == 'rechazado') $badge_classes .= ' bg-red-900/70 text-red-300';
                        else $badge_classes .= ' bg-yellow-900/70 text-yellow-300';
                    ?>
                    <span class="<?= $badge_classes ?>"><?= ucfirst($estado) ?></span>
                </div>
                
                <?php if ($form['estado'] === 'aprobado'): ?>
                    <div>
                        <strong class="block text-gray-500 text-sm mb-1">Estado de Entrega:</strong>
                        <?php if ($form['estado_entrega'] === 'entregado'): ?>
                            <div class="bg-green-900/70 text-green-300 p-2 text-center rounded-lg text-sm">
                                <i class="fas fa-check-circle mr-1"></i><strong>Entregado</strong>
                                <?php if (!empty($form['fecha_entrega_realizada'])): ?><span class="block text-xs">El <?= date('d/m/Y', strtotime($form['fecha_entrega_realizada'])) ?></span><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-blue-900/70 text-blue-300 p-2 text-center rounded-lg text-sm"><i class="fas fa-truck mr-1"></i><strong>Pendiente de Entrega</strong></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($form['estado'] === 'rechazado' && !empty($form['motivo_rechazo'])): ?>
                    <div class="bg-red-900/50 border-l-4 border-red-500 text-red-300 p-3 text-sm">
                        <strong class="d-block mb-1">Motivo del Rechazo:</strong>
                        <p class="mb-0 italic">"<?= htmlspecialchars($form['motivo_rechazo']) ?>"</p>
                    </div>
                <?php endif; ?>

                 <?php if ($form['supervisor_nombre']): ?>
                    <div class="text-sm border-t border-gray-700 pt-4"><strong class="block text-gray-500">Última acción por:</strong><p class="text-gray-200"><?= htmlspecialchars($form['supervisor_nombre']) ?></p></div>
                    <div class="text-sm"><strong class="block text-gray-500">Fecha de acción:</strong><p class="text-gray-200"><?= date('d/m/Y H:i', strtotime($form['fecha_actualizacion_estado'])) ?></p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="bg-gray-900/50 border-t border-gray-700 p-4 flex justify-between items-center">
        <a href="dashboard.php" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition duration-300">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
        
        <?php if (in_array($_SESSION['user_rol'], ['verificador', 'supervisor', 'superusuario']) && $form['estado'] === 'en revision'): ?>
        <div class="space-x-4">
            <button class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition-colors reject-modal-btn" data-form-id="<?= $form['id'] ?>">
                <i class="fas fa-times mr-2"></i>Rechazar
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center transition-colors form-action-btn" data-form-id="<?= $form['id'] ?>" data-action="aprobado">
                <i class="fas fa-check mr-2"></i>Aprobar
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>