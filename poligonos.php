<?php
include 'db_conexion.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Polígonos</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; }
        th { background-color: #f2f2f2; }
        select { margin: 10px; padding: 5px; font-size: 16px; }
        a { text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <?php
    if (isset($_GET['success'])) {
      echo "<script>alert('Actualizacion de Datos Exitoso!');</script>";
    }
    ?>

    <h2>Polígonos - Estadisticas Generales</h2>

    <label for="poligono">Lista de Polígonos:</label>
    <select id="poligono" onchange="location = this.value;">
        <option value="#">Seleccionar</option>
        <?php
        // List of polígonos
        $poligonos = [1, 3, 4, 16, 17, 18, 19, 20, 23, 24, 25, 26, 0];
        foreach ($poligonos as $id) {
          if ($id != 0)
            echo "<option value='poligono_detalle.php?id={$id}'>Polígono {$id}</option>";
          else
            echo "<option value='poligonos.php'>Estadisticas Generales</option>";
        }
        ?>

    </select>

    <table>
        <tr>
            <th>ID Polígono</th>
            <th>Total Necesidad</th>
            <th>Total Plantado</th>
            <th>Porcentaje Plantado (%)</th>
        </tr>

        <?php
        $sql = "SELECT id_poligono, SUM(necesidad) AS total_necesidad, SUM(cantidad_plantada) AS total_plantado
                FROM poligono
                GROUP BY id_poligono
                ORDER BY id_poligono";

        $result = $conn->query($sql);
        $overall_necesidad = 0;
        $overall_plantado = 0;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = ($row['total_necesidad'] > 0) ? round(($row['total_plantado'] / $row['total_necesidad']) * 100, 2) : 0;

                echo "<tr>
                        <td>{$row['id_poligono']}</td>
                        <td>{$row['total_necesidad']}</td>
                        <td>{$row['total_plantado']}</td>
                        <td>{$percentage}%</td>
                      </tr>";

                $overall_necesidad += $row['total_necesidad'];
                $overall_plantado += $row['total_plantado'];
            }
        } else {
            echo "<tr><td colspan='4'>No data available</td></tr>";
        }

        $overall_percentage = ($overall_necesidad > 0) ? round(($overall_plantado / $overall_necesidad) * 100, 2) : 0;
        ?>

        <tr style="font-weight: bold; background-color: #d1e7dd;">
            <td>Total</td>
            <td><?php echo $overall_necesidad; ?></td>
            <td><?php echo $overall_plantado; ?></td>
            <td><?php echo $overall_percentage; ?>%</td>
        </tr>
    </table>
    <a href="index.php">⬅ Volver a Pagina de Inicio</a><br><br>

    <form action="actualiza_necesidad_y_borra_cantidad_plantada.php" method="post" onsubmit="return confirmAction();">
        <button type="submit">Borra Plantado y Actualiza Necesidades</button>
    </form>

    <script>
    function confirmAction() {
        return confirm("Estas seguro que deseas borrar todo lo plantado y actualizar las necesidades?");
    }
    </script>

</body>
</html>

<?php $conn->close(); ?>
