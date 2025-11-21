<?php

function obtenerAccessToken() {
    try {
        $serviceAccountPath = __DIR__ . '/../bd/firebase-service-account.json';
        
        if (!file_exists($serviceAccountPath)) {
            throw new Exception("Service Account no encontrado");
        }
        
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        
        if (!$serviceAccount) {
            throw new Exception("Error al leer Service Account JSON");
        }
        
        $now = time();
        $payload = [
            "iss" => $serviceAccount['client_email'],
            "sub" => $serviceAccount['client_email'],
            "aud" => "https://oauth2.googleapis.com/token",
            "iat" => $now,
            "exp" => $now + 3600,
            "scope" => "https://www.googleapis.com/auth/firebase.messaging"
        ];
        
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $payloadJson = json_encode($payload);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));
        
        $signature = '';
        $signResult = openssl_sign(
            $base64UrlHeader . "." . $base64UrlPayload,
            $signature,
            $serviceAccount['private_key'],
            OPENSSL_ALGO_SHA256
        );
        
        if (!$signResult) {
            throw new Exception("Error al firmar JWT: " . openssl_error_string());
        }
        
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("Error cURL: " . $curlError);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Error HTTP $httpCode al obtener token: " . $response);
        }
        
        $responseData = json_decode($response, true);
        
        if (!isset($responseData['access_token'])) {
            throw new Exception("No se recibió access_token: " . $response);
        }
        
        return $responseData['access_token'];
        
    } catch (Exception $e) {
        error_log("Error en obtenerAccessToken: " . $e->getMessage());
        return null;
    }
}

function enviarNotificacionMultiple($tokens, $titulo, $mensaje, $datos = []) {
    try {
        $accessToken = obtenerAccessToken();
        
        if (!$accessToken) {
            return [
                'exitosos' => 0,
                'fallidos' => count($tokens),
                'error' => 'no se pudo obtener access token'
            ];
        }
        
        $serviceAccountPath = __DIR__ . '/../bd/firebase-service-account.json';
        $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        $projectId = $serviceAccount['project_id'];
        
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
        
        $resultados = [
            'exitosos' => 0,
            'fallidos' => 0,
            'detalles' => []
        ];
        
        foreach ($tokens as $token) {
    if (empty($token)) continue;
    
    $datosString = [];
    foreach ($datos as $key => $value) {
        $datosString[$key] = is_array($value) ? json_encode($value) : (string)$value;
    }
    
    $message = [
        'message' => [
            'token' => $token,
            'notification' => [
                'title' => $titulo,
                'body' => $mensaje
            ],
            'data' => $datosString,
                    'webpush' => [
                        'notification' => [
                            'icon' => '/awpp1/favicon.png',
                            'badge' => '/awpp1/favicon.png'
                        ]
                    ]
                ]
            ];
            
            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $resultados['exitosos']++;
            } else {
                $resultados['fallidos']++;
                
                $errorDetail = date('Y-m-d H:i:s') . " | HTTP: $httpCode | Token: " . substr($token, 0, 20) . "... | Response: $result\n";
                file_put_contents(__DIR__ . '/log_errores_fcm.txt', $errorDetail, FILE_APPEND);
                
                $resultados['detalles'][] = [
                    'token' => substr($token, 0, 20) . '...',
                    'http_code' => $httpCode,
                    'response' => $result
                ];
            }
        }
        
        return $resultados;
        
    } catch (Exception $e) {
        error_log("error en enviarNotificacionMultiple: " . $e->getMessage());
        return [
            'exitosos' => 0,
            'fallidos' => count($tokens),
            'error' => $e->getMessage()
        ];
    }
}