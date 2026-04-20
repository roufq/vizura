# Installation Guide - Vizura POS

Thank you for purchasing Vizura POS! Follow this guide to set up the application on your server.

## 📋 System Requirements
- **PHP**: 8.3 or higher
- **Extensions**: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD
- **Database**: MySQL 8.0+, MariaDB 10.3+, or PostgreSQL
- **Web Server**: Apache or Nginx
- **Tools**: Composer, Node.js & NPM (for local development/re-compiling assets)

---

## 🛠 Step-by-Step Installation

### 1. Upload & Extract
Upload the source code to your web server and extract it into your desired directory (e.g., `/var/www/vizura`).

### 2. Install Dependencies
Open your terminal in the project root and run:
```bash
composer install --optimize-autoloader --no-dev
```

### 3. Environment Configuration
Copy the example environment file:
```bash
cp .env.example .env
```
Open `.env` and configure your database and application details:
```env
APP_NAME="Vizura POS"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations & Seed Data
This will create the database tables and prepopulate required data (roles, permissions, etc.).
```bash
php artisan migrate --seed
```

### 6. Storage Link
Create a symbolic link to make uploaded files accessible:
```bash
php artisan storage:link
```

### 7. Folder Permissions
Ensure the web server has write access to these directories:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 8. Web Server Configuration
Point your web server's **Document Root** to the `/public` directory of the project.

---

## 🔑 Default Login Credentials
After seeding, you can log in with:

| Role | Email | Password |
|------|-------|----------|
| **Owner** | `test@example.com` | `password` |
| **Manager** | `manager@example.com` | `password` |
| **Head Store** | `headstore@example.com` | `password` |
| **Cashier** | `cashier@example.com` | `password` |

> **IMPORTANT**: Change these passwords immediately after your first login in the Profile menu.

---

## 🚀 Optimization (Production Only)
For better performance in production:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---
© 2026 Vizura Project.
