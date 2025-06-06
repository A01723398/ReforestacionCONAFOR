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
  <h2>Informacion Plantas</h2>

  <table>
    <tr>
      <th>Especie</th>
      <th>Nombre Comun</th>
      <th>Individuos/Ha</th>
      <th>Porcentaje/Ha</th>
      <th>Individuos en 75Ha</th>
      <th>Altura (cm)</th>
      <th>Capacidad en Camioneta</th>
    </tr>

    <?php
    $sql = "SELECT * FROM plantas";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['especie']}</td>
                    <td>{$row['nombre_comun']}</td>
                    <td>{$row['individuos_por_ha']}</td>
                    <td>{$row['porcentaje_por_ha']}%</td>
                    <td>{$row['individuos_en_75_ha']}</td>
                    <td>{$row['altura_cm']}</td>
                    <td>{$row['capacidad_en_camioneta']}</td>
                  </tr>";
        }
    }
    ?>
  </table>

<a href="index.php">⬅ Volver a Pagina de Inicio</a>
</body>
</html>

<?php $conn->close(); ?>
