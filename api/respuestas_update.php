<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require '../bd/conexion_bd.php';
$obj = new BD_PDO();

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $idRespuesta = $data['idRespuesta'] ?? '';
    $respuesta = $data['respuesta'] ?? '';
    $correcta = isset($data['correcta']) ? (int)$data['correcta'] : 0;

    if (empty($idRespuesta) || empty($respuesta)) {
        throw new Exception("El ID de la respuesta y el texto son requeridos");
    }

    $query = "UPDATE respuestas SET respuesta = ?, correcta = ? WHERE idRespuesta = ?";
    $obj->ejecutarInstruccion($query, [$respuesta, $correcta, $idRespuesta]);

    echo json_encode([
        'success' => true,
        'message' => 'Respuesta actualizada correctamente'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>