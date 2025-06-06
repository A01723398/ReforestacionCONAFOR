<?php
include 'db_conexion.php';

$id_poligono = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_poligono == 0) {
    die("Polígono no válido.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Polígono <?php echo $id_poligono; ?></title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        table { width: 80%; margin: 20px auto; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; }
        th { background-color: #f2f2f2; }
        a { text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <?php
    $sql = "SELECT superficie_ha
            FROM poligonos_caracteristicas
            WHERE id_poligono = {$id_poligono}";

    $result = $conn->query($sql);
    ?>

    <h2>Detalle del Polígono <?php echo $id_poligono . " (" .$result->fetch_column() . " Hectáreas)" ?></h2>

    <label for="poligono">Lista de Polígonos:</label>
    <select id="poligono" onchange="location = this.value;">
        <option value="#">Seleccionar</option>
        <?php
        // List of polígonos
        $poligonos = [1, 3, 4, 16, 17, 18, 19, 20, 23, 24, 25, 26, 0];
        foreach ($poligonos as $id) {
            if ($id != 0)
              echo "<option value='poligono_detalle.php?id={$id}'>Polígono {$id}</option>";
            else {
              echo "<option value='poligonos.php'>Estadisticas Generales</option>";
            }
        }
        ?>
    </select>
    <table>
        <tr>
            <th>Especie</th>
            <th>Necesidad</th>
            <th>Plantado</th>
            <th>Porcentaje (%)</th>
        </tr>

        <?php
        $sql = "SELECT plantas.especie, poligono.necesidad, poligono.cantidad_plantada
                FROM poligono
                JOIN plantas ON poligono.id_especie = plantas.id_especie
                WHERE id_poligono = {$id_poligono}";

        $result = $conn->query($sql);
        $total_necesidad = 0;
        $total_plantado = 0;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $percentage = ($row['necesidad'] > 0) ? round(($row['cantidad_plantada'] / $row['necesidad']) * 100, 2) : 0;

                echo "<tr>
                        <td>{$row['especie']}</td>
                        <td>{$row['necesidad']}</td>
                        <td>{$row['cantidad_plantada']}</td>
                        <td>{$percentage}%</td>
                      </tr>";

                $total_necesidad += $row['necesidad'];
                $total_plantado += $row['cantidad_plantada'];
            }
        } else {
            echo "<tr><td colspan='4'>No data available</td></tr>";
        }

        $overall_percentage = ($total_necesidad > 0) ? round(($total_plantado / $total_necesidad) * 100, 2) : 0;
        ?>

        <tr style="font-weight: bold; background-color: #d1e7dd;">
            <td>Total</td>
            <td><?php echo $total_necesidad; ?></td>
            <td><?php echo $total_plantado; ?></td>
            <td><?php echo $overall_percentage; ?>%</td>
        </tr>
    </table>

    <a href="index.php">⬅ Volver a Pagina de Inicio</a>

</body>
</html>

<?php $conn->close(); ?>
