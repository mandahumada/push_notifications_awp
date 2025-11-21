<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require '../bd/conexion_bd.php';
$obj = new BD_PDO();

try {
    $idExamen = $_GET['idExamen'] ?? null;

    if (!$idExamen) {
        throw new Exception("Falta el parámetro idExamen");
    }

    $query = "SELECT idPregunta, idExamen, pregunta, valor FROM preguntas WHERE idExamen = ?";
    $preguntas = $obj->ejecutarInstruccion($query, [$idExamen]);

    echo json_encode([
        'success' => true,
        'data' => $preguntas
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
