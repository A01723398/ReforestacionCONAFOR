<?php
include 'db_conexion.php';  // This connects to MySQL
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
  <h2>  Reforestacion CONAFOR</h2>

  <a href="poligonos.php">Polígonos</a><br><br>
  <a href="pedidos.php">Pedidos</a><br><br>
  <a href="informacion_diaria.php">Informacion Diaria</a><br><br>
  <a href="algoritmo_planeacion.php">Algoritmo de Planeacion</a><br><br>
  <a href="informacion_adicional.php">Información Adicional</a><br><br>

  <img src="assets/poligonos.png" usemap="#image-map">

  <map name="image-map">
      <area target="" alt="Polígono 4" title="Polígono 4" href="poligono_detalle.php?id=4" coords="259,556,331,703" shape="rect">
      <area target="" alt="Polígono 3" title="Polígono 3" href="poligono_detalle.php?id=3" coords="184,556,255,704" shape="rect">
      <area target="" alt="Polígono 5" title="Polígono 5" href="poligono_detalle.php?id=5" coords="336,556,398,705" shape="rect">
      <area target="" alt="Polígono 1" title="Polígono 1" href="poligono_detalle.php?id=1" coords="141,710,396,709,391,770,235,721,147,714" shape="poly">
      <area target="" alt="Polígono 20" title="Polígono 20" href="poligono_detalle.php?id=20" coords="15,136,26,283" shape="rect">
      <area target="" alt="Polígono 23" title="Polígono 23" href="poligono_detalle.php?id=23" coords="182,136,183,259,232,224,253,224,254,137" shape="poly">
      <area target="" alt="Polígono 24" title="Polígono 24" href="poligono_detalle.php?id=24" coords="261,137,259,223,312,244,331,258,331,137" shape="poly">
      <area target="" alt="Polígono 25" title="Polígono 25" href="poligono_detalle.php?id=25" coords="640,232,712,159,711,78,640,140" shape="poly">
      <area target="" alt="Polígono 16" title="Polígono 16" href="poligono_detalle.php?id=16" coords="487,252,487,351,503,370,560,314,559,228,551,238,494,259" shape="poly">
      <area target="" alt="Polígono 17" title="Polígono 17" href="poligono_detalle.php?id=17" coords="411,174,478,218,463,233,483,254,483,348,449,305,411,291" shape="poly">
      <area target="" alt="Polígono 18" title="Polígono 18" href="poligono_detalle.php?id=18" coords="335,137,356,137,407,171,407,291,355,276,335,261" shape="poly">
      <area target="" alt="Polígono 19" title="Polígono 19" href="poligono_detalle.php?id=19" coords="563,225,625,165,621,160,636,142,635,234,564,309" shape="poly">
  </map>

</body>
</html>

<?php $conn->close(); ?>
