<?php
// login.php
require_once 'lib/config.php'; // config.php ya inicia la sesión con session_start()
require_once 'lib/functions.php';

// Lógica de Logout Mejorada
// Se procesa el logout al principio de todo.
if (isset($_GET['logout'])) {
    // 1. Vaciar todas las variables de sesión.
    $_SESSION = array();

    // 2. Destruir la sesión.
    session_destroy();

    // 3. Redirigir a la página de login para un inicio limpio.
    redirect('login.php');
}

// Si, después de intentar el logout, todavía hay una sesión, redirigir al dashboard.
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'] ?? '';
    $clave = $_POST['clave'] ?? '';

    if (empty($dni) || empty($clave)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE dni = ?");
        $stmt->execute([$dni]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $clave === $user['dni']) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            redirect('dashboard.php');
        } else {
            $error = 'Usuario (DNI) o Clave (DNI) incorrectos.';
        }
    }
}

include 'partials/header.php';
?>

<div class="flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl p-8">
            <h2 class="text-2xl font-bold text-center text-gray-200 mb-6">Iniciar Sesión</h2>
            <?php if (!empty($error)): ?>
                <div class="bg-red-900/50 border border-red-700 text-red-300 px-4 py-3 rounded relative mb-4" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="dni" class="block text-gray-400 text-sm font-semibold mb-2">Usuario (tu DNI)</label>
                    <input type="text" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400" id="dni" name="dni" required>
                </div>
                <div class="mb-6">
                    <label for="clave" class="block text-gray-400 text-sm font-semibold mb-2">Clave (repite tu DNI)</label>
                    <input type="password" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="clave" name="clave" required>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">Ingresar</button>
                </div>
            </form>
            <div class="text-center mt-6">
                <p class="text-sm text-gray-400">¿No tienes una cuenta? <a href="register.php" class="text-blue-400 hover:underline">Regístrate aquí</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
