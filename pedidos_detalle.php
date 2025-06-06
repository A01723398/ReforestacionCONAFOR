<?php
include 'db_conexion.php'; 

$id_pedido = $_GET["id_pedido"];

$sql = "SELECT proveedores.nombre_proveedor, pedidos.fecha_pedido
FROM pedidos
JOIN proveedores ON pedidos.id_proveedor = proveedores.id_proveedor
WHERE pedidos.id_pedido = $id_pedido";

$result = $conn->query($sql);
$row = $result->fetch_assoc();
$fecha = $row["fecha_pedido"];
$nombre_proveedor = $row["nombre_proveedor"];

$sql = "SELECT plantas.especie, pedidos_detalle.cantidad
FROM pedidos_detalle
JOIN plantas ON pedidos_detalle.id_especie = plantas.id_especie
WHERE pedidos_detalle.id_pedido = $id_pedido";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reforestación CONAFOR</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; }
        th { background-color: #f2f2f2; }
    </style>

</head>
<body>
  <h2>Detalle del Pedido <?php echo $id_pedido ?></h2>

  Proveedor: <?php echo $nombre_proveedor?><br>
  Fecha: <?php echo $fecha ?>

  <table>
    <tr>
      <th>Especie</th>
      <th>Cantidad</th>
    </tr>

    <?php

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['especie']}</td>
                    <td>{$row['cantidad']}</td>
                  </tr>";
        }
    }
    ?>
  </table>

<a href="pedidos.php">⬅ Volver a Pedidos</a>
</body>
</html>

<?php $conn->close(); ?>
