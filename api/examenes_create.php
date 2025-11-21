<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../bd/conexion_bd.php';
require_once 'funciones_notificaciones.php';

$obj = new BD_PDO();

try {
    $data = json_decode(file_get_contents("php://input"), true);
    $titulo = $data['tituloExamen'] ?? '';
    $descripcion = $data['descripcion'] ?? '';

    if (empty($titulo)) {
        throw new Exception("El titulo es requerido");
    }

    $query = "INSERT INTO examenes (tituloExamen, descripcion) VALUES (?, ?)";
    $obj->ejecutarInstruccion($query, [$titulo, $descripcion]);

    $usuarios = $obj->ejecutarInstruccion(
        "SELECT token FROM usuarios WHERE token IS NOT NULL AND token != ''"
    );
    
    $tokens = array_column($usuarios, 'token');

    $notificacionInfo = ['enviadas' => 0, 'debug' => []];
    
    $notificacionInfo['debug']['total_usuarios'] = count($usuarios);
    $notificacionInfo['debug']['total_tokens'] = count($tokens);

    if (!empty($tokens)) {
        try {
            $resultados = enviarNotificacionMultiple(
                $tokens,
                'nuevo examen disponible',
                $titulo,
                ['tipo' => 'nuevo_examen', 'titulo' => $titulo]
            );
            
            $notificacionInfo = [
                'enviadas' => $resultados['exitosos'],
                'fallidas' => $resultados['fallidos'],
                'total_tokens' => count($tokens),
                'detalles' => $resultados['detalles'] ?? [],
                'error' => $resultados['error'] ?? null
            ];
            
            
            $logMsg = date('Y-m-d H:i:s') . " | Examen: $titulo | Exitosas: {$resultados['exitosos']} | Fallidas: {$resultados['fallidos']}\n";
            file_put_contents(__DIR__ . '/log_notificaciones.txt', $logMsg, FILE_APPEND);
            
        } catch (Exception $e) {
            $notificacionInfo['error'] = $e->getMessage();
            file_put_contents(__DIR__ . '/log_notificaciones.txt', date('Y-m-d H:i:s') . " | ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    } else {
        $notificacionInfo['mensaje'] = 'No hay usuarios con tokens registrados';
    }

    echo json_encode([
        'success' => true,
        'message' => 'Examen creado exitosamente',
        'notificaciones' => $notificacionInfo
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}