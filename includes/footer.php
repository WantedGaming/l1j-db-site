</div>
    </main>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-heading">L1J Database</h3>
                    <p>A comprehensive database for the L1J Remastered MMORPG. Browse items, monsters, skills, and more.</p>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/items/">Items</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/monsters/">Monsters</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/skills/">Skills</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/maps/">Maps</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-heading">Resources</h3>
                    <ul class="footer-links">
                        <li><a href="https://github.com/your-repo/l1j-remastered" target="_blank">GitHub Repository</a></li>
                        <li><a href="#" target="_blank">Official Game Site</a></li>
                        <li><a href="#" target="_blank">Game Wiki</a></li>
                        <li><a href="#" target="_blank">Community Forum</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> L1J Database. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
