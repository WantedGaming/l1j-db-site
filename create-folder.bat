@echo off
echo Creating L1J Database Website folder structure...

mkdir lineageadmin
cd lineageadmin

:: Create main directories
mkdir admin
mkdir assets
mkdir assets\css
mkdir assets\js
mkdir assets\img
mkdir assets\img\items
mkdir assets\img\monsters
mkdir assets\img\backgrounds
mkdir assets\fonts
mkdir includes
mkdir models       :: Added models directory
mkdir pages
mkdir pages\items
mkdir pages\monsters
mkdir pages\characters
mkdir pages\skills
mkdir pages\maps
mkdir api
mkdir install

:: Create admin subdirectories
mkdir admin\items
mkdir admin\monsters
mkdir admin\characters
mkdir admin\skills
mkdir admin\users

:: Create empty files in includes
echo // Database configuration > includes\config.php
echo // Database connection functions > includes\database.php
echo // Authentication functions > includes\auth.php
echo // Helper functions > includes\functions.php
echo // Site header > includes\header.php
echo // Site footer > includes\footer.php
echo // Site sidebar > includes\sidebar.php
echo // Admin header > includes\admin-header.php

:: Create empty model files
echo // Item model > models\Item.php
echo // Monster model > models\Monster.php
echo // Skill model > models\Skill.php

:: Create empty CSS files
echo /* Main stylesheet */ > assets\css\style.css
echo /* Admin stylesheet */ > assets\css\admin.css
echo /* Responsive design */ > assets\css\responsive.css

:: Create empty JS files
echo // Main JavaScript > assets\js\main.js
echo // Admin JavaScript > assets\js\admin.js

:: Create placeholder index files
echo ^<?php include '../includes/header.php'; ?^> > pages\items\index.php
echo ^<h1^>Items Database^</h1^> >> pages\items\index.php
echo ^<?php include '../includes/footer.php'; ?^> >> pages\items\index.php

echo ^<?php include '../includes/header.php'; ?^> > pages\monsters\index.php
echo ^<h1^>Monsters Database^</h1^> >> pages\monsters\index.php
echo ^<?php include '../includes/footer.php'; ?^> >> pages\monsters\index.php

:: Create main index
echo ^<?php include 'includes/header.php'; ?^> > index.php
echo ^<h1^>Welcome to L1J Database^</h1^> >> index.php
echo ^<?php include 'includes/footer.php'; ?^> >> index.php

:: Create admin index
echo ^<?php include '../includes/admin-header.php'; ?^> > admin\index.php
echo ^<h1^>Admin Dashboard^</h1^> >> admin\index.php
echo ^<?php include '../includes/footer.php'; ?^> >> admin\index.php

echo ^<?php include '../includes/admin-header.php'; ?^> > admin\login.php
echo ^<h1^>Admin Login^</h1^> >> admin\login.php
echo ^<?php include '../includes/footer.php'; ?^> >> admin\login.php

:: Create .htaccess
echo # Secure the admin directory > .htaccess
echo ^<IfModule mod_rewrite.c^> >> .htaccess
echo RewriteEngine On >> .htaccess
echo RewriteRule ^item/([0-9]+)$ pages/items/detail.php?id=$1 [L] >> .htaccess
echo RewriteRule ^monster/([0-9]+)$ pages/monsters/detail.php?id=$1 [L] >> .htaccess
echo ^</IfModule^> >> .htaccess

echo Folder structure created successfully!
echo Now you can start developing your L1J Database Website.
pause