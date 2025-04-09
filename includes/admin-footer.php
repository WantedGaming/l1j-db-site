<?php
/**
 * Admin Footer for L1J Database Website
 */
?>

        </div><!-- End of .admin-content -->
    </div><!-- End of .admin-wrapper -->

    <!-- Footer -->
    <footer class="admin-footer">
        <div class="admin-footer-content">
            <p>&copy; <?php echo date('Y'); ?> L1J Database Admin - All rights reserved</p>
            <p>
                <span>Version: 1.0.0</span> | 
                <span>Server Time: <?php echo date('Y-m-d H:i:s'); ?></span>
            </p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
    
    <!-- Toast/Notification System -->
    <div id="toast-container" class="toast-container"></div>
    <script>
        // Toast notification function
        function showToast(message, type = 'info', duration = 5000) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                </div>
                <div class="toast-message">${message}</div>
                <button class="toast-close"><i class="fas fa-times"></i></button>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            // Add show class after a small delay for transition effect
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);
            
            // Setup close button
            toast.querySelector('.toast-close').addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            });
            
            // Auto remove after duration
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 300);
                }
            }, duration);
        }
        
        // Display any flash messages as toasts
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            
            flashMessages.forEach(flash => {
                const type = flash.dataset.type || 'info';
                const message = flash.textContent;
                
                showToast(message, type);
                
                // Hide the original flash message
                flash.style.display = 'none';
            });
        });
    </script>
</body>
</html>
