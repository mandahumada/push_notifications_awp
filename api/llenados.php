<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(1);

try {
    require '../bd/conexion_bd.php';
    $obj = new BD_PDO();

    // Obtener todos los llenados
    $consulta = "SELECT l.idLlenado, l.Clave, l.IdRespuesta, r.respuesta FROM llenados l 
        LEFT JOIN respuestas r ON l.idRespuesta = r.idRespuesta ORDER BY idLlenado ASC";
    

        // $consulta = "SELECT p.idPregunta, p.idExamen, p.pregunta, p.valor, 
        //                 e.tituloExamen
        //          FROM preguntas p
        //          LEFT JOIN examenes e ON p.idExamen = e.idExamen
        //          ORDER BY p.idExamen, p.idPregunta";


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
        'data' => [],
        'error' => $e->getMessage()
    ]);
}