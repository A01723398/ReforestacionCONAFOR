<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "This endpoint only accepts POST requests.";
    exit;
}

include 'db_conexion.php';

$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE pedidos_detalle");
$conn->query("TRUNCATE TABLE pedidos");
$conn->query("TRUNCATE TABLE informacion_dia");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

$conn->close();

echo "Tables truncated successfully.";

$conn->close();
?>
