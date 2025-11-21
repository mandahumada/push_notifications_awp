<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

try {
    require_once "../bd/conexion_bd.php";
    
    $bd = new BD_PDO();
    
    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'] ?? '';
    $token = $data['token'] ?? '';

    if (!$username || !$token) {
        throw new Exception("Faltan datos: username o token vacío");
    }

    $consulta = "UPDATE usuarios SET token = :token WHERE username = :username";
    $stmt = $bd->ejecutarInstruccion($consulta, [
        ':token' => $token,
        ':username' => $username
    ]);

    $verificar = $bd->ejecutarInstruccion(
        "SELECT username FROM usuarios WHERE username = :username", 
        [':username' => $username]
    );

    if (count($verificar) > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Token actualizado correctamente"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Usuario no encontrado en la BD"
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
