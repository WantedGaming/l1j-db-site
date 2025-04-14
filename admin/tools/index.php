<?php
/**
 * Database Management Toolkit
 * 
 * Combines:
 * - Table Structure Extractor
 * - Table Description Documenter
 * - SQL Query Runner
 * - Data Preview with Export
 */

// Set page title
$pageTitle = 'Database Toolkit';

// Include required files
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/l1j-db-site';
require_once $root_path . '/includes/config.php';
require_once $root_path . '/includes/database.php';
require_once $root_path . '/includes/functions.php';
require_once $root_path . '/includes/auth.php';
require_once $root_path . '/includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Initialize variables
$selected_db = $_POST['database'] ?? null;
$selected_table = $_POST['table'] ?? null;
$action = $_POST['action'] ?? null;
$sql_query = $_POST['sql_query'] ?? null;
$query_result = null;
$error_message = null;
$output = null;
$filename = null;
$preview_output = null;
$preview_filename = null;
$preview_exported = false;

// Get list of databases
$databases = [];
$db_result = $db->query("SHOW DATABASES");
while ($row = $db_result->fetch(PDO::FETCH_ASSOC)) {
    $databases[] = $row['Database'];
}

$tables = [];
$columns = [];
$table_info = null;
$preview_data = [];

if ($selected_db) {
    // Select the database
    $db->query("USE `$selected_db`");

    // Get tables in selected database
    $table_result = $db->query("SHOW TABLES");
    while ($row = $table_result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    // Get table info if selected
    if ($selected_table) {
        // Get columns
        $col_result = $db->query("SHOW FULL COLUMNS FROM `$selected_table`");
        while ($col = $col_result->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $col;
        }

        // Get table status
        $table_info_result = $db->query("SHOW TABLE STATUS LIKE '$selected_table'");
        $table_info = $table_info_result->fetch(PDO::FETCH_ASSOC);

        // Get preview data
        $preview_stmt = $db->query("SELECT * FROM `$selected_table` LIMIT 5");
        $preview_data = $preview_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle SQL query execution
if ($action === 'execute_query' && $sql_query) {
    try {
        $query_result = $db->query($sql_query);
        if ($query_result) {
            // For SELECT queries, fetch results
            if (stripos(trim($sql_query), 'select') === 0) {
                $query_result = $query_result->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        $error_message = $e->getMessage();
    }
}

// Handle structure export
if ($action === 'export_structure' && $selected_table) {
    $output = generateTableStructure($db, $selected_db, $selected_table);
    $filename = "{$selected_table}_structure.txt";
    $filepath = $root_path . "/assets/extracts/{$filename}";
    
    // Create directory if it doesn't exist
    if (!is_dir($root_path . "/assets/extracts")) {
        mkdir($root_path . "/assets/extracts", 0755, true);
    }
    
    file_put_contents($filepath, $output);
}

// Handle preview data export
if ($action === 'export_preview' && $selected_table) {
    $preview_output = generatePreviewExport($db, $selected_table);
    $preview_filename = "{$selected_table}_preview_data.txt";
    $preview_filepath = $root_path . "/assets/extracts/{$preview_filename}";
    
    // Create directory if it doesn't exist
    if (!is_dir($root_path . "/assets/extracts")) {
        mkdir($root_path . "/assets/extracts", 0755, true);
    }
    
    file_put_contents($preview_filepath, $preview_output);
    $preview_exported = true;
}

/**
 * Generate table structure documentation
 */
function generateTableStructure($db, $database, $table) {
    $output = "Table: $table\n";
    $output .= "Database: $database\n\n";
    
    // Get columns
    $col_result = $db->query("SHOW FULL COLUMNS FROM `$table`");
    $output .= "Columns:\n";
    $output .= str_repeat("-", 80) . "\n";
    
    while ($col = $col_result->fetch(PDO::FETCH_ASSOC)) {
        $line = "• {$col['Field']} ({$col['Type']})";
        
        if ($col['Null'] === 'YES') {
            $line .= " [Nullable]";
        }
        
        if ($col['Default'] !== null) {
            $line .= " [Default: {$col['Default']}]";
        }
        
        if (!empty($col['Comment'])) {
            $line .= " [Comment: {$col['Comment']}]";
        }
        
        // Check for ENUM types
        if (preg_match("/^enum\((.*)\)$/i", $col['Type'], $matches)) {
            $enums = str_getcsv($matches[1], ',', "'");
            $line .= " [Values: " . implode(', ', $enums) . "]";
        }
        
        $output .= $line . "\n";
    }
    
    // Get indexes
    $output .= "\nIndexes:\n";
    $output .= str_repeat("-", 80) . "\n";
    $index_result = $db->query("SHOW INDEX FROM `$table`");
    $indexes = [];
    
    while ($index = $index_result->fetch(PDO::FETCH_ASSOC)) {
        $indexes[$index['Key_name']]['columns'][] = $index['Column_name'];
        $indexes[$index['Key_name']]['unique'] = !$index['Non_unique'];
    }
    
    foreach ($indexes as $name => $info) {
        $type = $name === 'PRIMARY' ? 'PRIMARY KEY' : ($info['unique'] ? 'UNIQUE' : 'INDEX');
        $output .= "• $name ($type): " . implode(', ', $info['columns']) . "\n";
    }
    
    return $output;
}

/**
 * Generate preview data export content
 */
function generatePreviewExport($db, $table) {
    $output = "Table: $table\n";
    $output .= "Preview Data (First 5 Rows)\n";
    $output .= str_repeat("=", 50) . "\n\n";
    
    // Get column names
    $col_result = $db->query("SHOW COLUMNS FROM `$table`");
    $columns = [];
    while ($col = $col_result->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $col['Field'];
    }
    
    // Add column headers
    $output .= "Columns: " . implode(', ', $columns) . "\n\n";
    
    // Get preview data
    $preview_stmt = $db->query("SELECT * FROM `$table` LIMIT 5");
    $preview_data = $preview_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($preview_data)) {
        // Determine column widths
        $column_widths = [];
        foreach ($columns as $col) {
            $column_widths[$col] = strlen($col);
            foreach ($preview_data as $row) {
                $val_length = strlen((string)$row[$col]);
                if ($val_length > $column_widths[$col]) {
                    $column_widths[$col] = $val_length;
                }
            }
            // Add some padding
            $column_widths[$col] += 2;
        }
        
        // Create header row
        foreach ($columns as $col) {
            $output .= str_pad($col, $column_widths[$col]);
        }
        $output .= "\n" . str_repeat("-", array_sum($column_widths)) . "\n";
        
        // Add data rows
        foreach ($preview_data as $row) {
            foreach ($columns as $col) {
                $output .= str_pad($row[$col] ?? 'NULL', $column_widths[$col]);
            }
            $output .= "\n";
        }
    } else {
        $output .= "No data found in table.\n";
    }
    
    return $output;
}

/**
 * Helper function to format bytes
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Database Toolkit</h1>
                <p class="admin-hero-subtitle">Manage, document and analyze your database</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-section">
        <div class="section-header">
            <h2>Database Navigation</h2>
        </div>
        
        <div class="data-card">
            <div class="data-card-body">
                <form method="POST" class="form">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="database" class="form-label">Database:</label>
                            <select name="database" id="database" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Select Database --</option>
                                <?php foreach ($databases as $db_name): ?>
                                    <option value="<?= htmlspecialchars($db_name) ?>" <?= $db_name == $selected_db ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($db_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <?php if ($selected_db): ?>
                        <div class="col-md-6">
                            <label for="table" class="form-label">Table:</label>
                            <select name="table" id="table" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Select Table --</option>
                                <?php foreach ($tables as $tbl): ?>
                                    <option value="<?= htmlspecialchars($tbl) ?>" <?= $tbl == $selected_table ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tbl) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($selected_table): ?>
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Table: <?= htmlspecialchars($selected_table) ?></h2>
                <div class="btn-group">
                    <form method="POST">
                        <input type="hidden" name="database" value="<?= htmlspecialchars($selected_db) ?>">
                        <input type="hidden" name="table" value="<?= htmlspecialchars($selected_table) ?>">
                        <input type="hidden" name="action" value="export_structure">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export Structure
                        </button>
                    </form>
                </div>
            </div>

            <!-- SQL Query Tool moved up -->
            <div class="row">
                <div class="col-12">
                    <div class="data-card">
                        <div class="data-card-header">
                            <h3>SQL Query Tool</h3>
                        </div>
                        <div class="data-card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="execute_query">
                                <input type="hidden" name="database" value="<?= htmlspecialchars($selected_db) ?>">
                                
                                <div class="form-group">
                                    <label for="sql_query">SQL Query:</label>
                                    <textarea name="sql_query" id="sql_query" class="form-control" rows="4" 
                                              placeholder="SELECT * FROM <?= $selected_table ? $selected_table : 'table_name' ?>"><?= htmlspecialchars($sql_query) ?></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Execute
                                    </button>
                                </div>
                            </form>
                            
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger mt-3">
                                    <strong>Error:</strong> <?= htmlspecialchars($error_message) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($query_result !== null): ?>
                                <div class="mt-4">
                                    <h4>Query Results</h4>
                                    
                                    <?php if (is_array($query_result) && !empty($query_result)): ?>
                                        <div class="table-responsive">
                                            <table class="admin-table">
                                                <thead>
                                                    <tr>
                                                        <?php foreach (array_keys($query_result[0]) as $column): ?>
                                                            <th><?= htmlspecialchars($column) ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($query_result as $row): ?>
                                                        <tr>
                                                            <?php foreach ($row as $value): ?>
                                                                <td><?= htmlspecialchars($value) ?></td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php elseif (is_array($query_result)): ?>
                                        <div class="alert alert-info">Query executed successfully but returned no results.</div>
                                    <?php else: ?>
                                        <div class="alert alert-success">
                                            Query executed successfully. <?= $query_result->rowCount() ?> rows affected.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Information (full width) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="data-card">
                        <div class="data-card-header">
                            <h3>Table Information</h3>
                        </div>
                        <div class="data-card-body">
                            <?php if ($table_info): ?>
                                <dl class="dl-horizontal row" style="padding: 15px;">
                                    <div class="col-md-3">
                                        <dt>Engine</dt>
                                        <dd><?= htmlspecialchars($table_info['Engine']) ?></dd>
                                    </div>
                                    <div class="col-md-3">
                                        <dt>Rows</dt>
                                        <dd><?= number_format($table_info['Rows']) ?></dd>
                                    </div>
                                    <div class="col-md-2">
                                        <dt>Data Size</dt>
                                        <dd><?= formatBytes($table_info['Data_length']) ?></dd>
                                    </div>
                                    <div class="col-md-2">
                                        <dt>Created</dt>
                                        <dd><?= htmlspecialchars($table_info['Create_time']) ?></dd>
                                    </div>
                                    <div class="col-md-2">
                                        <dt>Collation</dt>
                                        <dd><?= htmlspecialchars($table_info['Collation']) ?></dd>
                                    </div>
                                </dl>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Structure (full width) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="data-card">
                        <div class="data-card-header">
                            <h3>Table Structure</h3>
                        </div>
                        <div class="data-card-body">
                            <div class="table-responsive custom-scrollbar">
                                <table class="admin-table">
                                    <thead style="position: sticky; top: 0; background-color: var(--secondary); z-index: 1;">
                                        <tr>
                                            <th>Column</th>
                                            <th>Type</th>
                                            <th>Nullable</th>
                                            <th>Default</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($columns as $column): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($column['Field']) ?></td>
                                                <td><?= htmlspecialchars($column['Type']) ?></td>
                                                <td><?= $column['Null'] === 'YES' ? 'Yes' : 'No' ?></td>
                                                <td><?= $column['Default'] ?? '<em>NULL</em>' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Preview (full width) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="data-card">
                        <div class="data-card-header d-flex justify-content-between align-items-center">
                            <h3>Data Preview (First 5 Rows)</h3>
                            <?php if (!empty($preview_data)): ?>
                                <form method="POST">
                                    <input type="hidden" name="database" value="<?= htmlspecialchars($selected_db) ?>">
                                    <input type="hidden" name="table" value="<?= htmlspecialchars($selected_table) ?>">
                                    <input type="hidden" name="action" value="export_preview">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Export Preview
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="data-card-body">
                            <?php if (!empty($preview_data)): ?>
                                <div class="table-responsive custom-scrollbar" style="max-height: 400px; overflow: auto;">
                                    <table class="admin-table" style="min-width: 100%; width: max-content;">
                                        <thead style="position: sticky; top: 0; background-color: var(--secondary); z-index: 1;">
                                            <tr>
                                                <?php foreach (array_keys($preview_data[0]) as $column): ?>
                                                    <th style="padding: 8px; min-width: 120px; max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                                        <?= htmlspecialchars($column) ?>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($preview_data as $row): ?>
                                                <tr>
                                                    <?php foreach ($row as $value): ?>
                                                        <td style="padding: 8px; min-width: 120px; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($value ?? 'NULL') ?>">
                                                            <?php
                                                            if ($value === null) {
                                                                echo '<span style="color: #999; font-style: italic;">NULL</span>';
                                                            } elseif (strlen($value) > 50) {
                                                                echo htmlspecialchars(substr($value, 0, 47) . '...');
                                                            } else {
                                                                echo htmlspecialchars($value);
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">No data found in this table.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($output && $filename): ?>
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Structure Export Results</h2>
            </div>
            
            <div class="data-card">
                <div class="data-card-body">
                    <div class="alert alert-success">
                        <p>Structure saved to: <code><?= htmlspecialchars($filename) ?></code></p>
                    </div>
                    
                    <h3>Table Structure Preview</h3>
                    <div class="code-block">
                        <pre><?= htmlspecialchars($output) ?></pre>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($preview_exported && $preview_filename): ?>
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Preview Export Results</h2>
            </div>
            
            <div class="data-card">
                <div class="data-card-body">
                    <div class="alert alert-success">
                        <p>Preview data saved to: <code><?= htmlspecialchars($preview_filename) ?></code></p>
                    </div>
                    
                    <h3>Preview Data Export</h3>
                    <div class="code-block">
                        <pre><?= htmlspecialchars($preview_output) ?></pre>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Custom Scrollbar Styles */
.custom-scrollbar {
    overflow: auto !important;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: var(--bg-secondary);
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}

/* For Firefox */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: var(--primary) var(--bg-secondary);
}

/* Table Responsive Enhancements */
.table-responsive {
    overflow-x: auto !important;
    overflow-y: auto !important;
    max-width: 100%;
}

.admin-table {
    white-space: nowrap;
    width: max-content;
    min-width: 100%;
}
</style>

<?php
// Include admin footer
require_once $root_path . '/includes/admin-footer.php';
?>