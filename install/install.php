<?php
/**
 * Database Installation Script for L1J Database Website
 */

// Define a constant to prevent direct access to included files
define('INSTALL_SCRIPT', true);

// Function to display error message
function displayError($message) {
    echo '<div class="alert alert-danger">' . $message . '</div>';
}

// Function to display success message
function displaySuccess($message) {
    echo '<div class="alert alert-success">' . $message . '</div>';
}

// Function to check database connection
function checkDatabaseConnection($host, $user, $pass, $name) {
    try {
        $dsn = "mysql:host=$host";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        // Try to connect to the server
        $conn = new PDO($dsn, $user, $pass, $options);
        
        // Check if database exists
        $stmt = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$name'");
        
        if ($stmt->rowCount() > 0) {
            return [true, 'Database exists'];
        } else {
            // Try to create the database
            $conn->exec("CREATE DATABASE `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            return [true, 'Database created successfully'];
        }
    } catch (PDOException $e) {
        return [false, $e->getMessage()];
    }
}

// Function to import SQL file
function importSqlFile($host, $user, $pass, $name, $file) {
    try {
        $dsn = "mysql:host=$host;dbname=$name";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $conn = new PDO($dsn, $user, $pass, $options);
        
        // Read the SQL file
        $sql = file_get_contents($file);
        
        // Split the SQL file into individual statements
        $statements = explode(';', $sql);
        
        // Execute each statement
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement) {
                $conn->exec($statement);
            }
        }
        
        return [true, 'SQL file imported successfully'];
    } catch (PDOException $e) {
        return [false, $e->getMessage()];
    }
}

// Function to update config.php
function updateConfigFile($host, $user, $pass, $name) {
    try {
        // Path to config.php
        $configPath = '../includes/config.php';
        
        // Read the current file
        $configContent = file_get_contents($configPath);
        
        // Replace database settings
        $configContent = preg_replace('/define\(\'DB_HOST\', \'.*?\'\);/', "define('DB_HOST', '$host');", $configContent);
        $configContent = preg_replace('/define\(\'DB_USER\', \'.*?\'\);/', "define('DB_USER', '$user');", $configContent);
        $configContent = preg_replace('/define\(\'DB_PASS\', \'.*?\'\);/', "define('DB_PASS', '$pass');", $configContent);
        $configContent = preg_replace('/define\(\'DB_NAME\', \'.*?\'\);/', "define('DB_NAME', '$name');", $configContent);
        
        // Write the updated content
        file_put_contents($configPath, $configContent);
        
        return [true, 'Config file updated successfully'];
    } catch (Exception $e) {
        return [false, $e->getMessage()];
    }
}

// Process installation form
$installed = false;
$error = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbUser = $_POST['db_user'] ?? 'root';
    $dbPass = $_POST['db_pass'] ?? '';
    $dbName = $_POST['db_name'] ?? 'l1j_remastered';
    $adminUser = $_POST['admin_user'] ?? 'admin';
    $adminPass = $_POST['admin_pass'] ?? 'password';
    
    // Check database connection
    list($connected, $dbMessage) = checkDatabaseConnection($dbHost, $dbUser, $dbPass, $dbName);
    
    if (!$connected) {
        $error = true;
        $message = 'Database connection failed: ' . $dbMessage;
    } else {
        // Try to update config file
        list($configUpdated, $configMessage) = updateConfigFile($dbHost, $dbUser, $dbPass, $dbName);
        
        if (!$configUpdated) {
            $error = true;
            $message = 'Config file update failed: ' . $configMessage;
        } else {
            // Import SQL files
            $sqlFiles = [
                '../sql/l1j_remastered_database.sql',
                // Add all sql files in correct order
            ];
            
            foreach ($sqlFiles as $sqlFile) {
                if (!file_exists($sqlFile)) {
                    $error = true;
                    $message = 'SQL file not found: ' . $sqlFile;
                    break;
                }
                
                list($imported, $importMessage) = importSqlFile($dbHost, $dbUser, $dbPass, $dbName, $sqlFile);
                
                if (!$imported) {
                    $error = true;
                    $message = 'SQL import failed: ' . $importMessage;
                    break;
                }
            }
            
            if (!$error) {
                // Create admin user
                try {
                    $dsn = "mysql:host=$dbHost;dbname=$dbName";
                    $conn = new PDO($dsn, $dbUser, $dbPass);
                    
                    // Create admin_users table if it doesn't exist
                    $conn->exec("
                        CREATE TABLE IF NOT EXISTS `admin_users` (
                            `id` INT AUTO_INCREMENT PRIMARY KEY,
                            `username` VARCHAR(50) NOT NULL UNIQUE,
                            `email` VARCHAR(100) NOT NULL,
                            `password` VARCHAR(255) NOT NULL,
                            `role` ENUM('admin', 'editor', 'user') NOT NULL DEFAULT 'user',
                            `created_at` DATETIME NOT NULL,
                            `last_login` DATETIME NULL
                        )
                    ");
                    
                    // Hash password
                    $hashedPassword = password_hash($adminPass, PASSWORD_DEFAULT);
                    
                    // Insert admin user
                    $stmt = $conn->prepare("
                        INSERT INTO `admin_users` 
                        (`username`, `email`, `password`, `role`, `created_at`) 
                        VALUES 
                        (?, 'admin@example.com', ?, 'admin', NOW())
                        ON DUPLICATE KEY UPDATE 
                        `password` = ?, `role` = 'admin'
                    ");
                    
                    $stmt->execute([$adminUser, $hashedPassword, $hashedPassword]);
                    
                    $installed = true;
                    $message = 'Installation completed successfully!';
                } catch (PDOException $e) {
                    $error = true;
                    $message = 'Admin user creation failed: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L1J Database Website Installation</title>
    <style>
        :root {
            --text: #ffffff;
            --background: #030303;
            --primary: #080808;
            --secondary: #0a0a0a;
            --accent: #f94b1f;
            --border-color: #1a1a1a;
            --success: #4caf50;
            --danger: #f44336;
            --warning: #ff9800;
            --info: #2196f3;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: var(--primary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .header {
            background-color: var(--secondary);
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .header p {
            color: #cccccc;
            font-size: 16px;
        }
        
        .content {
            padding: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            border-left: 4px solid var(--danger);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            background-color: var(--secondary);
            color: var(--text);
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--accent);
        }
        
        .btn {
            display: inline-block;
            background-color: var(--accent);
            color: var(--text);
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #ff6b43;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .requirement {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: var(--secondary);
            border-radius: 4px;
            margin-bottom: 8px;
        }
        
        .requirement-label {
            display: flex;
            align-items: center;
        }
        
        .requirement-status {
            font-weight: 600;
        }
        
        .status-passed {
            color: var(--success);
        }
        
        .status-failed {
            color: var(--danger);
        }
        
        .footer {
            padding: 15px 20px;
            text-align: center;
            background-color: var(--secondary);
            border-top: 1px solid var(--border-color);
            font-size: 14px;
            color: #999;
        }
        
        .completion-message {
            text-align: center;
            margin: 30px 0;
        }
        
        .completion-icon {
            font-size: 48px;
            color: var(--success);
            margin-bottom: 20px;
        }
        
        .completion-actions {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .tab-navigation {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .tab-link {
            padding: 10px 20px;
            cursor: pointer;
            color: #cccccc;
            font-weight: 500;
        }
        
        .tab-link.active {
            color: var(--text);
            border-bottom: 2px solid var(--accent);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>L1J Database Website Installation</h1>
            <p>Follow the steps below to install the L1J Database Website</p>
        </div>
        
        <div class="content">
            <?php if ($installed): ?>
                <div class="completion-message">
                    <div class="completion-icon">✓</div>
                    <h2>Installation Completed Successfully!</h2>
                    <p>The L1J Database Website has been installed and is ready to use.</p>
                    
                    <div class="completion-actions">
                        <a href="../index.php" class="btn">Go to Homepage</a>
                        <a href="../admin/index.php" class="btn">Go to Admin Panel</a>
                    </div>
                </div>
            <?php elseif ($error): ?>
                <?php displayError($message); ?>
                <a href="install.php" class="btn">Try Again</a>
            <?php else: ?>
                <div class="tab-navigation">
                    <div class="tab-link active" data-tab="requirements">Requirements</div>
                    <div class="tab-link" data-tab="database">Database Setup</div>
                    <div class="tab-link" data-tab="admin">Admin Account</div>
                </div>
                
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <div id="tab-requirements" class="tab-content active">
                        <div class="section">
                            <h3 class="section-title">System Requirements</h3>
                            
                            <?php
                            // Check PHP version
                            $phpVersion = phpversion();
                            $phpOk = version_compare($phpVersion, '7.4.0', '>=');
                            ?>
                            <div class="requirement">
                                <div class="requirement-label">
                                    PHP Version (7.4+)
                                </div>
                                <div class="requirement-status <?php echo $phpOk ? 'status-passed' : 'status-failed'; ?>">
                                    <?php echo $phpVersion; ?> <?php echo $phpOk ? '✓' : '✗'; ?>
                                </div>
                            </div>
                            
                            <?php
                            // Check PDO extension
                            $pdoOk = extension_loaded('pdo_mysql');
                            ?>
                            <div class="requirement">
                                <div class="requirement-label">
                                    PDO MySQL Extension
                                </div>
                                <div class="requirement-status <?php echo $pdoOk ? 'status-passed' : 'status-failed'; ?>">
                                    <?php echo $pdoOk ? 'Installed ✓' : 'Not Installed ✗'; ?>
                                </div>
                            </div>
                            
                            <?php
                            // Check if config.php is writable
                            $configPath = '../includes/config.php';
                            $configOk = is_writable($configPath);
                            ?>
                            <div class="requirement">
                                <div class="requirement-label">
                                    Config File Writable
                                </div>
                                <div class="requirement-status <?php echo $configOk ? 'status-passed' : 'status-failed'; ?>">
                                    <?php echo $configOk ? 'Writable ✓' : 'Not Writable ✗'; ?>
                                </div>
                            </div>
                            
                            <?php
                            // Check if uploads directory is writable
                            $uploadsPath = '../assets/uploads';
                            $uploadsOk = is_dir($uploadsPath) && is_writable($uploadsPath);
                            ?>
                            <div class="requirement">
                                <div class="requirement-label">
                                    Uploads Directory Writable
                                </div>
                                <div class="requirement-status <?php echo $uploadsOk ? 'status-passed' : 'status-failed'; ?>">
                                    <?php echo $uploadsOk ? 'Writable ✓' : 'Not Writable ✗'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="button" class="btn next-tab" data-next="database" <?php echo (!$phpOk || !$pdoOk || !$configOk) ? 'disabled' : ''; ?>>Continue to Database Setup</button>
                        </div>
                    </div>
                    
                    <div id="tab-database" class="tab-content">
                        <div class="section">
                            <h3 class="section-title">Database Configuration</h3>
                            
                            <div class="form-group">
                                <label for="db_host">Database Host</label>
                                <input type="text" id="db_host" name="db_host" value="localhost" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="db_user">Database Username</label>
                                <input type="text" id="db_user" name="db_user" value="root" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="db_pass">Database Password</label>
                                <input type="password" id="db_pass" name="db_pass" value="">
                            </div>
                            
                            <div class="form-group">
                                <label for="db_name">Database Name</label>
                                <input type="text" id="db_name" name="db_name" value="l1j_remastered" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="button" class="btn prev-tab" data-prev="requirements">Previous</button>
                            <button type="button" class="btn next-tab" data-next="admin">Continue to Admin Setup</button>
                        </div>
                    </div>
                    
                    <div id="tab-admin" class="tab-content">
                        <div class="section">
                            <h3 class="section-title">Admin Account Setup</h3>
                            
                            <div class="form-group">
                                <label for="admin_user">Admin Username</label>
                                <input type="text" id="admin_user" name="admin_user" value="admin" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_pass">Admin Password</label>
                                <input type="password" id="admin_pass" name="admin_pass" value="password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="button" class="btn prev-tab" data-prev="database">Previous</button>
                            <button type="submit" class="btn">Install</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            &copy; <?php echo date('Y'); ?> L1J Database Website
        </div>
    </div>
    
    <script>
        // Tab navigation
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');
            const nextButtons = document.querySelectorAll('.next-tab');
            const prevButtons = document.querySelectorAll('.prev-tab');
            
            // Tab click event
            tabLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Hide all tabs
                    tabLinks.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show selected tab
                    this.classList.add('active');
                    document.getElementById('tab-' + tabId).classList.add('active');
                });
            });
            
            // Next button click event
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const nextTab = this.getAttribute('data-next');
                    
                    // Hide all tabs
                    tabLinks.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show next tab
                    document.querySelector('.tab-link[data-tab="' + nextTab + '"]').classList.add('active');
                    document.getElementById('tab-' + nextTab).classList.add('active');
                });
            });
            
            // Previous button click event
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const prevTab = this.getAttribute('data-prev');
                    
                    // Hide all tabs
                    tabLinks.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show previous tab
                    document.querySelector('.tab-link[data-tab="' + prevTab + '"]').classList.add('active');
                    document.getElementById('tab-' + prevTab).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>