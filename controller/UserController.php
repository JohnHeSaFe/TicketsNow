<?php
require_once 'User.php';
class UserController
{
    private $conn;

    public function __construct()
    {
        try {
            // Conectar sin base de datos primero
            $pdo = new PDO("mysql:host=localhost", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Crear la base de datos si no existe
            $pdo->exec("CREATE DATABASE IF NOT EXISTS ticketsnow");

            // Ahora conectar a la base de datos
            $this->conn = new PDO("mysql:host=localhost;dbname=ticketsnow", "root", "");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Crear tabla users si no existe (opcional)
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id_user INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    name VARCHAR(100),
                    first_surname VARCHAR(100),
                    second_surname VARCHAR(100),
                    birth_date DATE,
                    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
                    id_role INT,
                    profile_photo VARCHAR(255)
                )
            ");
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }


    public function login(): string
    {
        $error = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = $_POST["email"] ?? "";
            $password = $_POST["password"] ?? "";

            if (empty($email) || empty($password)) {
                return "Todos los campos son obligatorios.";
            }

            $stmt = $this->conn->prepare("SELECT id_user, name, first_surname, second_surname, birth_date, gender, id_role, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $user = new User(
                    $user['id_user'],
                    $email,
                    $user['password'],
                    $user['name'],
                    $user['first_surname'],
                    $user['second_surname'],
                    $user['birth_date'],
                    $user['gender'],
                    $user['id_role'],
                    null 
                );

                $_SESSION['logged_in'] = true;
                $_SESSION['user'] = $user;

                header("Location: ./profile.php");
                exit;
            }

            $error = "Email o contraseña incorrectos.";
        }

        return $error;
    }

    public function emailExists($email): bool
    {
        $stmt = $this->conn->prepare("SELECT id_user FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    public function updatePassword($email, $newPassword): void
    {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $email]);
    }

    public function logout(): void {}

    public function registerUser($data)
    {
        return $this->register($data, 1);
    }

    public function registerArtist($data)
    {
        return $this->register($data, 2);
    }

    public function registerAdmin($data)
    {
        return $this->register($data, 3);
    }

    private function register($data, $role_id)
    {
        if (
            empty($data['email']) || empty($data['password']) ||
            empty($data['nombre']) || empty($data['primer_apellido']) || 
            empty($data['segundo_apellido']) || empty($data['birth_date'])
        ) {
            return "Todos los campos son obligatorios.";
        }

        $pattern = "/^(?=.*\d)[A-Za-z\d]{6}$/";
        if (!preg_match($pattern, $data['password'])) {
            return "La contraseña es incorrecta. Debe ser de 6 caracteres y que contenga al menos 1 numero.";
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "El email no es válido.";
        }

        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $name = $data['nombre'];
        $first_surname = $data['primer_apellido'];
        $second_surname = $data['segundo_apellido'];

        $birth_date = $data['birth_date'];
        $current_date = date("Y-m-d");
        if ($birth_date > $current_date) {
            return "Fecha de nacimiento no válido.";
        }

        $gender = $data['gender'];
        $gender_options = ['male', 'female', 'other', 'prefer_not_to_say'];
        if (!in_array($gender, $gender_options)) {
            return "Opcion de genero no válido.";
        }

        $profilePhoto = '';

        $user = new User(null, $email, $password, $name, $first_surname, $second_surname, $birth_date, $gender, $role_id, $profilePhoto);

        try {
            $check = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->fetchColumn() > 0) {
                return "El correo electrónico ya está registrado.";
            }

            $stmt = $this->conn->prepare("INSERT INTO users (email, password, name, first_surname, second_surname, birth_date, gender, id_role, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user->getEmail(), $user->getPassword(), $user->getName(), $user->getFirst_surname(), $user->getSecond_surname(), $user->getBirth_date(), $user->getGender(), $user->getId_role(), $user->getProfile_photo()]);

            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            return "Error al registrar: " . $e->getMessage();
        }
    }

    public function deleteUser($id_user)
    {
        try {
            // Obtener la foto actual del usuario
            $stmt = $this->conn->prepare("SELECT profile_photo FROM users WHERE id_user = ?");
            $stmt->execute([$id_user]);
            $photo = $stmt->fetchColumn();

            // Eliminar usuario
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id_user = ?");
            $stmt->execute([$id_user]);

            // Eliminar foto si no es la predeterminada
            $defaultPhoto = '../../media/img/Interfaces/user_icon.png';
            if ($photo && $photo !== $defaultPhoto) {
                $filePath = __DIR__ . '/../../' . str_replace('../', '', $photo);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Destruir sesión
            session_start();
            session_destroy();

            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            die("Error al eliminar el usuario: " . $e->getMessage());
        }
    }

}
