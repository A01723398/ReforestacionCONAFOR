<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Algoritmo de Planeación</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        label { display: block; margin-top: 10px; }
        input, select { width: 300px; padding: 5px; }
        button { margin-top: 20px; padding: 10px 15px; }
        .form-section { margin-bottom: 30px; }
    </style>
</head>
<body>

<h1>Algoritmo de Planeación</h1>

<form id="algoritmoForm">
    <label for="fecha_inicio_display">Start Date:</label>
    <input type="date" id="fecha_inicio_display" name="fecha_inicio_display" required>
    <input type="hidden" id="fecha_inicio" name="fecha_inicio">

    <label>Tiempo jornada (hrs): <input type="number" name="tiempo_jornada" value="6" required></label>
    <label>Días envío: <input type="number" name="denvio" value="1" required></label>
    <label>Días reposo: <input type="number" name="dreposo" value="3" required></label>
    <label>Días plantables: <input type="number" name="dplantable" value="5" required></label>
    <label>Velocidad promedio: <input type="number" name="velocidad_promedio" value="20" required></label>
    <label>Tiempo plantación: <input type="number" name="tiempo_plantacion" step="0.1" value="1.0" required></label>
    <label>Tiempo mínimo permitido: <input type="number" name="tiempo_minimo_permitido" value="15" required></label>
    <label>Survival rate: <input type="number" name="plant_survival_rate" step="0.01" min="0" max="1" value="0.96" required></label>

    <button type="submit">Run Algorithm</button>
</form>

<pre id="response"></pre>
<br>
<a href="index.php">⬅ Volver a Página de Inicio</a>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const formatted = `${yyyy}-${mm}-${dd}`;
    document.getElementById("fecha_inicio_display").value = formatted;
});

document.getElementById("algoritmoForm").addEventListener("submit", async function (event) {
    event.preventDefault();

    const dateInput = document.getElementById("fecha_inicio_display").value;
    const date = new Date(dateInput);
    if (!isNaN(date)) {
        const formattedDate = `${String(date.getDate()).padStart(2, '0')}-${String(date.getMonth() + 1).padStart(2, '0')}-${date.getFullYear()}`;
        document.getElementById("fecha_inicio").value = formattedDate;
    }

    const truncateResponse = await fetch("truncate_tables.php", { method: "POST" });
    const truncateText = await truncateResponse.text();
    console.log("Truncate result:", truncateText);

    const form = event.target;
    const data = {
        fecha_inicio: form.fecha_inicio.value,
        tiempo_jornada: parseFloat(form.tiempo_jornada.value),
        denvio: parseFloat(form.denvio.value),
        dreposo: parseFloat(form.dreposo.value),
        dplantable: parseFloat(form.dplantable.value),
        velocidad_promedio: parseFloat(form.velocidad_promedio.value),
        tiempo_plantacion: parseFloat(form.tiempo_plantacion.value),
        tiempo_minimo_permitido: parseFloat(form.tiempo_minimo_permitido.value),
        plant_survival_rate: parseFloat(form.plant_survival_rate.value)
    };

    const response = await fetch("http://127.0.0.1:8000/run-algorithm", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    });

    const result = await response.json();
    console.log("FastAPI Output:", result);
    document.getElementById("response").textContent = JSON.stringify(result, null, 2);
});
</script>

</body>
</html>
