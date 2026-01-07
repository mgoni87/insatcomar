<?php
require 'db.php';

/**
 * Script de inicialización para la tabla mdg_conexiones
 * Ejecutar este archivo una sola vez para preparar la base de datos.
 */

$sql = "CREATE TABLE IF NOT EXISTS mdg_conexiones (
    IdCliente INT NOT NULL,
    ClienteNombre VARCHAR(255),
    ClienteTipo VARCHAR(100),
    ClienteVencimiento VARCHAR(50),
    IdConexion INT NOT NULL,
    EstadoConexion VARCHAR(50),
    Latitud DECIMAL(10, 7),
    Longitud DECIMAL(10, 7),
    Nodo VARCHAR(100),
    IdProducto INT NOT NULL,
    Producto VARCHAR(255),
    Cantidad INT,
    FechaInstalacion DATE,
    FechaContrato DATE,
    Sistema VARCHAR(100),
    IdPlan INT NOT NULL,
    Plan VARCHAR(255),
    PlanTipo VARCHAR(100),
    Valor DECIMAL(15, 2),
    ValorBonificado DECIMAL(15, 2),
    FechaAltaPromo DATE,
    Promo VARCHAR(255),
    PromoCuota INT,
    PromoCantidadCuotas INT,
    PromoValor DECIMAL(15, 2),
    PromoPorcentaje DECIMAL(5, 2),
    ProductoOriginal VARCHAR(255),
    PlanOriginal VARCHAR(255),
    FechaInstalacionOriginal DATE,
    Ajuste VARCHAR(100),
    flag_actualizacion TINYINT DEFAULT 0,
    -- Definición de Clave Primaria compuesta
    PRIMARY KEY (IdConexion, IdPlan, IdProducto),
    -- Índice de rendimiento para búsquedas por cliente
    INDEX idx_cliente (IdCliente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conexion->query($sql) === TRUE) {
    echo "Excelente: La tabla 'mdg_conexiones' ha sido creada o ya existía correctamente.";
} else {
    echo "Error al crear la tabla: " . $conexion->error;
}

$conexion->close();
?>