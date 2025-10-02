<?php
// register.php
require_once 'lib/config.php';
require_once 'lib/functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear datos
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $dni = filter_input(INPUT_POST, 'dni', FILTER_SANITIZE_STRING);
    $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $celular = filter_input(INPUT_POST, 'celular', FILTER_SANITIZE_STRING);
    $domicilio = filter_input(INPUT_POST, 'domicilio', FILTER_SANITIZE_STRING);
    $localidad = filter_input(INPUT_POST, 'localidad', FILTER_SANITIZE_STRING);

    // Validaciones
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El formato del email es inválido.";
    }
    if (empty($dni) || empty($nombre) || empty($apellido)) {
        $errors[] = "Nombre, Apellido y DNI son campos obligatorios.";
    }

    // Verificar si DNI o email ya existen
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR dni = ?");
        $stmt->execute([$email, $dni]);
        if ($stmt->fetch()) {
            $errors[] = "El email o DNI ya se encuentra registrado en el sistema.";
        }
    }

    // Si no hay errores, proceder con la inserción
    if (empty($errors)) {
        // La columna 'password' se omite, ya que ahora es opcional y no se usa.
        $sql = "INSERT INTO usuarios (email, dni, apellido, nombre, celular, domicilio, localidad, rol) VALUES (?, ?, ?, ?, ?, ?, ?, 'vendedor')";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$email, $dni, $apellido, $nombre, $celular, $domicilio, $localidad]);
            $success = "¡Registro exitoso! Ahora puedes <a href='login.php' class='font-bold underline'>iniciar sesión</a> usando tu DNI como usuario y clave.";
        } catch (PDOException $e) {
            // En un entorno de producción, sería mejor registrar el error en un log.
            // error_log($e->getMessage());
            $errors[] = "Ocurrió un error al intentar registrar el usuario. Por favor, inténtalo de nuevo.";
        }
    }
}

include 'partials/header.php';
?>
<div class="flex items-center justify-center">
    <div class="w-full max-w-2xl">
        <div class="bg-gray-800 border border-gray-700 shadow-lg rounded-xl p-8">
            <h2 class="text-2xl font-bold text-center text-gray-200 mb-6">Registro de Vendedor</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-900/50 border-l-4 border-red-500 text-red-300 p-4 mb-6" role="alert">
                    <p class="font-bold">Error al registrar</p>
                    <ul><?php foreach ($errors as $error) echo "<li class='list-disc ml-4'>" . htmlspecialchars($error) . "</li>"; ?></ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-900/50 border-l-4 border-green-500 text-green-300 p-4 text-center" role="alert">
                    <p class="font-bold text-lg">¡Éxito!</p>
                    <p><?= $success ?></p>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="block text-gray-400 text-sm font-semibold mb-2">Nombre</label>
                            <input type="text" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="nombre" name="nombre" required>
                        </div>
                        <div>
                            <label for="apellido" class="block text-gray-400 text-sm font-semibold mb-2">Apellido</label>
                            <input type="text" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="apellido" name="apellido" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-gray-400 text-sm font-semibold mb-2">Email</label>
                            <input type="email" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="email" name="email" required>
                        </div>
                        <div>
                            <label for="dni" class="block text-gray-400 text-sm font-semibold mb-2">DNI (será tu usuario y clave)</label>
                            <input type="text" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="dni" name="dni" required>
                        </div>
                    </div>
                    <div>
                        <label for="celular" class="block text-gray-400 text-sm font-semibold mb-2">Número de Celular</label>
                        <input type="text" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="celular" name="celular" required>
                    </div>
                    <div>
                        <label for="domicilio" class="block text-gray-400 text-sm font-semibold mb-2">Domicilio</label>
                        <input type="text" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="domicilio" name="domicilio" required>
                    </div>
                    <div>
                        <label for="localidad" class="block text-gray-400 text-sm font-semibold mb-2">Localidad</label>
                        <input type="text" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" id="localidad" name="localidad" required>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-md hover:bg-blue-700 transition duration-300">Registrarse</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>
