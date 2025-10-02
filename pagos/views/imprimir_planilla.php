<?php
// Incluimos el archivo de configuración.
// La ruta es '../config.php' porque este archivo se encuentra dentro de la carpeta /views.
require_once '../config.php';

// Obtenemos la zona y el día de los parámetros de la URL.
// Usamos htmlspecialchars para prevenir ataques XSS.
$zona_seleccionada = htmlspecialchars($_GET['zona'] ?? 0);
$dia_seleccionado = htmlspecialchars($_GET['dia'] ?? '');

// --- CONSULTA A LA BASE DE DATOS ACTUALIZADA ---
// Ahora seleccionamos también la dirección y el teléfono del cliente.
$sql = "SELECT c.nombre, c.direccion, c.telefono, cr.cuotas_pagadas, cr.total_cuotas, cr.monto_cuota, cr.ultimo_pago, cr.estado 
        FROM creditos cr 
        JOIN clientes c ON cr.cliente_id = c.id 
        WHERE cr.zona = ? AND cr.dia_pago = ? AND cr.estado = 'Activo' 
        ORDER BY c.nombre ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$zona_seleccionada, $dia_seleccionado]);
    $clientes_filtrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al generar la planilla: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Cobro - Zona <?= $zona_seleccionada ?></title>
    <!-- Estilos CSS optimizados para impresión -->
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10px; 
            margin: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 4px; 
            text-align: left; 
            word-wrap: break-word;
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        h1 { margin: 0; font-size: 20px; }
        h2 { margin: 0; font-size: 16px; font-weight: normal; }
        @media print {
            body { 
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact; 
                margin: 0;
            }
        }
    </style>
</head>
<!-- El evento onload ejecuta el diálogo de impresión automáticamente -->
<body onload="window.print()">
    <div class="header">
        <h1>Planilla de Cobro</h1>
        <h2>Zona: <?= $zona_seleccionada ?> - Día: <?= $dia_seleccionado ?></h2>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 25%;">Dirección</th>
                <th style="width: 15%;">Celular</th>
                <th style="width: 10%;">Cuotas</th>
                <th style="width: 15%;">Monto Cuota</th>
                <th style="width: 10%;">Días Atraso</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clientes_filtrados)): ?>
                <tr><td colspan="6" style="text-align: center;">No hay clientes activos para esta ruta.</td></tr>
            <?php else: ?>
                <?php foreach ($clientes_filtrados as $cliente): ?>
                    <?php
                    $atraso = calcularAtraso($cliente['ultimo_pago'], $cliente['estado']);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                        <td><?= htmlspecialchars($cliente['direccion'] ?? '') ?></td>
                        <td><?= htmlspecialchars($cliente['telefono'] ?? '') ?></td>
                        <td style="text-align: center;"><?= $cliente['cuotas_pagadas'] ?> / <?= $cliente['total_cuotas'] ?></td>
                        <td><?= formatCurrency($cliente['monto_cuota']) ?></td>
                        <td style="text-align: center; font-weight: bold;"><?= $atraso['estado'] == 'Atrasado' ? $atraso['dias'] : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
