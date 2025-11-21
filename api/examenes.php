<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(1);

try {
    require '../bd/conexion_bd.php';
    $obj = new BD_PDO();

    $consulta_examenes = "SELECT idExamen, tituloExamen, descripcion FROM examenes";
    $resultado_examenes = $obj->ejecutarInstruccion($consulta_examenes);
    
    if (empty($resultado_examenes)) {
        $resultado_examenes = array();
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $resultado_examenes
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'error al obtener examenes',
        'message' => $e->getMessage()
    ]);
}