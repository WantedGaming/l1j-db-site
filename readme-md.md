# L1J Database Website

A comprehensive web-based database for L1J Remastered MMORPG. Browse items, monsters, skills, and more with an easy-to-use interface and admin panel.

## Features

- **Public Database Explorer**
  - Item Database (Weapons, Armor, EtcItems)
  - Monster Database
  - Skills Database
  - Maps Explorer
  - Search Functionality

- **Admin Panel**
  - Dashboard with Statistics
  - Item Management (Add, Edit, Delete)
  - Monster Management
  - Character Management
  - Skills Management
  - User Management
  - Database Backup

## Screenshots

![Homepage](screenshot-home.jpg)
![Item Database](screenshot-items.jpg)
![Admin Dashboard](screenshot-admin.jpg)

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server (or equivalent)
- XAMPP or similar local development stack (for development)

## Installation

### Using XAMPP (Local Development)

1. **Install XAMPP**:
   - Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Start Apache and MySQL services

2. **Clone or Download Repository**:
   ```bash
   git clone https://github.com/your-username/l1j-database-website.git
   ```
   Or download and extract the ZIP file to your XAMPP htdocs folder (e.g., `C:\xampp\htdocs\l1j-db-site`)

3. **Run the Setup Script**:
   - Navigate to `http://localhost/l1j-db-site/install/install.php` in your web browser
   - Follow the on-screen instructions to set up the database and admin account

4. **Alternative Manual Setup**:
   - Import the SQL files from the `sql` directory into your MySQL database
   - Update database credentials in `includes/config.php`
   - Set proper file permissions (755 for directories, 644 for files)

### Production Deployment

1. **Upload Files**:
   - Upload all files to your web server
   - Ensure proper file permissions

2. **Database Setup**:
   - Create a MySQL database
   - Import the SQL files from the `sql` directory

3. **Configuration**:
   - Update database credentials in `includes/config.php`
   - Update site URL and other settings as needed

4. **Security Measures**:
   - Change default admin password
   - Configure proper security settings for your server
   - Set up HTTPS

## Usage

### Public Interface

Visit the website to browse items, monsters, skills, and other game data:
- Home Page: `http://your-domain.com/`
- Items: `http://your-domain.com/pages/items/`
- Monsters: `http://your-domain.com/pages/monsters/`
- Skills: `http://your-domain.com/pages/skills/`

### Admin Panel

Access the admin panel to manage website content:
- Admin Dashboard: `http://your-domain.com/admin/`
- Default login:
  - Username: `admin`
  - Password: `password` (change this immediately after installation)

## Customization

### Theme

The website uses a sleek dark theme with the following color scheme:
- Text: `#ffffff`
- Background: `#030303`
- Primary: `#080808`
- Secondary: `#0a0a0a`
- Accent: `#f94b1f`

You can customize the theme by modifying the CSS files in the `assets/css` directory.

### Adding Content

Use the admin panel to add new items, monsters, and other content to the database.

## Directory Structure

```
l1j-db-site/
├── admin/              # Admin panel
├── assets/             # Static assets (CSS, JS, images)
├── includes/           # Common PHP files
├── models/             # Data models
├── pages/              # Public pages
├── install/            # Installation files
├── sql/                # SQL database files
├── index.php           # Homepage
└── README.md           # Documentation
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgements

- [L1J Remastered](https://github.com/L1J-Remastered) for the original game data
- [Font Awesome](https://fontawesome.com) for icons
- All contributors who have helped with the development of this project
