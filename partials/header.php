<?php
// partials/header.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM de Imperio</title>

    <!-- Configuración PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1f2937"> <!-- Color oscuro para la barra de estado -->

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Estilos para el tema oscuro y la fuente -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Estilo para la barra de desplazamiento en modo oscuro */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #2d3748; } /* gris oscuro */
        ::-webkit-scrollbar-thumb { background: #4a5568; border-radius: 4px; } /* gris medio */
        ::-webkit-scrollbar-thumb:hover { background: #718096; } /* gris claro */
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-300 flex flex-col min-h-screen italic">

<nav class="bg-gray-800/70 backdrop-blur-sm text-white shadow-lg sticky top-0 z-50 border-b border-gray-700">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
            <a class="text-xl font-bold tracking-wider" href="index.php">
                <i class="fas fa-chart-line"></i> CRM Ventas
            </a>
            <!-- Menú para pantallas grandes -->
            <div class="hidden md:flex items-center space-x-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"><i class="fas fa-tachometer-alt fa-fw mr-1"></i> Dashboard</a>
                    <?php if (in_array($_SESSION['user_rol'], ['vendedor', 'supervisor'])): ?>
                        <a href="create_form.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"><i class="fas fa-plus-circle fa-fw mr-1"></i> Cargar Formulario</a>
                    <?php endif; ?>
                    <?php if (in_array($_SESSION['user_rol'], ['supervisor', 'superusuario', 'verificador'])): ?>
                        <a href="vendedores_reporte.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"><i class="fas fa-users fa-fw mr-1"></i> Vendedores</a>
                        <a href="ventas_reporte.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"><i class="fas fa-file-invoice-dollar fa-fw mr-1"></i> Reporte Ventas</a>
                        <a href="entregas.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"><i class="fas fa-truck fa-fw mr-1"></i> Entregas</a>
                        <a href="aprobados_historico.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"><i class="fas fa-archive fa-fw mr-1"></i> Historial</a>
                    <?php endif; ?>
                    <?php if ($_SESSION['user_rol'] === 'verificador'): ?>
                        <a href="verificador_aprobados.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"><i class="fas fa-history fa-fw mr-1"></i> Mis Aprobados</a>
                    <?php endif; ?>
                    <a href="login.php?logout=true" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors"><i class="fas fa-sign-out-alt fa-fw mr-1"></i> Salir</a>
                <?php else: ?>
                    <a href="login.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors">Iniciar Sesión</a>
                    <a href="register.php" class="px-3 py-2 rounded-md hover:bg-gray-700 transition-colors">Registrarse</a>
                <?php endif; ?>
            </div>
            <!-- Botón para menú móvil -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-white focus:outline-none"><i class="fas fa-bars text-2xl"></i></button>
            </div>
        </div>
    </div>
    <!-- Menú desplegable para móvil -->
    <div id="mobile-menu" class="hidden md:hidden bg-gray-800 px-4 pt-2 pb-4 space-y-2">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="block px-3 py-2 rounded-md hover:bg-gray-700"><i class="fas fa-tachometer-alt fa-fw mr-1"></i> Dashboard</a>
            <?php if (in_array($_SESSION['user_rol'], ['vendedor', 'supervisor'])): ?>
                <a href="create_form.php" class="block px-3 py-2 rounded-md hover:bg-gray-700"><i class="fas fa-plus-circle fa-fw mr-1"></i> Cargar Formulario</a>
            <?php endif; ?>
            <?php if (in_array($_SESSION['user_rol'], ['supervisor', 'superusuario', 'verificador'])): ?>
                <a href="vendedores_reporte.php" class="block px-3 py-2 rounded-md hover:bg-gray-700"><i class="fas fa-users fa-fw mr-1"></i> Vendedores</a>
                <a href="ventas_reporte.php" class="block px-3 py-2 rounded-md hover:bg-gray-700"><i class="fas fa-file-invoice-dollar fa-fw mr-1"></i> Reporte Ventas</a>
                <a href="entregas.php" class="block px-3 py-2 rounded-md hover:bg-gray-700"><i class="fas fa-truck fa-fw mr-1"></i> Entregas</a>
                <a href="aprobados_historico.php" class="block px-3 py-2 rounded-md hover:bg-gray-700"><i class="fas fa-archive fa-fw mr-1"></i> Historial</a>
            <?php endif; ?>
            <?php if ($_SESSION['user_rol'] === 'verificador'): ?>
                <a href="verificador_aprobados.php" class="block px-3 py-2 rounded-md hover:bg-gray-700"><i class="fas fa-history fa-fw mr-1"></i> Mis Aprobados</a>
            <?php endif; ?>
            <a href="login.php?logout=true" class="block px-3 py-2 rounded-md hover:bg-gray-700"><i class="fas fa-sign-out-alt fa-fw mr-1"></i> Salir</a>
        <?php else: ?>
            <a href="login.php" class="block px-3 py-2 rounded-md hover:bg-gray-700">Iniciar Sesión</a>
            <a href="register.php" class="block px-3 py-2 rounded-md hover:bg-gray-700">Registrarse</a>
        <?php endif; ?>
    </div>
</nav>

<main class="container mx-auto px-4 my-6 flex-grow">