<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(1);

try {
    require '../bd/conexion_bd.php';
    $obj = new BD_PDO();

    $consulta = "SELECT idNotificacion, titulo, mensaje, fecha_creacion, leida, tipo, idRelacionado 
                 FROM notificaciones 
                 ORDER BY fecha_creacion DESC";
    $resultado = $obj->ejecutarInstruccion($consulta);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $resultado ?: []
    ]);

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => []
    ]);
}