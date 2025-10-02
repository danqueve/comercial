<?php
// Incluye el archivo de configuración al principio de todo.
// Esto nos da acceso a la conexión a la base de datos ($pdo) y a las funciones auxiliares.
require_once 'config.php';

// Llama a la función check_login() para proteger la página.
// Si el usuario no ha iniciado sesión, será redirigido a login.php y el script se detendrá.
check_login();

// --- ROUTER SIMPLE ---
// Determina qué página se debe mostrar basándose en el parámetro 'page' de la URL.
// Si no se especifica ninguna página, carga 'rutas' por defecto.
$page = $_GET['page'] ?? 'rutas';

// --- CASOS ESPECIALES PARA DESCARGA ---
// Estos scripts generan archivos y no necesitan el header/footer HTML.
// Los manejamos aquí antes de imprimir cualquier otra cosa.
if ($page == 'descargar_plantilla') {
    include 'views/descargar_plantilla.php';
    exit; // Detenemos la ejecución para que no se cargue el resto de la página.
}
if ($page == 'exportar_datos') {
    include 'views/exportar_datos.php';
    exit;
}

// Incluye el encabezado HTML común (<!DOCTYPE>, <head>, barra de navegación, etc.).
include 'partials/header.php';

// --- CARGADOR DE VISTAS ---
// Este switch actúa como el "cargador" de las diferentes secciones de la aplicación.
switch ($page) {
    case 'clientes':
        // Carga la lista de clientes.
        include 'views/clientes.php';
        break;
    
    case 'agregar_cliente':
        // Carga el formulario para agregar un nuevo cliente.
        include 'views/agregar_cliente.php';
        break;

    case 'editar_cliente':
        // Carga el formulario para editar un cliente existente.
        include 'views/editar_cliente.php';
        break;

    case 'eliminar_cliente':
        // Carga la lógica para eliminar un cliente.
        include 'views/eliminar_cliente.php';
        break;

    case 'atrasados':
        // Carga la vista de clientes con pagos atrasados.
        include 'views/atrasados.php';
        break;

    case 'importar_clientes':
        // Carga la página para importar clientes desde CSV.
        include 'views/importar_clientes.php';
        break;

    case 'calculadora':
        // Carga la herramienta de calculadora.
        include 'views/calculadora.php';
        break;

    case 'reportes':
        // Carga la página de reportes.
        include 'views/reportes.php';
        break;
    
    case 'rutas':
    default:
        // Carga la vista principal de rutas de cobro por defecto.
        include 'views/rutas.php';
        break;
}

// Incluye el pie de página común (cierre de etiquetas HTML, scripts de JavaScript, etc.).
include 'partials/footer.php';

?>
