<?php
// Inicia la sesión en cada página que incluya este archivo.
// Es fundamental para el sistema de login.
session_start();

// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
// Define constantes para los detalles de la conexión.
// Estos son los nuevos datos que proporcionaste.
define('DB_HOST', 'localhost');
define('DB_USER', 'c2801446_cobros'); // Usuario de tu base de datos
define('DB_PASS', 'sevageGU85');         // Contraseña de tu base de datos
define('DB_NAME', 'c2801446_cobros'); // El nombre de la base de datos que creaste

// --- CONEXIÓN A LA BASE DE DATOS (PDO) ---
try {
    // Intenta crear una nueva instancia de PDO para la conexión.
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Configura PDO para que lance excepciones en caso de error.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Asegura que la comunicación con la base de datos se realice en UTF-8.
    $pdo->exec("SET CHARACTER SET utf8");

} catch (PDOException $e) {
    // Si la conexión falla, se muestra un mensaje de error claro y se detiene la ejecución.
    die("ERROR: No se pudo conectar a la base de datos. " . $e->getMessage());
}

// --- FUNCIONES AUXILIARES GLOBALES ---

/**
 * Verifica si el usuario ha iniciado sesión.
 * Si no hay una sesión activa, lo redirige a la página de login.
 */
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Formatea un número como moneda local (Pesos Argentinos).
 * @param float $value El número a formatear.
 * @return string La cadena de texto formateada como moneda.
 */
function formatCurrency($value) {
    return '$' . number_format($value, 0, ',', '.');
}

/**
 * Calcula los días de atraso de un crédito basándose en su último pago.
 * @param string|null $fecha_ultimo_pago_str La fecha del último pago en formato 'Y-m-d'.
 * @param string $estado El estado actual del crédito ('Activo' o 'Pagado').
 * @return array Un array con los días de atraso y el estado calculado.
 */
function calcularAtraso($fecha_ultimo_pago_str, $estado) {
    if ($estado == 'Pagado') {
        return ['dias' => 0, 'estado' => 'Pagado'];
    }
    // Si nunca ha pagado, se considera muy atrasado.
    if (empty($fecha_ultimo_pago_str)) {
        return ['dias' => 999, 'estado' => 'Atrasado'];
    }
    
    // Usamos la fecha actual del servidor para un cálculo en tiempo real.
    $hoy = new DateTime(); 
    $ultimo_pago = new DateTime($fecha_ultimo_pago_str);
    
    // La siguiente cuota vence 7 días después del último pago.
    $proximo_vencimiento = (clone $ultimo_pago)->modify('+7 days');

    if ($hoy > $proximo_vencimiento) {
        $diferencia = $hoy->diff($proximo_vencimiento);
        return ['dias' => $diferencia->days, 'estado' => 'Atrasado'];
    } else {
        return ['dias' => 0, 'estado' => 'Al día'];
    }
}

?>
