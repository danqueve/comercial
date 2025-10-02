<?php
// lib/functions.php
require_once 'config.php';

/**
 * Redirige a una página específica.
 * @param string $url La URL a la que redirigir.
 */
function redirect(string $url): void {
    header("Location: {$url}");
    exit();
}

/**
 * Verifica si un usuario ha iniciado sesión.
 * Si no, lo redirige a la página de login.
 */
function check_login(): void {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

/**
 * Obtiene los datos de un usuario por su ID.
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $id ID del usuario.
 * @return array|false
 */
function get_user_by_id(PDO $pdo, int $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Obtiene las estadísticas de formularios para un vendedor.
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $vendedor_id ID del vendedor.
 * @return array
 */
function get_vendedor_stats(PDO $pdo, int $vendedor_id): array {
    $estados = ['aprobado', 'rechazado', 'en revision'];
    $stats = [];
    foreach ($estados as $estado) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM formularios WHERE vendedor_id = ? AND estado = ?");
        $stmt->execute([$vendedor_id, $estado]);
        $stats[$estado] = $stmt->fetchColumn();
    }
    return $stats;
}

/**
 * Obtiene TODOS los formularios de un vendedor, sin filtro de tiempo.
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $vendedor_id ID del vendedor.
 * @return array
 */
function get_formularios_by_vendedor(PDO $pdo, int $vendedor_id): array {
    $stmt = $pdo->prepare("
        SELECT * FROM formularios 
        WHERE vendedor_id = ? 
        ORDER BY FIELD(estado, 'en revision', 'aprobado', 'rechazado'), fecha_creacion DESC
    ");
    $stmt->execute([$vendedor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene los formularios activos (últimas 72hs) para un supervisor o superusuario.
 * @param PDO $pdo Conexión a la base de datos.
 * @return array
 */
function get_all_formularios(PDO $pdo): array {
    $sql = "
        SELECT f.*, CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre
        FROM formularios f
        JOIN usuarios u ON f.vendedor_id = u.id
        WHERE
            (
                f.estado = 'en revision' OR
                (f.estado IN ('aprobado', 'rechazado') AND f.fecha_actualizacion_estado >= NOW() - INTERVAL 72 HOUR)
            )
        ORDER BY FIELD(f.estado, 'en revision', 'aprobado', 'rechazado'), f.fecha_creacion DESC
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene el log de actividad de los formularios.
 * @param PDO $pdo Conexión a la base de datos.
 * @return array
 */
function get_activity_log(PDO $pdo): array {
    $sql = "
        SELECT
            f.id,
            f.cliente_apellido_nombre,
            f.estado,
            f.fecha_actualizacion_estado,
            CONCAT(u.nombre, ' ', u.apellido) AS supervisor_nombre
        FROM formularios f
        JOIN usuarios u ON f.supervisor_id_accion = u.id
        WHERE f.fecha_actualizacion_estado IS NOT NULL
        ORDER BY f.fecha_actualizacion_estado DESC
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene todos los usuarios (para el superusuario).
 * @param PDO $pdo Conexión a la base de datos.
 * @return array
 */
function get_all_users(PDO $pdo): array {
    $stmt = $pdo->query("SELECT id, email, dni, apellido, nombre, rol, celular FROM usuarios WHERE rol != 'superusuario' ORDER BY apellido, nombre");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Genera un reporte de actividad de vendedores, ordenado por total de cargas.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string $fecha_desde Fecha de inicio en formato 'Y-m-d'.
 * @param string $fecha_hasta Fecha de fin en formato 'Y-m-d'.
 * @return array
 */
function get_vendedores_report(PDO $pdo, string $fecha_desde, string $fecha_hasta): array {
    $sql = "
        SELECT
            u.id, u.nombre, u.apellido,
            COUNT(f.id) AS cargadas,
            SUM(CASE WHEN f.estado = 'aprobado' THEN 1 ELSE 0 END) AS aprobadas,
            SUM(CASE WHEN f.estado = 'rechazado' THEN 1 ELSE 0 END) AS rechazadas,
            SUM(CASE WHEN f.estado = 'en revision' THEN 1 ELSE 0 END) AS en_revision
        FROM usuarios u
        LEFT JOIN formularios f ON u.id = f.vendedor_id AND f.fecha_creacion BETWEEN :fecha_desde AND :fecha_hasta
        WHERE u.rol = 'vendedor'
        GROUP BY u.id, u.nombre, u.apellido
        ORDER BY cargadas DESC, u.apellido, u.nombre
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'fecha_desde' => $fecha_desde . ' 00:00:00',
        'fecha_hasta' => $fecha_hasta . ' 23:59:59'
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene el detalle de ventas de un vendedor específico en un rango de fechas.
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $vendedor_id ID del vendedor.
 * @param string $fecha_desde Fecha de inicio en formato 'Y-m-d'.
 * @param string $fecha_hasta Fecha de fin en formato 'Y-m-d'.
 * @return array
 */
function get_sales_details_for_vendor_report(PDO $pdo, int $vendedor_id, string $fecha_desde, string $fecha_hasta): array {
    $sql = "
        SELECT id, cliente_apellido_nombre, estado, fecha_creacion, cliente_domicilio, cliente_whatsapp, articulo_venta
        FROM formularios
        WHERE
            vendedor_id = :vendedor_id AND
            fecha_creacion BETWEEN :fecha_desde AND :fecha_hasta
        ORDER BY FIELD(estado, 'en revision', 'aprobado', 'rechazado'), fecha_creacion DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'vendedor_id' => $vendedor_id,
        'fecha_desde' => $fecha_desde . ' 00:00:00',
        'fecha_hasta' => $fecha_hasta . ' 23:59:59'
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene solo los formularios que están "en revision" para el Verificador.
 * @param PDO $pdo Conexión a la base de datos.
 * @return array
 */
function get_formularios_para_verificador(PDO $pdo): array {
    $sql = "
        SELECT f.*, CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre
        FROM formularios f
        JOIN usuarios u ON f.vendedor_id = u.id
        WHERE f.estado = 'en revision'
        ORDER BY f.fecha_creacion ASC
    ";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Obtiene el historial de formularios aprobados por un verificador específico.
 * @param PDO $pdo Conexión a la base de datos.
 * @param int $verificador_id ID del verificador.
 * @return array
 */
function get_aprobados_by_verificador(PDO $pdo, int $verificador_id): array {
    $sql = "
        SELECT f.id, f.cliente_apellido_nombre, f.fecha_actualizacion_estado,
               CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre
        FROM formularios f
        JOIN usuarios u ON f.vendedor_id = u.id
        WHERE f.estado = 'aprobado' AND f.supervisor_id_accion = :verificador_id
        ORDER BY f.fecha_actualizacion_estado DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['verificador_id' => $verificador_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Genera un reporte de ventas detallado dentro de un rango de fechas.
 * @param PDO $pdo Conexión a la base de datos.
 * @param string $fecha_desde Fecha de inicio en formato 'Y-m-d'.
 * @param string $fecha_hasta Fecha de fin en formato 'Y-m-d'.
 * @return array
 */
function get_sales_report_by_date(PDO $pdo, string $fecha_desde, string $fecha_hasta): array {
    $sql = "
        SELECT
            f.id,
            f.cliente_apellido_nombre,
            f.articulo_venta,
            f.estado,
            f.estado_entrega,
            f.fecha_creacion,
            f.fecha_actualizacion_estado,
            CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre
        FROM
            formularios f
        JOIN
            usuarios u ON f.vendedor_id = u.id
        WHERE
            f.fecha_creacion BETWEEN :fecha_desde AND :fecha_hasta
        ORDER BY
            f.fecha_creacion DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'fecha_desde' => $fecha_desde . ' 00:00:00',
        'fecha_hasta' => $fecha_hasta . ' 23:59:59'
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
