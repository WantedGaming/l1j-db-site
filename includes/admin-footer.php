</main>
        
        <!-- Admin Footer -->
        <footer class="admin-footer">
            <div class="admin-footer-container">
                <div class="admin-footer-text">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> - Admin Panel
                </div>
                <div class="admin-footer-nav">
                    <a href="<?php echo SITE_URL; ?>" class="admin-footer-link">View Website</a>
                    <a href="<?php echo SITE_URL; ?>/admin/help.php" class="admin-footer-link">Help</a>
                    <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="admin-footer-link">Logout</a>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Toast Container for Notifications -->
    <div class="toast-container"></div>
    
    <!-- Main JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Admin JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
    
    <script>
    // Toast notification function
    function showToast(message, type = 'info', duration = 5000) {
        const toastContainer = document.querySelector('.toast-container');
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        // Set toast icon
        let icon = '';
        switch (type) {
            case 'success':
                icon = '<i class="fas fa-check-circle"></i>';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle"></i>';
                break;
            case 'warning':
                icon = '<i class="fas fa-exclamation-triangle"></i>';
                break;
            default:
                icon = '<i class="fas fa-info-circle"></i>';
        }
        
        // Set toast content
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">${message}</div>
            <div class="toast-close"><i class="fas fa-times"></i></div>
        `;
        
        // Add toast to container
        toastContainer.appendChild(toast);
        
        // Set timeout to remove toast
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);
        
        // Add click event to close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.remove();
            }, 300);
        });
    }
    </script>
</body>
</html>