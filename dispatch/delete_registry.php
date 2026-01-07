<?php

// Definición de constantes de conexión a la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'insatcomar_usr');
define('DB_PASS', 'H~B(%rIjcGw-');
define('DB_NAME', 'insatcomar_dbctes');

$message = "";
$error = "";

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['num_records']) && is_numeric($_POST['num_records'])) {
        $num_records_to_delete = intval($_POST['num_records']);

        if ($num_records_to_delete <= 0) {
            $error = "Por favor, ingrese un número positivo de registros a eliminar.";
        } else {
            // Establish database connection
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            // Check for connection errors
            if ($mysqli->connect_errno) {
                $error = "Error al conectar con la base de datos: " . $mysqli->connect_error;
            } else {
                // Select IDs to delete (assuming higher ID means more recent record)
                $sql_select_ids = "SELECT id FROM consumos_diarios ORDER BY id DESC LIMIT " . $num_records_to_delete;
                $result = $mysqli->query($sql_select_ids);

                $ids_to_delete = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $ids_to_delete[] = $row['id'];
                    }
                } else {
                    $message = "No se encontraron registros para eliminar o la tabla está vacía.";
                }

                // If IDs are found, proceed with deletion
                if (!empty($ids_to_delete)) {
                    // Convert the array of IDs into a string for the IN clause
                    $ids_string = implode(',', $ids_to_delete);

                    // SQL query to delete the records
                    $sql_delete = "DELETE FROM consumos_diarios WHERE id IN ($ids_string)";

                    // Execute the deletion query
                    if ($mysqli->query($sql_delete) === TRUE) {
                        $message = "Se han eliminado exitosamente los últimos " . count($ids_to_delete) . " registros de la tabla `consumos_diarios`.";
                    } else {
                        $error = "Error al eliminar registros: " . $mysqli->error;
                    }
                } else if (empty($message)) { // Only show this if no other message was set
                    $message = "No se encontraron IDs para eliminar. Puede que haya menos de " . $num_records_to_delete . " registros en la tabla.";
                }

                // Close the database connection
                $mysqli->close();
            }
        }
    } else {
        $error = "Por favor, ingrese una cantidad válida de registros a eliminar.";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Registros</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: auto;
        }
        label, input[type="number"], button {
            display: block;
            margin-bottom: 10px;
        }
        input[type="number"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Eliminar Registros de `consumos_diarios`</h2>
        <?php if (!empty($message)): ?>
            <div class="message success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="message error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return confirmDeletion()">
            <label for="num_records">Cantidad de registros a eliminar (los más recientes):</label>
            <input type="number" id="num_records" name="num_records" min="1" required>
            <button type="submit">Eliminar Registros</button>
        </form>
    </div>

    <script>
        function confirmDeletion() {
            var numRecords = document.getElementById('num_records').value;
            if (numRecords === "" || parseInt(numRecords) <= 0) {
                alert("Por favor, ingrese un número válido de registros a eliminar.");
                return false;
            }
            var confirmation = confirm("¿Está seguro de que desea eliminar los últimos " + numRecords + " registros de la tabla `consumos_diarios`? Esta acción es irreversible.");
            return confirmation;
        }
    </script>
</body>
</html>