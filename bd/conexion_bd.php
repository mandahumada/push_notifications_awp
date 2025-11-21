<?php

require_once 'config.php';

class BD_PDO {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=".DB_SERVER.";dbname=".DB_NAME.";charset=utf8",
                DB_USER,
                DB_PASS
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Failed to get DB handle: " . $e->getMessage();
            exit;
        }
    }

    public function ejecutarInstruccion($consulta_sql, $params = []) {
        $query = $this->pdo->prepare($consulta_sql);
        $query->execute($params);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ultimoID() {
        return $this->pdo->lastInsertId();
    }

    public function registrarLog($accion, $detalle) {
        $fecha = date('Y-m-d H:i:s');
        $logMessage = "$fecha | $accion | $detalle\n";
        file_put_contents('log.txt', $logMessage, FILE_APPEND);
    }
}
