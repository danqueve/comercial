<?php
// Ya no es necesario incluir 'config.php' aquí, porque index.php ya lo hizo.

// Verificamos que el usuario haya iniciado sesión.
check_login();

// Verificamos que se haya proporcionado un ID de cliente en la URL.
if (isset($_GET['id'])) {
    $cliente_id = $_GET['id'];

    try {
        // Iniciamos una transacción para asegurar la integridad de los datos.
        $pdo->beginTransaction();

        // Paso 1: Eliminar todos los créditos asociados a este cliente.
        $sql_delete_creditos = "DELETE FROM creditos WHERE cliente_id = ?";
        $stmt_creditos = $pdo->prepare($sql_delete_creditos);
        $stmt_creditos->execute([$cliente_id]);

        // Paso 2: Una vez eliminados los créditos, eliminar el cliente.
        $sql_delete_cliente = "DELETE FROM clientes WHERE id = ?";
        $stmt_cliente = $pdo->prepare($sql_delete_cliente);
        $stmt_cliente->execute([$cliente_id]);

        // Si ambas eliminaciones fueron exitosas, confirmamos la transacción.
        $pdo->commit();

        // Redirigimos de vuelta a la lista de clientes con un mensaje de éxito.
        header("Location: index.php?page=clientes&status=deleted");
        exit;

    } catch (PDOException $e) {
        // Si ocurre algún error, revertimos todos los cambios.
        $pdo->rollBack();
        // Redirigimos con un mensaje de error.
        header("Location: index.php?page=clientes&status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Si no se proporciona un ID, redirigimos a la página de clientes.
    header("Location: index.php?page=clientes");
    exit;
}
?>

