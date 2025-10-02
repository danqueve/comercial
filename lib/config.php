<?php
// lib/config.php

// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
define('DB_HOST', 'localhost'); // Tu host, usualmente localhost
define('DB_NAME', 'c2801446_imperio');   // El nombre de tu base de datos
define('DB_USER', 'c2801446_imperio');      // Tu usuario de MySQL
define('DB_PASS', 'leTU32tisa');          // Tu contraseña de MySQL

// --- CONFIGURACIÓN GENERAL ---
define('SITE_URL', 'http://localhost/imperio'); // URL de tu proyecto

// --- INICIAR SESIÓN ---
// Asegura que la sesión se inicie en todas las páginas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CONEXIÓN PDO A LA BASE DE DATOS ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En un entorno de producción, no muestres detalles del error.
    // Loguea el error y muestra un mensaje genérico.
    die("Error: No se pudo conectar a la base de datos. " . $e->getMessage());
}
