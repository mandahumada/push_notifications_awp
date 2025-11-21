<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Cargar la clase de conexión
include_once __DIR__ . "/../bd/conexion_bd.php";
$bd = new BD_PDO(); // crear instancia de la conexión

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

try {
    // 🔹 Consultar el usuario
    $sql = "SELECT * FROM usuarios WHERE username = ?";
    $result = $bd->ejecutarInstruccion($sql, [$username]);

    if (count($result) === 1) {
        $user = $result[0];
        if ($user['password'] === $password) {
            echo json_encode([
                "success" => true,
                "username" => $user['username'],
                "message" => "Inicio de sesión correcto"
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error en la consulta: " . $e->getMessage()]);
}
