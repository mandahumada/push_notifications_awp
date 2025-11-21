<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit(); }

require '../bd/conexion_bd.php';
require_once 'funciones_notificaciones.php';

$obj = new BD_PDO();

try{
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['idLlenado'] ?? null;
    $clave = $data['Clave'] ?? '';
    $idRespuesta = $data['idRespuesta'] ?? '';

    if(!$id) throw new Exception("falta el id");
    if(empty($clave)) throw new Exception("la clave es requerida");

    $query = "UPDATE llenados SET Clave=?, idRespuesta=? WHERE idLlenado=?";
    $obj->ejecutarInstruccion($query, [$clave, $idRespuesta, $id]);

    $usuarios = $obj->ejecutarInstruccion(
        "SELECT token FROM usuarios WHERE token IS NOT NULL AND token != ''"
    );
    
    $tokens = array_column($usuarios, 'token');

    if (!empty($tokens)) {
        try {
            $resultados = enviarNotificacionMultiple(
                $tokens,
                'llenado actualizado',
                "se actualizo un llenado (clave: $clave)",
                ['tipo' => 'llenado_actualizado', 'idLlenado' => $id]
            );
            
            // Log opcional
            $logMsg = date('Y-m-d H:i:s') . " | Llenado actualizado | Exitosas: {$resultados['exitosos']} | Fallidas: {$resultados['fallidos']}\n";
            file_put_contents(__DIR__ . '/log_notificaciones.txt', $logMsg, FILE_APPEND);
            
        } catch (Exception $e) {
            // Si falla la notificación, no bloquear la actualización
            error_log("Error enviando notificación: " . $e->getMessage());
        }
    }

    echo json_encode(['success'=>true,'message'=>'Llenado actualizado']);
    
} catch(Exception $e){
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}