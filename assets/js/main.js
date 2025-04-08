/**
 * Main JavaScript for L1J Database Website
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (mobileMenu && !mobileMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
            mobileMenu.classList.remove('active');
        }
    });
    
    // Stat bars initialization
    const statBars = document.querySelectorAll('.stat-fill');
    if (statBars.length > 0) {
        statBars.forEach(bar => {
            const percentage = bar.getAttribute('data-percentage') || 0;
            bar.style.width = `${percentage}%`;
        });
    }
    
    // Search functionality
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = document.getElementById('search-input');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                alert('Please enter a search term');
            }
        });
    }
    
    // Tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    if (tooltips.length > 0) {
        tooltips.forEach(tooltip => {
            tooltip.addEventListener('mouseenter', showTooltip);
            tooltip.addEventListener('mouseleave', hideTooltip);
        });
    }
    
    function showTooltip(e) {
        const tooltipText = this.getAttribute('data-tooltip');
        
        // Create tooltip element
        const tooltipEl = document.createElement('div');
        tooltipEl.className = 'tooltip';
        tooltipEl.textContent = tooltipText;
        
        // Append to body
        document.body.appendChild(tooltipEl);
        
        // Position tooltip
        const rect = this.getBoundingClientRect();
        tooltipEl.style.top = `${rect.top - tooltipEl.offsetHeight - 10 + window.scrollY}px`;
        tooltipEl.style.left = `${rect.left + (rect.width / 2) - (tooltipEl.offsetWidth / 2) + window.scrollX}px`;
        
        // Show tooltip
        tooltipEl.classList.add('show');
        
        // Store reference to tooltip
        this._tooltip = tooltipEl;
    }
    
    function hideTooltip() {
        if (this._tooltip) {
            this._tooltip.remove();
            this._tooltip = null;
        }
    }
    
    // Flash message auto-dismiss
    const flash = document.querySelector('.alert');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => {
                flash.remove();
            }, 300);
        }, 5000);
    }
    
    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll('.copy-btn');
    if (copyButtons.length > 0) {
        copyButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const textToCopy = this.getAttribute('data-copy');
                
                // Create temporary textarea
                const textarea = document.createElement('textarea');
                textarea.value = textToCopy;
                textarea.style.position = 'fixed'; // Avoid scrolling to bottom
                document.body.appendChild(textarea);
                textarea.select();
                
                try {
                    // Copy text
                    document.execCommand('copy');
                    
                    // Visual feedback
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';
                    
                    // Reset after 2 seconds
                    setTimeout(() => {
                        this.textContent = originalText;
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy text:', err);
                }
                
                // Remove temporary textarea
                document.body.removeChild(textarea);
            });
        });
    }
    
    // Item detail tabs
    const tabButtons = document.querySelectorAll('.tab-button');
    if (tabButtons.length > 0) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Get tab id
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and tabs
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Add active class to current button and tab
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    }
    
    // Table sorting
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    if (sortableHeaders.length > 0) {
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const sortBy = this.getAttribute('data-sort');
                const sortOrder = this.getAttribute('data-order') || 'asc';
                
                // Toggle sort order
                const newSortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
                this.setAttribute('data-order', newSortOrder);
                
                // Remove sort indicators from all headers
                document.querySelectorAll('th[data-sort]').forEach(th => {
                    th.classList.remove('sort-asc', 'sort-desc');
                });
                
                // Add sort indicator to current header
                this.classList.add(`sort-${newSortOrder}`);
                
                // Sort the table
                sortTable(sortBy, newSortOrder);
            });
        });
    }
    
    function sortTable(sortBy, sortOrder) {
        const table = document.querySelector('table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Sort rows
        rows.sort((a, b) => {
            const aValue = a.querySelector(`td[data-value="${sortBy}"]`).getAttribute('data-sort-value') || 
                          a.querySelector(`td[data-value="${sortBy}"]`).textContent.trim();
            const bValue = b.querySelector(`td[data-value="${sortBy}"]`).getAttribute('data-sort-value') || 
                          b.querySelector(`td[data-value="${sortBy}"]`).textContent.trim();
            
            // Check if values are numbers
            const aNum = !isNaN(aValue) ? Number(aValue) : aValue;
            const bNum = !isNaN(bValue) ? Number(bValue) : bValue;
            
            // Compare values
            if (sortOrder === 'asc') {
                return aNum < bNum ? -1 : aNum > bNum ? 1 : 0;
            } else {
                return aNum > bNum ? -1 : aNum < bNum ? 1 : 0;
            }
        });
        
        // Reorder rows in the DOM
        rows.forEach(row => {
            tbody.appendChild(row);
        });
    }
});
