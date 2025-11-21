<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require '../bd/conexion_bd.php';
$obj = new BD_PDO();

try {
    $data = json_decode(file_get_contents("php://input"), true);

    $idPregunta = $data['idPregunta'] ?? '';
    $respuesta = $data['respuesta'] ?? '';
    $correcta = isset($data['correcta']) ? (int)$data['correcta'] : 0;

    if (empty($idPregunta) || empty($respuesta)) {
        throw new Exception("El ID de la pregunta y la respuesta son requeridos");
    }

    $query = "INSERT INTO respuestas (idPregunta, respuesta, correcta) VALUES (?, ?, ?)";
    $obj->ejecutarInstruccion($query, [$idPregunta, $respuesta, $correcta]);

    echo json_encode([
        'success' => true,
        'message' => 'Respuesta creada correctamente'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
