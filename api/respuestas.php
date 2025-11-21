<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require '../bd/conexion_bd.php';
    $obj = new BD_PDO();

    if (isset($_GET['idExamen'])) {
        $idExamen = $_GET['idExamen'];
        
        // Obtener información del examen
        $consulta_examen = "SELECT idExamen, tituloExamen, descripcion FROM examenes WHERE idExamen = ?";
        $examen = $obj->ejecutarInstruccion($consulta_examen, [$idExamen]);
        
        if (empty($examen)) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Examen no encontrado'
            ]);
            exit;
        }
        
        // Obtener preguntas del examen
        $consulta_preguntas = "SELECT idPregunta, pregunta, valor FROM preguntas WHERE idExamen = ?";
        $preguntas = $obj->ejecutarInstruccion($consulta_preguntas, [$idExamen]);
        
        // Obtener respuestas para cada pregunta
        foreach ($preguntas as &$pregunta) {
            $consulta_respuestas = "SELECT idRespuesta, respuesta, correcta FROM respuestas WHERE idPregunta = ?";
            $respuestas = $obj->ejecutarInstruccion($consulta_respuestas, [$pregunta['idPregunta']]);
            $pregunta['respuestas'] = $respuestas;
        }
        
        $resultado = [
            'examen' => $examen[0],
            'preguntas' => $preguntas
        ];
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $resultado
        ]);
        
    } else {
        // Si no se especifica idExamen, devolver todos los exámenes
        $consulta_examenes = "SELECT idExamen, tituloExamen, descripcion FROM examenes";
        $examenes = $obj->ejecutarInstruccion($consulta_examenes);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $examenes
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener respuestas',
        'message' => $e->getMessage()
    ]);
}
?>