<?php
/**
 * Admin Import/Export Maps Tool for L1J Database Website
 */

// Set page title
$pageTitle = 'Import/Export Maps';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Initialize variables
$message = '';
$messageType = '';
$maps = [];

// Get all maps for export
$maps = $db->getRows("SELECT * FROM mapids ORDER BY mapid ASC");

// Handle import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'import') {
        // Check if a file was uploaded
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['import_file'];
            
            // Check file type
            $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
            if ($fileType !== 'json' && $fileType !== 'csv') {
                $message = 'Only JSON and CSV files are supported for import.';
                $messageType = 'error';
            } else {
                // Read file content
                $fileContent = file_get_contents($file['tmp_name']);
                
                if ($fileType === 'json') {
                    // Parse JSON
                    $importData = json_decode($fileContent, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $message = 'Invalid JSON format: ' . json_last_error_msg();
                        $messageType = 'error';
                    } else {
                        // Process JSON import
                        $importCount = 0;
                        $errorCount = 0;
                        
                        foreach ($importData as $mapData) {
                            // Check if map ID exists
                            $existingMap = $db->getRow("SELECT mapid FROM mapids WHERE mapid = ?", [$mapData['mapid']]);
                            
                            try {
                                if ($existingMap) {
                                    // Update existing map
                                    $result = $db->update('mapids', $mapData, 'mapid = :mapid', ['mapid' => $mapData['mapid']]);
                                } else {
                                    // Insert new map
                                    $result = $db->insert('mapids', $mapData);
                                }
                                
                                if ($result) {
                                    $importCount++;
                                } else {
                                    $errorCount++;
                                }
                            } catch (Exception $e) {
                                $errorCount++;
                            }
                        }
                        
                        $message = "Import complete: $importCount maps imported successfully, $errorCount errors.";
                        $messageType = $errorCount > 0 ? 'warning' : 'success';
                    }
                } else if ($fileType === 'csv') {
                    // Parse CSV
                    $rows = array_map('str_getcsv', explode("\n", $fileContent));
                    
                    if (count($rows) < 2) {
                        $message = 'Invalid CSV format: File must contain at least a header row and one data row.';
                        $messageType = 'error';
                    } else {
                        // Get header row
                        $headers = $rows[0];
                        
                        // Process CSV import
                        $importCount = 0;
                        $errorCount = 0;
                        
                        for ($i = 1; $i < count($rows); $i++) {
                            if (count($rows[$i]) === count($headers)) {
                                $mapData = array_combine($headers, $rows[$i]);
                                
                                // Skip empty rows
                                if (empty($mapData['mapid'])) {
                                    continue;
                                }
                                
                                // Check if map ID exists
                                $existingMap = $db->getRow("SELECT mapid FROM mapids WHERE mapid = ?", [$mapData['mapid']]);
                                
                                try {
                                    if ($existingMap) {
                                        // Update existing map
                                        $result = $db->update('mapids', $mapData, 'mapid = :mapid', ['mapid' => $mapData['mapid']]);
                                    } else {
                                        // Insert new map
                                        $result = $db->insert('mapids', $mapData);
                                    }
                                    
                                    if ($result) {
                                        $importCount++;
                                    } else {
                                        $errorCount++;
                                    }
                                } catch (Exception $e) {
                                    $errorCount++;
                                }
                            } else {
                                $errorCount++;
                            }
                        }
                        
                        $message = "Import complete: $importCount maps imported successfully, $errorCount errors.";
                        $messageType = $errorCount > 0 ? 'warning' : 'success';
                    }
                }
                
                // Refresh maps data after import
                $maps = $db->getRows("SELECT * FROM mapids ORDER BY mapid ASC");
            }
        } else {
            $message = 'Please select a file to import.';
            $messageType = 'error';
        }
    } else if ($_POST['action'] === 'export') {
        // Handle export (handled by JavaScript)
    }
}
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Import/Export Maps</h1>
                <p class="admin-hero-subtitle">Manage map data with import and export tools</p>
                
                <div class="mt-3">
                    <a href="index.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Import Section -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Import Maps</h3>
                </div>
                <div class="card-body">
                    <form action="import-export.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="import">
                        
                        <div class="form-group">
                            <label for="import_file">Select File (JSON or CSV)</label>
                            <input type="file" id="import_file" name="import_file" class="form-control" accept=".json,.csv">
                            <small class="text-muted">Select a JSON or CSV file containing map data</small>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" id="overwrite" name="overwrite" class="form-check-input">
                            <label class="form-check-label" for="overwrite">Overwrite existing maps</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Import Maps</button>
                    </form>
                    
                    <div class="mt-4">
                        <h4>Import Format</h4>
                        <p>Your import file should follow this structure:</p>
                        
                        <div class="mt-3">
                            <h5>JSON Format</h5>
                            <pre style="background-color: #1a1a1a; padding: 10px; border-radius: 5px;"><code>[
  {
    "mapid": 1,
    "locationname": "Example Map",
    "dungeon": 0,
    ...
  },
  ...
]</code></pre>
                        </div>
                        
                        <div class="mt-3">
                            <h5>CSV Format</h5>
                            <pre style="background-color: #1a1a1a; padding: 10px; border-radius: 5px;"><code>mapid,locationname,desc_kr,startX,endX,startY,endY,...
1,"Example Map","예시 맵",32256,32767,32768,33279,...</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Export Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Export Maps</h3>
                </div>
                <div class="card-body">
                    <form action="import-export.php" method="POST" id="export-form">
                        <input type="hidden" name="action" value="export">
                        
                        <div class="form-group">
                            <label for="export_format">Export Format</label>
                            <select id="export_format" name="export_format" class="form-control">
                                <option value="json">JSON</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="export_selection">Maps to Export</label>
                            <select id="export_selection" name="export_selection" class="form-control">
                                <option value="all">All Maps</option>
                                <option value="field">Field Maps Only</option>
                                <option value="dungeon">Dungeon Maps Only</option>
                                <option value="custom">Custom Selection</option>
                            </select>
                        </div>
                        
                        <div id="custom-selection" style="display: none; max-height: 300px; overflow-y: auto; margin-bottom: 15px;">
                            <?php foreach ($maps as $map): ?>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input map-checkbox" id="map_<?= $map['mapid'] ?>" name="map_ids[]" value="<?= $map['mapid'] ?>">
                                    <label class="form-check-label" for="map_<?= $map['mapid'] ?>">
                                        <?= htmlspecialchars($map['locationname']) ?> (ID: <?= $map['mapid'] ?>)
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <button type="button" id="export-btn" class="btn btn-primary">Export Maps</button>
                    </form>
                    
                    <div class="mt-4">
                        <h4>Export Preview</h4>
                        <p>Total Maps: <span id="export-count"><?= count($maps) ?></span></p>
                        <div id="export-preview" style="max-height: 200px; overflow-y: auto; background-color: #1a1a1a; padding: 10px; border-radius: 5px;">
                            <code>Select an export format and click Export to see a preview</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide custom selection based on export selection
    const exportSelection = document.getElementById('export_selection');
    const customSelection = document.getElementById('custom-selection');
    
    exportSelection.addEventListener('change', function() {
        if (this.value === 'custom') {
            customSelection.style.display = 'block';
        } else {
            customSelection.style.display = 'none';
        }
        updateExportPreview();
    });
    
    // Update export preview when format changes
    const exportFormat = document.getElementById('export_format');
    exportFormat.addEventListener('change', updateExportPreview);
    
    // Handle map checkboxes
    const mapCheckboxes = document.querySelectorAll('.map-checkbox');
    mapCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateExportPreview);
    });
    
    // Export button click handler
    const exportBtn = document.getElementById('export-btn');
    exportBtn.addEventListener('click', function() {
        const format = exportFormat.value;
        const selection = exportSelection.value;
        
        // Get selected map IDs for custom selection
        let mapIds = [];
        if (selection === 'custom') {
            mapCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    mapIds.push(parseInt(checkbox.value));
                }
            });
            
            if (mapIds.length === 0) {
                alert('Please select at least one map to export.');
                return;
            }
        }
        
        // Get maps data
        let mapsData = <?= json_encode($maps) ?>;
        
        // Filter maps based on selection
        if (selection === 'field') {
            mapsData = mapsData.filter(map => !map.dungeon);
        } else if (selection === 'dungeon') {
            mapsData = mapsData.filter(map => map.dungeon);
        } else if (selection === 'custom') {
            mapsData = mapsData.filter(map => mapIds.includes(parseInt(map.mapid)));
        }
        
        // Create export data
        let exportData;
        let fileName;
        let contentType;
        
        if (format === 'json') {
            exportData = JSON.stringify(mapsData, null, 2);
            fileName = 'maps_export.json';
            contentType = 'application/json';
        } else if (format === 'csv') {
            // Create CSV header from keys of first map
            const headers = Object.keys(mapsData[0]);
            let csv = headers.join(',') + '\n';
            
            // Add data rows
            mapsData.forEach(map => {
                const row = headers.map(header => {
                    const value = map[header];
                    // Handle string values with commas by wrapping in quotes
                    if (typeof value === 'string' && value.includes(',')) {
                        return `"${value}"`;
                    }
                    return value;
                });
                csv += row.join(',') + '\n';
            });
            
            exportData = csv;
            fileName = 'maps_export.csv';
            contentType = 'text/csv';
        }
        
        // Create download link
        const blob = new Blob([exportData], { type: contentType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        a.click();
        URL.revokeObjectURL(url);
    });
    
    // Function to update export preview
    function updateExportPreview() {
        const format = exportFormat.value;
        const selection = exportSelection.value;
        const exportPreview = document.getElementById('export-preview');
        const exportCount = document.getElementById('export-count');
        
        // Get maps data
        let mapsData = <?= json_encode($maps) ?>;
        
        // Filter maps based on selection
        if (selection === 'field') {
            mapsData = mapsData.filter(map => !map.dungeon);
        } else if (selection === 'dungeon') {
            mapsData = mapsData.filter(map => map.dungeon);
        } else if (selection === 'custom') {
            const mapIds = [];
            mapCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    mapIds.push(parseInt(checkbox.value));
                }
            });
            mapsData = mapsData.filter(map => mapIds.includes(parseInt(map.mapid)));
        }
        
        // Update export count
        exportCount.textContent = mapsData.length;
        
        // Create preview
        let preview;
        
        if (mapsData.length === 0) {
            preview = 'No maps selected for export.';
        } else {
            if (format === 'json') {
                // Show first 2 maps and truncate if more
                const previewData = mapsData.slice(0, 2);
                preview = JSON.stringify(previewData, null, 2);
                
                if (mapsData.length > 2) {
                    preview += '\n// ... and ' + (mapsData.length - 2) + ' more maps';
                }
            } else if (format === 'csv') {
                // Create CSV header from keys of first map
                const headers = Object.keys(mapsData[0]);
                let csv = headers.join(',') + '\n';
                
                // Add first 2 data rows and truncate if more
                const previewData = mapsData.slice(0, 2);
                previewData.forEach(map => {
                    const row = headers.map(header => {
                        const value = map[header];
                        if (typeof value === 'string' && value.includes(',')) {
                            return `"${value}"`;
                        }
                        return value;
                    });
                    csv += row.join(',') + '\n';
                });
                
                if (mapsData.length > 2) {
                    csv += '// ... and ' + (mapsData.length - 2) + ' more maps';
                }
                
                preview = csv;
            }
        }
        
        exportPreview.innerHTML = `<code>${preview}</code>`;
    }
    
    // Initial preview update
    updateExportPreview();
});
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>
