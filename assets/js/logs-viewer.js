/**
 * Logs Viewer JavaScript
 * Handles log display functionality for L1J Database Website
 */

document.addEventListener('DOMContentLoaded', function() {
    // Log expansion toggle functionality
    const expandLogsBtn = document.getElementById('expandLogsBtn');
    if (expandLogsBtn) {
        const initialView = document.getElementById('logs-initial-view');
        const expandedView = document.getElementById('logs-expanded-view');
        
        expandLogsBtn.addEventListener('click', function() {
            const isExpanded = expandedView.style.display !== 'none';
            
            if (isExpanded) {
                // Collapse view
                initialView.style.display = '';
                expandedView.style.display = 'none';
                expandLogsBtn.innerHTML = '<i class="fas fa-expand-alt"></i> Show More';
            } else {
                // Expand view
                initialView.style.display = 'none';
                expandedView.style.display = '';
                expandLogsBtn.innerHTML = '<i class="fas fa-compress-alt"></i> Show Less';
            }
        });
    }

    // Log filter functionality
    const logTypeFilter = document.getElementById('logTypeFilter');
    if (logTypeFilter) {
        logTypeFilter.addEventListener('change', function() {
            const selectedType = this.value;
            const initialRows = document.querySelectorAll('#logs-initial-view tr');
            const expandedRows = document.querySelectorAll('#logs-expanded-view tr');
            
            // Filter initial view
            initialRows.forEach(row => {
                if (selectedType === 'all' || row.getAttribute('data-log-type') === selectedType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Filter expanded view
            expandedRows.forEach(row => {
                // Skip the "View All" row in expanded view
                if (row.classList.contains('view-more-row')) {
                    return;
                }
                
                if (selectedType === 'all' || row.getAttribute('data-log-type') === selectedType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
