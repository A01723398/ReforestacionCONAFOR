<?php
include 'db_conexion.php';

$fecha = $_POST["fecha_pedido"];
$id_proveedor = $_POST["id_proveedor"];
$cantidades = $_POST["cantidad"];

$sql = "INSERT INTO pedidos (id_proveedor, fecha_pedido) VALUES ('$id_proveedor','$fecha')";
$result = $conn->query($sql);
$id_pedido = $conn->insert_id;

foreach ($cantidades as $id_especie => $cantidad) {
  $cantidad = intval($cantidad);
  if ($cantidad > 0) {
    $sql = "INSERT INTO pedidos_detalle (id_pedido, id_especie, cantidad) VALUES ('$id_pedido','$id_especie','$cantidad')";
    $result = $conn->query($sql);
  }
}

$conn->close();
header("Location: pedidos.php");
exit();
?>
