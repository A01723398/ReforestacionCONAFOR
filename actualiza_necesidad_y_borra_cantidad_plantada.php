<?php
include 'db_conexion.php'; 

$sql = "SELECT * FROM poligono";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

        $id_poligono = $row['id_poligono'];
        $especie = $row['especie'];

        $query_superficie = "SELECT superficie_ha
        FROM poligonos_caracteristicas
        WHERE poligonos_caracteristicas.id_poligono = $id_poligono";
        $result_superficie = $conn->query($query_superficie);
        $superficie_ha = $result_superficie->fetch_assoc()['superficie_ha'];

        $query_individuos = "SELECT individuos_por_ha
        FROM plantas
        WHERE plantas.especie = '$especie'";
        $result_individuos = $conn->query($query_individuos);
        $individuos_por_ha = $result_individuos->fetch_assoc()['individuos_por_ha'];

        $necesidad = ceil($superficie_ha * $individuos_por_ha);

        $sql2 = "UPDATE poligono
        SET necesidad = $necesidad
        WHERE poligono.id_poligono = $id_poligono
        AND poligono.especie = '$especie'";
        $conn->query($sql2);

        $sql3 = "UPDATE poligono
        SET cantidad_plantada = 0
        WHERE poligono.id_poligono = $id_poligono";
        $conn->query($sql3);
    }
}

$conn->close();
header("Location: poligonos.php?success=1");
exit();
?>
