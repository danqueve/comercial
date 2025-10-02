<?php
// api.php
// Establece la cabecera para indicar que la respuesta será en formato JSON.
header('Content-Type: application/json');

// 1. INCLUIR ARCHIVOS DE CONFIGURACIÓN Y FUNCIONES
require_once 'lib/config.php';
require_once 'lib/functions.php';

// 2. VERIFICAR QUE EL USUARIO HAYA INICIADO SESIÓN
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Debes iniciar sesión.']);
    exit;
}

// 3. VERIFICAR QUE EL USUARIO TENGA PERMISOS (NO SEA VENDEDOR)
if ($_SESSION['user_rol'] === 'vendedor') {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.']);
    exit;
}

// 4. LEER LOS DATOS ENVIADOS DESDE JAVASCRIPT
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No se especificó ninguna acción.']);
    exit;
}

// 5. PROCESAR LA ACCIÓN SOLICITADA
try {
    switch ($action) {
        
        // CASO 1: CAMBIAR EL ROL DE UN USUARIO
        case 'change_role':
            if ($_SESSION['user_rol'] !== 'superusuario') {
                throw new Exception('No tienes los permisos necesarios para cambiar roles.');
            }
            $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
            // Se añade 'verificador' a la lista de roles válidos que se pueden asignar.
            $new_role = in_array($data['new_role'], ['vendedor', 'supervisor', 'verificador']) ? $data['new_role'] : null;

            if (!$user_id || !$new_role) {
                throw new Exception('Datos inválidos para cambiar el rol.');
            }
            
            $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ? AND rol != 'superusuario'");
            $stmt->execute([$new_role, $user_id]);
            echo json_encode(['success' => true, 'message' => 'Rol actualizado correctamente.']);
            break;

        // CASO 2: ACTUALIZAR EL ESTADO DE UN FORMULARIO (APROBADO/RECHAZADO)
        case 'update_status':
            // Se permite que el 'verificador' también pueda ejecutar esta acción.
            if (!in_array($_SESSION['user_rol'], ['supervisor', 'superusuario', 'verificador'])) {
                throw new Exception('No tienes permiso para actualizar estados.');
            }
            $form_id = filter_var($data['form_id'], FILTER_VALIDATE_INT);
            $status = in_array($data['status'], ['aprobado', 'rechazado']) ? $data['status'] : null;
            $reason = ($status === 'rechazado') ? filter_var($data['reason'], FILTER_SANITIZE_STRING) : null;

            if (!$form_id || !$status) {
                throw new Exception('Datos inválidos para actualizar el estado.');
            }

            $sql = "UPDATE formularios SET estado = ?, motivo_rechazo = ?, supervisor_id_accion = ?, fecha_actualizacion_estado = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $reason, $_SESSION['user_id'], $form_id]);
            echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente.']);
            break;

        // CASO 3: MARCAR UNA VENTA COMO ENTREGADA
        case 'mark_as_delivered':
            $form_id = filter_var($data['form_id'], FILTER_VALIDATE_INT);

            if (!$form_id) {
                throw new Exception('ID de formulario inválido.');
            }

            // Actualiza el estado y la fecha de entrega al mismo tiempo
            $stmt = $pdo->prepare("
                UPDATE formularios 
                SET estado_entrega = 'entregado', fecha_entrega_realizada = NOW() 
                WHERE id = ? AND estado = 'aprobado'
            ");
            $stmt->execute([$form_id]);
            echo json_encode(['success' => true, 'message' => 'Venta marcada como entregada.']);
            break;

        // CASO 4: CANCELAR LA APROBACIÓN DE UNA VENTA
        case 'cancel_approval':
            $form_id = filter_var($data['form_id'], FILTER_VALIDATE_INT);

            if (!$form_id) {
                throw new Exception('ID de formulario inválido.');
            }

            // Se actualiza el estado a 'en revision' y se limpian los datos de la acción anterior.
            $stmt = $pdo->prepare("
                UPDATE formularios 
                SET estado = 'en revision', 
                    supervisor_id_accion = NULL, 
                    fecha_actualizacion_estado = NULL 
                WHERE id = ? AND estado = 'aprobado'
            ");
            $stmt->execute([$form_id]);
            echo json_encode(['success' => true, 'message' => 'La aprobación ha sido cancelada. El formulario está nuevamente en revisión.']);
            break;

        // CASO POR DEFECTO: SI LA ACCIÓN NO SE RECONOCE
        default:
            throw new Exception('La acción solicitada no es válida.');
    }
} catch (Exception $e) {
    // Si ocurre cualquier error durante el proceso, se captura y se devuelve un mensaje.
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}