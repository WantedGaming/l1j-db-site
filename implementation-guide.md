# L1J Database Website Implementation Guide

This guide provides detailed instructions on how to set up and deploy the L1J Database Website using XAMPP for local development.

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Installation and Setup](#installation-and-setup)
3. [Database Configuration](#database-configuration)
4. [Website Structure](#website-structure)
5. [Common Tasks](#common-tasks)
6. [Troubleshooting](#troubleshooting)

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP (or equivalent Apache/MySQL/PHP stack)
- Web browser (Chrome, Firefox, Edge, etc.)
- At least 500MB of disk space for website files
- At least 1GB of disk space for the database

## Installation and Setup

### Step 1: Install XAMPP

1. Download XAMPP from the [official website](https://www.apachefriends.org/index.html).
2. Run the installer and follow the installation instructions.
3. Start the Apache and MySQL services from the XAMPP Control Panel.

### Step 2: Create the Website Directory Structure

1. Navigate to the XAMPP `htdocs` directory (typically `C:\xampp\htdocs` on Windows or `/Applications/XAMPP/htdocs` on macOS).
2. Run the provided batch script `create-folders.bat` to create the directory structure.

   Alternatively, you can manually create the folder structure as shown in the [Website Structure](#website-structure) section.

### Step 3: Copy the Source Files

1. Copy all the PHP files, CSS, JavaScript, and other assets to their respective folders in the directory structure.
2. Ensure the file permissions are set correctly:
   - PHP files: 644 (rw-r--r--)
   - Directories: 755 (rwxr-xr-x)

### Step 4: Import the Database

1. Open a web browser and navigate to `http://localhost/phpmyadmin`.
2. Create a new database named `l1j_remastered`.
3. Import the SQL files from the `sql` directory into the newly created database.
   - Start with the main schema file.
   - Then import table structure files.
   - Finally, import any data files.

## Database Configuration

The database connection is configured in the `includes/config.php` file. Update the following constants to match your database setup:

```php
// Database configuration
define('DB_HOST', 'localhost');     // Database host (usually localhost)
define('DB_USER', 'root');          // Database username
define('DB_PASS', '');              // Database password
define('DB_NAME', 'l1j_remastered'); // Database name
```

## Website Structure

The website follows a modular structure with separate sections for the public-facing website and the admin panel:

```
l1j-db-site/
├── admin/              # Admin panel
│   ├── items/          # Items management
│   ├── monsters/       # Monsters management
│   ├── characters/     # Characters management
│   ├── skills/         # Skills management
│   ├── users/          # User management
│   └── index.php       # Admin dashboard
├── assets/             # Static assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   ├── img/            # Images
│   └── fonts/          # Font files
├── includes/           # Common PHP files
│   ├── config.php      # Configuration
│   ├── database.php    # Database connection
│   ├── auth.php        # Authentication
│   ├── functions.php   # Utility functions
│   ├── header.php      # Page header
│   └── footer.php      # Page footer
├── models/             # Data models
│   ├── Item.php        # Item model
│   ├── Monster.php     # Monster model
│   └── Skill.php       # Skill model
├── pages/              # Public pages
│   ├── items/          # Items pages
│   ├── monsters/       # Monsters pages
│   ├── skills/         # Skills pages
│   └── maps/           # Maps pages
└── index.php           # Homepage
```

## Common Tasks

### Adding a New Item

1. **Admin Panel Method:**
   - Log in to the admin panel.
   - Navigate to "Items" → "Add New Item".
   - Fill in the form and save.

2. **Database Method:**
   - Add a record to the appropriate items table (weapon, armor, or etcitem).
   - Ensure the item_id is unique across all item tables.

### Adding a New Monster

1. **Admin Panel Method:**
   - Log in to the admin panel.
   - Navigate to "Monsters" → "Add New Monster".
   - Fill in the form and save.

2. **Database Method:**
   - Add a record to the `npc` table with `impl` containing 'L1Monster'.
   - Add drops to the `droplist` table if needed.
   - Add skills to the `mobskill` table if needed.

### Updating the Website Theme

1. Modify the CSS files in the `assets/css` directory:
   - `style.css` for main styling
   - `admin.css` for admin panel styling
   - `responsive.css` for responsive design

2. The website uses the following color scheme:
   - Text: `#ffffff`
   - Background: `#030303`
   - Primary: `#080808`
   - Secondary: `#0a0a0a`
   - Accent: `#f94b1f`

### Creating a Backup

1. **Admin Panel Method:**
   - Log in to the admin panel.
   - Navigate to "Settings" → "Backup Database".
   - Click the "Create Backup" button.

2. **Manual Method:**
   - Use phpMyAdmin to export the database.
   - Copy the website files from the `htdocs` directory.

## Troubleshooting

### Common Issues

**Database Connection Fails**
- Verify the database connection settings in `includes/config.php`.
- Ensure MySQL is running in XAMPP.
- Check if the database and tables exist with correct names.

**Images Not Displaying**
- Check if the image files exist in the `assets/img` directory.
- Verify the file permissions allow read access.
- Check for correct paths in the HTML/PHP code.

**Admin Login Issues**
- Default admin credentials are:
  - Username: `admin`
  - Password: `password`
- If these don't work, check the `ADMIN_USERNAME` and `ADMIN_PASSWORD` constants in `includes/config.php`.

**Error Messages**
- Enable debugging in `includes/config.php` by setting `DEBUG_MODE` to `true`.
- Check PHP error logs in the XAMPP logs directory.

### Getting Help

If you encounter issues not covered in this guide, you can:
1. Check the XAMPP documentation for server-related issues.
2. Review the L1J Remastered project documentation for database structure questions.
3. Search for specific error messages online.
4. Post questions in the project's GitHub repository issues section.

## Conclusion

This implementation guide should help you set up and manage the L1J Database Website. The modular structure makes it easy to extend and customize according to your specific needs.

Remember to change the default admin credentials in a production environment and to implement proper security measures before deploying to a public server.
