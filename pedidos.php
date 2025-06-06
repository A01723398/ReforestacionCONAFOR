<?php
include 'db_conexion.php';

$filter = "";
if (isset($_GET['id_grupo_pedido']) && is_numeric($_GET['id_grupo_pedido'])) {
    $id_grupo_pedido = intval($_GET['id_grupo_pedido']);
    $filter = "WHERE pedidos.id_grupo_pedido = $id_grupo_pedido";
}

$sql = "SELECT pedidos.id_pedido, pedidos.fecha_pedido, pedidos.id_grupo_pedido, pedidos.costo_total, pedidos.plantas_total, proveedores.nombre_proveedor
        FROM pedidos
        JOIN proveedores ON pedidos.id_proveedor = proveedores.id_proveedor
        $filter";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Pedidos</title>
  <style>
    table { border-collapse: collapse; width: 90%; margin: 20px auto; }
    th, td { border: 1px solid #ccc; padding: 8px; }
    th { background-color: #f0f0f0; }
    body { font-family: Arial, sans-serif; }
  </style>
</head>
<body>

  <h1>Lista de Pedidos<?php echo isset($id_grupo_pedido) ? " - Grupo $id_grupo_pedido" : ""; ?></h1>

  <table>
    <tr>
      <th>ID Pedido</th>
      <th>Fecha</th>
      <th>Grupo</th>
      <th>Proveedor</th>
      <th>Costo Total</th>
      <th>Plantas Totales</th>
    </tr>

    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td><a href='pedidos_detalle.php?id_pedido={$row['id_pedido']}'>{$row['id_pedido']}</a></td>
                <td>{$row['fecha_pedido']}</td>
                <td>{$row['id_grupo_pedido']}</td>
                <td>{$row['nombre_proveedor']}</td>
                <td>{$row['costo_total']}</td>
                <td>{$row['plantas_total']}</td>
              </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No se encontraron pedidos.</td></tr>";
    }
    ?>
  </table>

  <a href="informacion_diaria.php">⬅ Volver a Información Diaria</a>
</body>
</html>

<?php $conn->close(); ?>
