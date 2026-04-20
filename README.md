# Vizura POS - Premium Multi-Location Point of Sale System

Vizura POS is a professional, high-performance Point of Sale application built with **Laravel 12**, **Tailwind CSS 4**, and **PHP 8.3**. Designed for scalability and ease of use, it caters to small and medium enterprises (MSMEs) with multi-branch management requirements.

![Vizura POS Banner](https://via.placeholder.com/1200x600?text=Vizura+POS+Premium+Dashboard)

## 🚀 Key Features

### 🏢 Multi-Location Management
- Seamlessly manage multiple branches/outlets.
- Role-based access control (Owner, Manager, Branch Head, Cashier).
- Real-time stock visibility across all locations.

### 💰 Fast POS Terminal
- AJAX-powered product search & manual selection.
- Multi-method payments (Cash, Card, QRIS, Transfer, Credit/Receivables).
- Line-item and order-level discounts.
- Inclusive & Exclusive tax management.
- Draft & Post transaction workflow.
- Automated thermal receipt printing integration.

### 📦 Warehouse & Inventory
- Full SKU and Barcode support.
- Stock adjustments with approval workflows.
- Stock transfers between locations (Inbound/Outbound tracking).
- Automated stock cards for every product.

### 📊 Advanced Reporting
- Real-time Sales, Stock, and Profit/Loss reports.
- Export to CSV capability for all key reports.
- Audit logs for every critical transaction.
- Daily Cash-up reporting for cashiers.

### 🎨 Premium UI/UX
- Developed with **Tailwind CSS v4** for a modern, sleek interface.
- Glassmorphism-inspired components.
- Fully responsive design (Desktop, Tablet, Mobile).
- Dark Mode support ready.

## 🛠 Technology Stack
- **Framework**: Laravel 12 (latest)
- **Engine**: PHP 8.3
- **Styling**: Tailwind CSS 4
- **Database**: MySQL / PostgreSQL
- **Frontend**: Blade + Alpine.js / jQuery

## 📦 Installation
For a detailed step-by-step installation guide, please refer to [INSTALLATION.md](INSTALLATION.md).

Quick start:
1. Clone the repository.
2. Run `composer install` & `npm install`.
3. Configure `.env` with your database credentials.
4. Run `php artisan migrate --seed`.
5. Run `php artisan storage:link`.
6. Access your dashboard at `/login`.

## 🌐 Globalization
- Multi-language support (English & Indonesian).
- Configurable Currency and Date formats.

## 🛡 Security & Performance
- Built-in CSRF protection.
- Optimized database indexing for large-scale reports.
- Soft-deletes for all master data integrity.

---
© 2026 Vizura. Built for Excellence.
