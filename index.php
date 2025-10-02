<?php
// index.php
// Este archivo actúa como un enrutador simple.

// 1. Cargar la configuración principal que inicia la sesión.
require_once 'lib/config.php';

// 2. Comprobar si existe una sesión de usuario activa.
if (isset($_SESSION['user_id'])) {
    // Si el usuario ya ha iniciado sesión, lo redirige a su panel principal.
    header('Location: dashboard.php');
    exit();
} else {
    // Si no ha iniciado sesión, lo envía a la página de login.
    header('Location: login.php');
    exit();
}
