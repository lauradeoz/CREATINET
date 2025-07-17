<?php

class Database {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "creatinet";
    private $conexion;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conexion = new mysqli($this->host, $this->user, $this->password, $this->database);

        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }

    public function getConexion() {
        return $this->conexion;
    }

    public function close() {
        $this->conexion->close();
    }
}