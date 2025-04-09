<?php
// Step 1: Connect to local XAMPP server
$host = "localhost";
$user = "root";
$password = "";
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle database and table selection
$selected_db = $_POST['database'] ?? null;
$selected_table = $_POST['table'] ?? null;

// Get list of databases
$databases = [];
$db_result = $conn->query("SHOW DATABASES");
while ($row = $db_result->fetch_assoc()) {
    $databases[] = $row['Database'];
}

$tables = [];
$columns = [];

if ($selected_db) {
    $conn->select_db($selected_db);

    // Get tables in selected database
    $table_result = $conn->query("SHOW TABLES");
    while ($row = $table_result->fetch_array()) {
        $tables[] = $row[0];
    }

    // Get columns in selected table
    if ($selected_table) {
        $col_result = $conn->query("SHOW FULL COLUMNS FROM `$selected_table`");

        $output = "Table: $selected_table\n\n";
        while ($col = $col_result->fetch_assoc()) {
            $line = "Column: {$col['Field']} | Type: {$col['Type']}";

            // Check for ENUM types (dropdowns)
            if (preg_match("/^enum\((.*)\)$/i", $col['Type'], $matches)) {
                $enums = str_getcsv($matches[1], ',', "'");
                $line .= " | Dropdown values: [" . implode(', ', $enums) . "]";
            }

            $output .= $line . "\n";
        }

				// Save to text file named after the table
				$filename = "{$selected_table}_structure.txt";
				file_put_contents($filename, $output);

    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Table Column Extractor</title>
    <style>
        body {
            background-color: #030303;
            color: #ffffff;
            font-family: Arial, sans-serif;
        }
        select, button {
            background-color: #080808;
            color: #ffffff;
            border: 1px solid #f94b1f;
            padding: 5px;
            margin: 5px 0;
        }
        label {
            color: #f94b1f;
        }
        .container {
            background-color: #0a0a0a;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            margin: 40px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Select Database and Table</h2>
        <form method="POST">
            <label for="database">Database:</label><br>
            <select name="database" id="database" onchange="this.form.submit()">
                <option value="">--Select Database--</option>
                <?php foreach ($databases as $db): ?>
                    <option value="<?= $db ?>" <?= $db == $selected_db ? 'selected' : '' ?>><?= $db ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <?php if ($selected_db): ?>
                <label for="table">Table:</label><br>
                <select name="table" id="table">
                    <option value="">--Select Table--</option>
                    <?php foreach ($tables as $tbl): ?>
                        <option value="<?= $tbl ?>" <?= $tbl == $selected_table ? 'selected' : '' ?>><?= $tbl ?></option>
                    <?php endforeach; ?>
                </select><br><br>
                <button type="submit">Extract Columns</button>
            <?php endif; ?>
        </form>

        <?php if ($selected_table): ?>
    <p style="color: #f94b1f;">Structure saved to: <code><?= htmlspecialchars($filename) ?></code></p>
    <form method="POST">
        <button type="submit" style="margin-top:10px;">New Extract</button>
    </form>
<?php endif; ?>

    </div>
</body>
</html>
