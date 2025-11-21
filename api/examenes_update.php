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
    $id = $data['idExamen'] ?? null;
    $titulo = $data['tituloExamen'] ?? '';
    $descripcion = $data['descripcion'] ?? '';

    if (!$id) throw new Exception("Falta el ID del examen");
    if (empty($titulo)) throw new Exception("El título es requerido");

    $query = "UPDATE examenes SET tituloExamen = ?, descripcion = ? WHERE idExamen = ?";
    $obj->ejecutarInstruccion($query, [$titulo, $descripcion, $id]);

    echo json_encode(['success' => true, 'message' => 'Examen actualizado']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
