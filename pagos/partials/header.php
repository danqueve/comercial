<?php
// Determina la página actual para poder resaltar el enlace activo en el menú.
$page = $_GET['page'] ?? 'rutas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Cobros Imperio</title>
    
    <!-- Librerías Externas -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos Personalizados para el Tema Oscuro -->
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #111827; 
            color: #d1d5db; 
        }
        .table-header-custom { background-color: #374151; }
        .late-payment { color: #f87171; font-weight: bold; }
        .on-time { color: #4ade80; font-weight: bold; }
        .summary-card { 
            background-color: #1f2937; 
            border: 1px solid #374151; 
            border-radius: 0.75rem; 
            padding: 1.5rem; 
            transition: all 0.3s ease; 
        }
        .summary-card:hover { 
            transform: translateY(-5px); 
            border-color: #4b5563; 
        }
        .form-element-dark { 
            background-color: #374151; 
            border-color: #4b5563; 
            color: #d1d5db; 
        }
        .form-element-dark:focus { 
            --tw-ring-color: #3b82f6; 
            border-color: #3b82f6; 
        }
        .table-row-dark { 
            background-color: #1f2937; 
            border-color: #374151; 
        }
        .table-row-dark:hover { background-color: #374151; }
        .nav-link { 
            padding: 8px 16px; 
            border-radius: 6px; 
            transition: background-color 0.3s; 
            color: #d1d5db; 
        }
        .nav-link:hover { background-color: #374151; }
        .nav-link.active { 
            background-color: #3b82f6; 
            color: #ffffff; 
        }
        
        /* --- ESTILOS PARA TABLAS RESPONSIVAS --- */
        @media (max-width: 768px) {
            .responsive-table thead {
                display: none; /* Ocultar cabeceras en móvil */
            }
            .responsive-table tr {
                display: block;
                margin-bottom: 1rem;
                border-radius: 0.5rem;
                overflow: hidden;
                border: 1px solid #374151;
            }
            .responsive-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 1rem;
                text-align: right;
                border-bottom: 1px solid #374151;
            }
            .responsive-table td:last-child {
                border-bottom: none;
            }
            .responsive-table td::before {
                content: attr(data-label);
                font-weight: bold;
                text-align: left;
                margin-right: 1rem;
                color: #9ca3af; /* Color de la etiqueta */
            }
        }

        @media print {
            body { background-color: #ffffff; color: #000000; }
            .no-print { display: none; } 
            .print-container { padding: 0; }
            table { width: 100%; border-collapse: collapse; font-size: 10px; }
            th, td { border: 1px solid #ccc; padding: 4px; } 
            h1, h2 { color: #000; }
        }
    </style>
</head>
<body class="p-4 sm:p-6 md:p-8">
<div class="container mx-auto print-container">
    
    <header class="text-center mb-8 no-print">
        <div class="flex justify-between items-center">
            <div></div>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-100"><i class="fas fa-cash-register text-blue-500"></i> Sistema de Gestión de Cobros</h1>
            <a href="logout.php" class="nav-link" title="Cerrar sesión"><i class="fas fa-sign-out-alt mr-2"></i>Salir</a>
        </div>
        <p class="text-gray-400 mt-2">Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?>.</p>
    </header>
    
    <nav class="bg-gray-800 p-2 rounded-lg shadow-md mb-6 flex justify-center flex-wrap gap-2 sm:gap-4 no-print">
        <a href="index.php?page=rutas" class="nav-link <?= $page == 'rutas' ? 'active' : '' ?>"><i class="fas fa-route mr-2"></i>Rutas</a>
        <a href="index.php?page=clientes" class="nav-link <?= $page == 'clientes' ? 'active' : '' ?>"><i class="fas fa-users mr-2"></i>Clientes</a>
        <a href="index.php?page=atrasados" class="nav-link <?= $page == 'atrasados' ? 'active' : '' ?>"><i class="fas fa-exclamation-triangle mr-2"></i>Atrasados</a>
        <a href="index.php?page=importar_clientes" class="nav-link <?= $page == 'importar_clientes' ? 'active' : '' ?>"><i class="fas fa-file-import mr-2"></i>Importar</a>
        <a href="index.php?page=exportar_datos" class="nav-link"><i class="fas fa-file-export mr-2"></i>Exportar</a>
        <a href="index.php?page=calculadora" class="nav-link"><i class="fa-solid fa-credit-card mr-2"></i>Tarjeta</a>
        <a href="index.php?page=reportes" class="nav-link <?= $page == 'reportes' ? 'active' : '' ?>"><i class="fas fa-chart-pie mr-2"></i>Reportes</a>
    </nav>
