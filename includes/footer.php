</div>
    </main>
    
    <footer>
		<div class="container">
			<div class="footer-content">
				<div class="footer-section">
					<h3 class="footer-heading">L1J Database</h3>
					<p>A comprehensive database for the L1J Remastered MMORPG. Browse items, monsters, skills, and more.</p>
				</div>
            
            <!-- Replace Quick Links & Resources with an image -->
				<div class="footer-section">
					<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/class/header/48_1.png" alt="Footer Image" class="footer-image">
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
