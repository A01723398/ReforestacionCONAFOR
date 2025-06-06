<?php
include 'db_conexion.php';  // Connect to MySQL

$id_dia = $_GET["id_dia"];

$sql = "SELECT * FROM informacion_dia WHERE id_dia = $id_dia";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$fecha = $row["fecha_dia"];
$dia_semana = $row["dia_de_semana"];
$estatus_dia = $row["estatus_dia"];
$grupo_orden_pedir = $row["grupo_orden_pedir"];
$grupo_orden_recibir = $row["grupo_orden_recibir"];
$poligonos_inicial = json_decode($row["poligonos_inicial_dict"], true);
$inventario_inicial = json_decode($row["inventario_inicial_dict"], true);
$plantado_dict = json_decode($row["plantado_dict"], true);
$expirado_dict = json_decode($row["expirado_dict"], true);
$poligonos_final = json_decode($row["poligonos_final_dict"], true);
$inventario_final = json_decode($row["inventario_final_dict"], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Detalle del Día <?php echo $id_dia; ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2 { border-bottom: 1px solid #ccc; padding-bottom: 5px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: left; }
    .section-title { background-color: #f0f0f0; padding: 8px; font-weight: bold; margin-top: 20px; }
    pre { background: #f9f9f9; padding: 10px; border: 1px solid #ddd; }
  </style>
</head>
<body>

<h1>Día <?php echo $id_dia; ?> - <?php echo $fecha; ?></h1>
<p><strong>Día de la semana:</strong> <?php echo $dia_semana; ?> |
<strong>Estatus del día:</strong> <?php echo $estatus_dia; ?></p>

<div class="section-title">I. Initial Conditions</div>
<h3>Inventario Inicial</h3>
<pre><?php echo json_encode($inventario_inicial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>

<h3>Polígonos Iniciales</h3>
<pre><?php echo json_encode($poligonos_inicial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>

<div class="section-title">II. Orders</div>
<p>Ver detalles del pedido:
<a href='pedidos.php?id_grupo_pedido=<?php echo $grupo_orden_pedir; ?>'>Pedido <?php echo $grupo_orden_pedir; ?></a></p>

<div class="section-title">III. Orders Received</div>
<p>Ver pedido recibido:
<a href='pedidos.php?id_grupo_pedido=<?php echo $grupo_orden_recibir; ?>'>Pedido <?php echo $grupo_orden_recibir; ?></a></p>

<div class="section-title">IV. Planted</div>
<h3>Especies Plantadas</h3>
<pre><?php echo json_encode($plantado_dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>

<div class="section-title">V. Expired</div>
<h3>Pedidos Expirados</h3>
<pre><?php echo json_encode($expirado_dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>

<div class="section-title">VI. End of Day Situation</div>
<h3>Inventario Final</h3>
<pre><?php echo json_encode($inventario_final, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>

<h3>Polígonos Finales</h3>
<pre><?php echo json_encode($poligonos_final, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>

<a href="informacion_diaria.php">⬅ Volver a Información Diaria</a>

</body>
</html>

<?php $conn->close(); ?>
