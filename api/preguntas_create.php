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

    $idExamen = $data['idExamen'] ?? null;
    $preguntaTexto = $data['pregunta'] ?? '';
    $valor = $data['valor'] ?? 0;

    if (!$idExamen) {
        throw new Exception("El ID del examen es requerido");
    }
    if (empty($preguntaTexto)) {
        throw new Exception("El texto de la pregunta es requerido");
    }
    if ($valor <= 0) {
        throw new Exception("El valor de la pregunta debe ser mayor a 0");
    }

    $query = "INSERT INTO preguntas (idExamen, pregunta, valor) VALUES (?, ?, ?)";
    $obj->ejecutarInstruccion($query, [$idExamen, $preguntaTexto, $valor]);

    $examenInfo = $obj->ejecutarInstruccion(
        "SELECT tituloExamen FROM examenes WHERE idExamen = ?",
        [$idExamen]
    );
    $tituloExamen = $examenInfo[0]['tituloExamen'] ?? "Examen #$idExamen";

    $usuarios = $obj->ejecutarInstruccion(
        "SELECT token FROM usuarios WHERE token IS NOT NULL AND token != ''"
    );
    
    $tokens = array_column($usuarios, 'token');

    if (!empty($tokens)) {
        try {
            $resultados = enviarNotificacionMultiple(
                $tokens,
                'nueva pregunta agregada',
                "se agregó una pregunta al examen: $tituloExamen",
                [
                    'tipo' => 'nueva_pregunta',
                    'idExamen' => (string)$idExamen,
                    'valor' => (string)$valor
                ]
            );
            
            $logMsg = date('Y-m-d H:i:s') . " | Nueva pregunta en: $tituloExamen | Exitosas: {$resultados['exitosos']} | Fallidas: {$resultados['fallidos']}\n";
            file_put_contents(__DIR__ . '/log_notificaciones.txt', $logMsg, FILE_APPEND);
            
        } catch (Exception $e) {
            error_log("Error enviando notificación: " . $e->getMessage());
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Pregunta creada correctamente',
        'idExamen' => $idExamen
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}