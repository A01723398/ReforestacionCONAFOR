<?php
include 'db_conexion.php';

$id_proveedor = isset($_GET['id_proveedor']) ? intval($_GET['id_proveedor']) : 0;
?>

<!DOCTYPE html>
<html>
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

<script>
function calcularTotal(id) {
    const precio = parseFloat(document.getElementById('precio_' + id).innerText);
    const cantidad = parseInt(document.getElementById('cantidad_' + id).value) || 0;
    const total = (precio * cantidad).toFixed(2);
    document.getElementById('total_' + id).innerText = total;

    calcularSubtotal();
    calcularCantidad();
    calcularTotalFinal();
}

function calcularCantidad() {
    let cantidadTotal = 0;

    const totalElements = document.querySelectorAll("[id^='cantidad_']");

    totalElements.forEach(el => {
        cantidadTotal += parseInt(el.value) || 0;
    });

    document.getElementById('final_cantidad').innerText = cantidadTotal;
}

function calcularSubtotal() {
  let subtotal = 0;

  const totalElements = document.querySelectorAll("[id^='total_']")

  totalElements.forEach(el => {
    subtotal += parseFloat(el.innerText) || 0;
  })

  document.getElementById("subtotal").innerText = subtotal.toFixed(2);

  agregarEnvio()
}

function agregarEnvio() {
  if (document.getElementById("subtotal").innerText > 0.00){
      document.getElementById("envio").innerText = 4500.00;
  }
  else {
      document.getElementById("envio").innerText = 0.00;
  }
}

function calcularTotalFinal() {
    let total_final = 0;

    const subtotal = parseFloat(document.getElementById("subtotal").innerText);
    const envio = parseFloat(document.getElementById("envio").innerText);

    total_final = subtotal + envio;

    document.getElementById("final_total").innerText = total_final.toFixed(2);
}

function guardarFechaEnInput() {
    const hoy = new Date();
    const fechaISO = hoy.toISOString().split('T')[0]; // yyyy-mm-dd
    document.getElementById('fecha_pedido_hidden').value = fechaISO;
}

document.addEventListener("DOMContentLoaded", function() {
    const inputsCantidad = document.querySelectorAll("[id^='cantidad_']");

    inputsCantidad.forEach(input => {
        const id = input.id.split('_')[1]; // extrae el número del ID
        calcularTotal(id); // llama a calcularTotal por cada planta
    });
    guardarFechaEnInput();
});

</script>

<body>

<h2 style="text-align:center;">Generar Pedido</h2>

<form method="GET" style="text-align:center;">
    <label for="proveedor">Selecciona un proveedor:</label>
    <select name="id_proveedor" id="proveedor" onchange="this.form.submit()" required>
        <option value="">-- Elegir --</option>
        <?php
        $proveedores = $conn->query("SELECT id_proveedor, nombre_proveedor FROM proveedores");
        while ($row = $proveedores->fetch_assoc()) {
            $selected = ($row['id_proveedor'] == $id_proveedor) ? 'selected' : '';
            echo "<option value='{$row['id_proveedor']}' $selected>{$row['nombre_proveedor']}</option>";
        }
        ?>
    </select>
</form>

<?php if ($id_proveedor): ?>

<form method="POST" action="procesar_pedido.php">
    <input type="hidden" name="id_proveedor" value="<?php echo $id_proveedor; ?>">
    <input type="hidden" name="fecha_pedido" id="fecha_pedido_hidden">
    <input type="number" name="cantidad[<?php echo $id_especie; ?>]" value="0">

    <br>
    <table>
      <tr>
          <th>Especie</th>
          <th>Precio Unitario (MXN)</th>
          <th>Cantidad</th>
          <th>Precio Total (MXN)</th>
      </tr>
      <?php
      $sql = "SELECT plantas.id_especie, plantas.especie, proveedores_detalle.precio_planta
              FROM proveedores_detalle
              JOIN plantas ON proveedores_detalle.id_especie = plantas.id_especie
              WHERE proveedores_detalle.id_proveedor = $id_proveedor";
      $result = $conn->query($sql);

      while ($row = $result->fetch_assoc()) {
          $id = $row['id_especie'];
          $precio = $row['precio_planta'];
          echo "<tr>
                  <td>{$row['especie']}</td>
                  <td><span id='precio_$id'>{$precio}</span></td>
                  <td>
                      <input type='number' name='cantidad[$id]' min='0' value='0'
                             oninput='calcularTotal($id)' id='cantidad_$id'>
                  </td>
                  <td><span id='total_$id'>0.00</span></td>
                </tr>";
      }
      ?>
    </table>

    <table>
      <tr>
        <th scope="row">Cantidad de Plantas Total</th>
        <td> <span id='final_cantidad'>0</span></td>
      </tr>
      <tr>
        <th scope="row">Subtotal</th>
        <td> <span id='subtotal'>0.00</span></td>
      </tr>
      <tr>
        <th scope="row">Envio</th>
        <td> <span id='envio'>0.00</span></td>
      </tr>
      <tr>
        <th scope="row">Total</th>
        <td> <span id='final_total'>0.00</span></td>
      </tr>
    </table>

    <button type="submit">Mandar Pedido</button>
  </form>

<?php endif; ?>

<br>
<a href="index.php">⬅ Volver a Pagina de Inicio</a>
</body>
</html>

<?php $conn->close(); ?>
