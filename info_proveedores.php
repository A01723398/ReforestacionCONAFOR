<?php
include 'db_conexion.php';  
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
  <h2>Informacion Proveedores</h2>

  Precio en MXN por planta de cada proveedor.

  <table>
    <tr>
      <th>Proveedor</th>
      <th>Especie</th>
      <th>Precio</th>
    </tr>

    <?php
    $sql = "SELECT proveedores.nombre_proveedor, plantas.especie, proveedores_detalle.precio_planta
    FROM proveedores_detalle
    JOIN plantas ON proveedores_detalle.id_especie = plantas.id_especie
    JOIN proveedores ON proveedores_detalle.id_proveedor = proveedores.id_proveedor";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['nombre_proveedor']}</td>
                    <td>{$row['especie']}</td>
                    <td>{$row['precio_planta']}</td>
                  </tr>";
        }
    }
    ?>
  </table>

  *Cada envio tiene un costo de transporte de $4500<br><br>

<a href="index.php">⬅ Volver a Pagina de Inicio</a>
</body>
</html>

<?php $conn->close(); ?>
