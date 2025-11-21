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
require_once 'funciones_notificaciones.php';

$obj = new BD_PDO();

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $idRespuesta = $data['idRespuesta'] ?? '';

    if (empty($idRespuesta)) throw new Exception("ID de respuesta requerido");

    $query = "DELETE FROM respuestas WHERE idRespuesta = ?";
    $obj->ejecutarInstruccion($query, [$idRespuesta]);

    $usuarios = $obj->ejecutarInstruccion(
        "SELECT token FROM usuarios WHERE token IS NOT NULL AND token != ''"
    );
    
    $tokens = array_column($usuarios, 'token');

    if (!empty($tokens)) {
        try {
            enviarNotificacionMultiple(
                $tokens,
                'respuesta eliminada',
                'se eliminó una opción de respuesta',
                ['tipo' => 'respuesta_eliminada', 'idRespuesta' => (string)$idRespuesta]
            );
        } catch (Exception $e) {
            error_log("Error enviando notificación: " . $e->getMessage());
        }
    }

    echo json_encode(['success' => true, 'message' => 'Respuesta eliminada correctamente']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}