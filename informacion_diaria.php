<?php
include 'db_conexion.php'; 
?>

<!DOCTYPE html>
<html lang="es">
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

<h2>Información Diaria</h2>

<table>
    <tr>
        <th>Número de Día</th>
        <th>Fecha</th>
        <th>Especies Plantadas</th>
        <th>Inversión Económica</th>
    </tr>

    <?php
    $sql = "SELECT id_dia, fecha_dia, planting_order_cost, total_planted FROM informacion_dia";
    $result = $conn->query($sql);

    if ($result === false) {
        echo "<tr><td colspan='4'>Error al ejecutar la consulta: " . htmlspecialchars($conn->error) . "</td></tr>";
    } elseif ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td><a href='dia_detalle.php?id_dia=" . htmlspecialchars($row['id_dia']) . "'>" . htmlspecialchars($row['id_dia']) . "</a></td>
                <td>" . htmlspecialchars($row['fecha_dia']) . "</td>
                <td>" . htmlspecialchars($row['total_planted']) . "</td>
                <td>" . htmlspecialchars($row['planting_order_cost']) . "</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No hay registros en la tabla.</td></tr>";
    }

    $conn->close();
    ?>
</table>

<a href="index.php">⬅ Volver a Página de Inicio</a>

</body>
</html>
