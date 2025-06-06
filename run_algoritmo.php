<?php
if (!isset($_POST['confirm_run'])) {
    die("❌ You must confirm that you want to run the algorithm.");
}

include 'db_conexion.php';

/*
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE pedidos_detalle");
$conn->query("TRUNCATE TABLE pedidos");
$conn->query("TRUNCATE TABLE informacion_dia");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
$conn->close();
unset($conn);
sleep(1); // Give MySQL a second to fully release locks
*/

// Collect POST parameters
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST['fecha_inicio'];
    $tiempo_jornada = $_POST['tiempo_jornada'];
    $denvio = $_POST['denvio'];
    $dreposo = $_POST['dreposo'];
    $dplantable = $_POST['dplantable'];
    $velocidad_promedio = $_POST['velocidad_promedio'];
    $tiempo_plantacion = str_replace(',', '.', $_POST['tiempo_plantacion']);
    $tiempo_minimo_permitido = $_POST['tiempo_minimo_permitido'];
    $plant_survival_rate = $_POST['plant_survival_rate'];

    // Set full paths
    $python = "C:\\Users\\mateo\\AppData\\Local\\Programs\\Python\\Python311\\python.exe";
    $script = "C:\\MAMP\\htdocs\\reforestacion\\algoritmo.py";

    // Construct shell-safe command
    $command = "\"$python\" \"$script\" \"$fecha_inicio\" $tiempo_jornada $denvio $dreposo $dplantable $velocidad_promedio $tiempo_plantacion $tiempo_minimo_permitido $plant_survival_rate";

    // Execute and capture all output
    exec($command . " 2>&1", $output_lines, $exit_code);

    echo "<h2>Python script executed</h2>";
    echo "<pre>";
    echo "Command: $command\n\n";

    foreach ($output_lines as $line) {
        echo htmlspecialchars($line) . "\n";
    }

    echo "\nExit Code: $exit_code";
    echo "</pre>";
}
?>
