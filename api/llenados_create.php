<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

require '../bd/conexion_bd.php';
$obj = new BD_PDO();

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $clave = $data['Clave'] ?? '';
    $idRespuesta = $data['idRespuesta'] ?? '';

    if (empty($clave)) throw new Exception("La Clave es requerida");
    if (empty($idRespuesta)) throw new Exception("El ID de respuesta es requerido");

    $query = "INSERT INTO llenados (Clave, idRespuesta) VALUES (?, ?)";
    $obj->ejecutarInstruccion($query, [$clave, $idRespuesta]);

    echo json_encode(['success' => true, 'message' => 'Llenado creado correctamente']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
