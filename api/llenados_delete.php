<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require '../bd/conexion_bd.php';
    $obj = new BD_PDO();

    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['idLlenado'])) {
        throw new Exception("ID del llenado no proporcionado");
    }

    $idLlenado = (int)$data['idLlenado'];
    if ($idLlenado <= 0) {
        throw new Exception("ID del llenado inválido");
    }

    $query = "DELETE FROM llenados WHERE idLlenado = ?";
    $resultado = $obj->ejecutarInstruccion($query, [$idLlenado]);

    echo json_encode([
        'success' => true,
        'message' => "Llenado eliminado correctamente",
        'idLlenado' => $idLlenado
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
