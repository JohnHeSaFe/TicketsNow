<?php
require_once __DIR__ . '/../../../controller/User.php';
session_start();
require_once __DIR__ . '/../../../controller/UserController.php';

if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$controller = new UserController();

// Si se ha enviado el formulario de borrar cuenta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $controller->deleteUser($_SESSION['user']->getId_user());
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=ticketsnow;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$errorMsg = "";

$user = $_SESSION['user'];
$id_user = $user->getId_user();
$name = $user->getName();
$first_surname = $user->getFirst_surname();
$second_surname = $user->getSecond_surname();
$unformatted_birth_date = new DateTime($user->getBirth_date());
$birth_date = $unformatted_birth_date->format("d-m-Y");
$gender = $user->getGender();
switch ($gender) {
    case 'male':
        $gender = 'Hombre';
        break;
    case 'female':
        $gender = 'Mujer';
        break;
    case 'other':
        $gender = 'Otro';
        break;
    case 'prefer_not_to_say':
        $gender = 'No especificado';
        break;
}
$unformatted_phone_number = $user->getPhone_number();
$phone_number = "+" . substr($unformatted_phone_number, 0, 2) . " " . substr($unformatted_phone_number, 2);
$email = $user->getEmail();
$photo = $user->getProfile_photo() ?: '../../media/img/Interfaces/user_icon.png';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_photo"])) {
    $targetDir = __DIR__ . '/../../media/img/profile_pictures/';
    $fileName = time() . '_' . basename($_FILES["profile_photo"]["name"]);
    $targetFile = $targetDir . $fileName;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $imageInfo = getimagesize($_FILES["profile_photo"]["tmp_name"]);
    $validMimeTypes = ['image/jpeg', 'image/png'];

    if (!$imageInfo || !in_array($imageInfo['mime'], $validMimeTypes)) {
        $errorMsg = "❌ El archivo no es una imagen válida (JPG o PNG).";
    } else {
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFile)) {
            $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id_user = :id");
            $stmt->execute([':id' => $id_user]);
            $oldPhoto = $stmt->fetchColumn();

            $defaultPhoto = '../../media/img/Interfaces/user_icon.png';
            if ($oldPhoto && $oldPhoto !== $defaultPhoto) {
                $oldFilePath = __DIR__ . '/../../' . str_replace('../', '', $oldPhoto);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            $relativePath = '../../media/img/profile_pictures/' . $fileName;
            $stmt = $pdo->prepare("UPDATE users SET profile_photo = :photo WHERE id_user = :id");
            $stmt->execute([':photo' => $relativePath, ':id' => $id_user]);
            $photo = $relativePath;
        } else {
            $errorMsg = "❌ Error al subir la imagen. Intenta nuevamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Mi perfil | Tickets Now</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/profile.css">
</head>

<body>
<nav class="navbar">
    <div>
        <a href="../../../view/index.php" class="logo">
            <img src="../../media/img/interfaces/logo.png" alt="Tickets Now">
        </a>
    </div>

    <div class="account-menu">
        <button class="account-button">
            <div class="account-icon">
                <hr>
                <hr>
                <hr>
            </div>
            <div class="account-picture">
                <img src="../../media/img/interfaces/user_icon.png" alt="Usuario">
            </div>
        </button>
        <div class="account-dropdown-menu">
            <ul>
                <?php
                if (isset($_SESSION['logged_in'])) {
                    echo '<li><a href="profile.php">Mi perfil</a></li>';

                    if ($_SESSION['user']->getId_role() == 3) {
                        echo '<li><a href="dashboard.php">Dashboard</a></li>';
                    }

                    echo "<li><a href='#' onclick=\"document.getElementById('logoutForm').submit(); return false;\">Cerrar sesión</a></li>";
                } else {
                    echo "<li><a href='login.php'>Iniciar sesión</a></li>";
                    echo "<li><a href='register_user.php'>Regístrate</a></li>";
                }
                ?>
                <?php if (isset($_SESSION['logged_in'])): ?>
                    <form id="logoutForm" action="logout.php" method="post" style="display: none;"></form>
                <?php endif; ?>
                <hr>
                <li><a href="../html/work_in_progress.html">Ayuda</a></li>
                <li><a href="../php/about.php">Sobre nosotros</a></li>
                <li><a href="#footer">Contacto</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="profile-container">
    <div class="profile-sidebar">
        <img src="<?php echo htmlspecialchars($photo); ?>" alt="Foto de perfil">
        <h3><?php echo htmlspecialchars($name); ?></h3>
    </div>
    <div class="profile-info">
        <h2>👤 Mi perfil</h2>
        <?php if (!empty($errorMsg)): ?>
            <div class="error-msg"><?= htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <div class="info-group">
            <label>Nombre</label>
            <span><?php echo htmlspecialchars($name); ?></span>
        </div>
        <div class="info-group">
            <label>Primer apellido</label>
            <span><?php echo htmlspecialchars($first_surname); ?></span>
        </div>
        <div class="info-group">
            <label>Segundo apellido</label>
            <span><?php echo htmlspecialchars($second_surname); ?></span>
        </div>
        <div class="info-group">
            <label>Correo electrónico</label>
            <span><?php echo htmlspecialchars($email); ?></span>
        </div>
        <div class="info-group">
            <label>Fecha de nacimiento</label>
            <span><?php echo htmlspecialchars($birth_date); ?></span>
        </div>
        <div class="info-group">
            <label>Genero</label>
            <span><?php echo htmlspecialchars($gender); ?></span>
        </div>
        <div class="info-group">
            <label>Número de teléfono</label>
            <span><?php echo htmlspecialchars($phone_number); ?></span>
        </div>

        <?php if ($_SESSION['user']->getId_role() == 3): ?>
            <form action="profile.php" method="post" enctype="multipart/form-data">
                <div class="info-group">
                    <label>Cambiar foto de perfil</label>
                    <input type="file" name="profile_photo" accept="image/*" required>
                </div>
                <button type="submit" class="profile-update-btn">Guardar cambios</button>
            </form>
        <?php endif; ?>

        <!-- Formulario para borrar cuenta -->
        <form method="post" onsubmit="return confirm('¿Estás seguro de que deseas borrar tu cuenta? Esta acción no se puede deshacer.');">
            <input type="hidden" name="delete_account" value="1">
            <button type="submit" class="profile-delete-btn" style="background-color:#e74c3c; color:white; margin-top:20px;">
                🗑️ Borrar cuenta
            </button>
        </form>

    </div>
</div>
</body>

</html>
