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
  <h2>Informacion Hectáreas</h2>

  <table>
    <tr>
      <th>Poligono</th>
      <th>Superficie (Ha)</th>
    </tr>

    <?php
    $sql = "SELECT * FROM poligonos_caracteristicas";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id_poligono']}</td>
                    <td>{$row['superficie_ha']}</td>
                  </tr>";
        }
    }
    ?>
  </table>

<a href="index.php">⬅ Volver a Pagina de Inicio</a>
</body>
</html>

<?php $conn->close(); ?>
