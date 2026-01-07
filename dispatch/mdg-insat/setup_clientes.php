<?php
require 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS mdg_clientes (
    IdCliente INT PRIMARY KEY,
    CodigoCliente VARCHAR(50),
    Nombre VARCHAR(255),
    Estado VARCHAR(50),
    TipoCliente VARCHAR(100),
    AreaTel_1 VARCHAR(10),
    Tel_1 VARCHAR(20),
    AreaTel_2 VARCHAR(10),
    Tel_2 VARCHAR(20),
    AreaTel_3 VARCHAR(10),
    Tel_3 VARCHAR(20),
    Email_1 VARCHAR(255),
    Email_2 VARCHAR(255),
    Email_3 VARCHAR(255),
    TipoDocumento VARCHAR(50),
    NroDocumento BIGINT,
    FormaPago VARCHAR(100),
    ConvenioPago VARCHAR(100),
    FechaCX DATE,
    FechaDX DATE,
    FechaSuspendido DATE,
    EmpresaVenta VARCHAR(100),
    AgenciaVenta VARCHAR(100),
    Saldo DECIMAL(15,2),
    CantFacturas INT,
    FechaActualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    if ($conexion->query($sql) === TRUE) {
        echo "Tabla 'mdg_clientes' creada con éxito.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>