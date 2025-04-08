/**
 * Admin JavaScript for L1J Database Website
 */

document.addEventListener('DOMContentLoaded', function() {
    // Admin sidebar toggle for mobile
    const sidebarToggle = document.querySelector('.admin-toggle-sidebar');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Admin user dropdown
    const userDropdownToggle = document.querySelector('.admin-user-info');
    const userDropdownContent = document.querySelector('.admin-dropdown-content');
    
    if (userDropdownToggle && userDropdownContent) {
        userDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdownContent.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            if (userDropdownContent.classList.contains('show')) {
                userDropdownContent.classList.remove('show');
            }
        });
    }
    
    // Toast notification
    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Icon based on type
        let icon = '';
        switch (type) {
            case 'success':
                icon = '<i class="fas fa-check-circle toast-icon"></i>';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle toast-icon"></i>';
                break;
            case 'warning':
                icon = '<i class="fas fa-exclamation-triangle toast-icon"></i>';
                break;
            default:
                icon = '<i class="fas fa-info-circle toast-icon"></i>';
        }
        
        // Set toast content
        toast.innerHTML = `
            ${icon}
            <div class="toast-message">${message}</div>
            <div class="toast-close"><i class="fas fa-times"></i></div>
        `;
        
        // Add toast to container
        toastContainer.appendChild(toast);
        
        // Close toast when clicking the close button
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function() {
            removeToast(toast);
        });
        
        // Auto-remove toast after 5 seconds
        setTimeout(() => {
            removeToast(toast);
        }, 5000);
    }
    
    function removeToast(toast) {
        toast.classList.add('toast-hide');
        
        // Remove from DOM after animation
        setTimeout(() => {
            toast.remove();
            
            // Remove container if empty
            const toastContainer = document.querySelector('.toast-container');
            if (toastContainer && toastContainer.children.length === 0) {
                toastContainer.remove();
            }
        }, 300);
    }
    
    // Make toast function available globally
    window.showToast = showToast;
    
    // Confirm delete
    const deleteButtons = document.querySelectorAll('.delete-btn');
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    if (forms.length > 0) {
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let hasError = false;
                
                requiredFields.forEach(field => {
                    // Clear previous error
                    const errorElement = field.parentNode.querySelector('.field-error');
                    if (errorElement) {
                        errorElement.remove();
                    }
                    
                    // Check if field is empty
                    if (!field.value.trim()) {
                        e.preventDefault();
                        hasError = true;
                        
                        // Add error message
                        const error = document.createElement('div');
                        error.className = 'field-error';
                        error.textContent = 'This field is required';
                        field.parentNode.appendChild(error);
                        
                        // Highlight field
                        field.classList.add('input-error');
                    } else {
                        field.classList.remove('input-error');
                    }
                });
                
                if (hasError) {
                    showToast('Please fill all required fields', 'error');
                }
            });
        });
    }
    
    // Image upload preview
    const imageUploads = document.querySelectorAll('.image-upload');
    if (imageUploads.length > 0) {
        imageUploads.forEach(upload => {
            const input = upload.querySelector('input[type="file"]');
            const preview = upload.querySelector('.image-preview');
            
            if (input && preview) {
                input.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            preview.style.backgroundImage = `url('${e.target.result}')`;
                            preview.classList.add('has-image');
                        };
                        
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        });
    }
    
    // Rich text editor initialization
    const editors = document.querySelectorAll('.rich-editor');
    if (editors.length > 0) {
        editors.forEach(editor => {
            // Simple implementation - can be replaced with a library like TinyMCE
            const toolbar = document.createElement('div');
            toolbar.className = 'editor-toolbar';
            toolbar.innerHTML = `
                <button type="button" data-command="bold"><i class="fas fa-bold"></i></button>
                <button type="button" data-command="italic"><i class="fas fa-italic"></i></button>
                <button type="button" data-command="underline"><i class="fas fa-underline"></i></button>
                <button type="button" data-command="insertOrderedList"><i class="fas fa-list-ol"></i></button>
                <button type="button" data-command="insertUnorderedList"><i class="fas fa-list-ul"></i></button>
                <button type="button" data-command="createLink"><i class="fas fa-link"></i></button>
                <button type="button" data-command="insertImage"><i class="fas fa-image"></i></button>
            `;
            
            const editorArea = document.createElement('div');
            editorArea.className = 'editor-content';
            editorArea.contentEditable = true;
            
            // Hidden input to store HTML content
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = editor.getAttribute('data-name');
            
            // Initial content
            if (editor.textContent.trim()) {
                editorArea.innerHTML = editor.textContent;
            }
            
            // Replace textarea with editor
            editor.innerHTML = '';
            editor.appendChild(toolbar);
            editor.appendChild(editorArea);
            editor.appendChild(hiddenInput);
            
            // Handle toolbar buttons
            const buttons = toolbar.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const command = this.getAttribute('data-command');
                    
                    if (command === 'createLink') {
                        const url = prompt('Enter the link URL:');
                        if (url) {
                            document.execCommand(command, false, url);
                        }
                    } else if (command === 'insertImage') {
                        const url = prompt('Enter the image URL:');
                        if (url) {
                            document.execCommand(command, false, url);
                        }
                    } else {
                        document.execCommand(command, false, null);
                    }
                    
                    // Update hidden input
                    hiddenInput.value = editorArea.innerHTML;
                });
            });
            
            // Update hidden input on content change
            editorArea.addEventListener('input', function() {
                hiddenInput.value = this.innerHTML;
            });
            
            // Initialize with current content
            hiddenInput.value = editorArea.innerHTML;
        });
    }
    
    // Data tables with search and pagination
    const dataTables = document.querySelectorAll('.data-table');
    if (dataTables.length > 0) {
        dataTables.forEach(table => {
            const wrapper = document.createElement('div');
            wrapper.className = 'data-table-wrapper';
            
            const tableSearch = document.createElement('div');
            tableSearch.className = 'data-table-search';
            tableSearch.innerHTML = `
                <input type="text" placeholder="Search..." class="data-table-search-input">
            `;
            
            // Insert wrapper and search before table
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(tableSearch);
            wrapper.appendChild(table);
            
            // Add pagination container
            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'data-table-pagination';
            wrapper.appendChild(paginationContainer);
            
            // Initialize data table
            initDataTable(table, tableSearch.querySelector('input'), paginationContainer);
        });
    }
    
    function initDataTable(table, searchInput, paginationContainer) {
        const itemsPerPage = parseInt(table.getAttribute('data-items-per-page')) || 10;
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        
        let currentPage = 1;
        let filteredRows = rows;
        
        // Handle search
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            filteredRows = rows.filter(row => {
                const text = row.textContent.toLowerCase();
                return text.includes(searchTerm);
            });
            
            currentPage = 1;
            renderTable();
        });
        
        // Initial render
        renderTable();
        
        function renderTable() {
            // Calculate pagination
            const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            
            // Clear table body
            const tbody = table.querySelector('tbody');
            tbody.innerHTML = '';
            
            // Show message if no results
            if (filteredRows.length === 0) {
                const messageRow = document.createElement('tr');
                messageRow.innerHTML = `<td colspan="${table.querySelectorAll('th').length}" class="no-results">No results found</td>`;
                tbody.appendChild(messageRow);
            } else {
                // Add visible rows
                filteredRows.slice(startIndex, endIndex).forEach(row => {
                    tbody.appendChild(row);
                });
            }
            
            // Render pagination
            renderPagination(totalPages);
        }
        
        function renderPagination(totalPages) {
            paginationContainer.innerHTML = '';
            
            if (totalPages <= 1) {
                return;
            }
            
            const pagination = document.createElement('ul');
            pagination.className = 'pagination';
            
            // Previous button
            const prevButton = document.createElement('li');
            prevButton.innerHTML = '<a href="#" aria-label="Previous">&laquo;</a>';
            prevButton.className = currentPage === 1 ? 'disabled' : '';
            prevButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            });
            pagination.appendChild(prevButton);
            
            // Page numbers
            const maxPageLinks = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxPageLinks / 2));
            let endPage = Math.min(totalPages, startPage + maxPageLinks - 1);
            
            if (endPage - startPage + 1 < maxPageLinks) {
                startPage = Math.max(1, endPage - maxPageLinks + 1);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const pageButton = document.createElement('li');
                pageButton.className = i === currentPage ? 'active' : '';
                pageButton.innerHTML = `<a href="#">${i}</a>`;
                pageButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentPage = i;
                    renderTable();
                });
                pagination.appendChild(pageButton);
            }
            
            // Next button
            const nextButton = document.createElement('li');
            nextButton.innerHTML = '<a href="#" aria-label="Next">&raquo;</a>';
            nextButton.className = currentPage === totalPages ? 'disabled' : '';
            nextButton.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            });
            pagination.appendChild(nextButton);
            
            paginationContainer.appendChild(pagination);
        }
    }
});
