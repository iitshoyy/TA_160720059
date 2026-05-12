# 🍽️ RestoPOS — Restaurant Point of Sales & Production System

**Full-featured restaurant management system** built with Laravel 10, PHP, and MySQL.

---

## ✅ Features

| Module | Features |
|--------|----------|
| **POS / Sales** | Create orders, dine-in / takeaway / pre-order, payment processing, receipt printing |
| **QR Code Ordering** | Customers scan QR → browse menu → order directly from table |
| **Inventory** | Ingredient stock management, auto-deduct on sale, low-stock alerts |
| **Production** | Recipe components per menu item, stock tracking |
| **Purchase Orders** | Order from suppliers, mark received → auto-updates stock |
| **Reservations** | Online + offline reservations, status management |
| **Table Management** | Table status (available/occupied/reserved), QR code per table |
| **Employees** | Staff management, position tracking |
| **Reports** | Daily/weekly/monthly sales, top items, payment breakdown, CSV export |
| **Dashboard** | Live overview: revenue, orders, low-stock, reservations, table status |

---

## 🚀 Quick Start

### Requirements
- PHP >= 8.1
- Composer
- MySQL 5.7+ / MariaDB 10.3+
- Node.js (optional, for asset compilation)

---

### Step 1 — Clone / Extract the project

```bash
# If cloned from git:
git clone <repo-url> restaurant-pos
cd restaurant-pos

# Or just extract the zip and cd into it
cd restaurant-pos
```

---

### Step 2 — Install PHP dependencies

```bash
composer install
```

---

### Step 3 — Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_DATABASE=restaurant_pos
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

### Step 4 — Create database

In MySQL:
```sql
CREATE DATABASE restaurant_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### Step 5 — Run migrations and seed demo data

```bash
php artisan migrate --seed
```

This creates all tables and inserts:
- Admin, Kasir, Chef user accounts
- 18 menu items across 5 categories
- 8 tables with QR codes
- 18 ingredients with stock levels
- Sample orders for the past 7 days
- Sample reservations and purchase orders

---

### Step 6 — Set storage permissions

```bash
chmod -R 775 storage bootstrap/cache
```

---

### Step 7 — Run the development server

```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

## 🔑 Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Kasir | `kasir` | `kasir123` |
| Chef | `chef` | `chef123` |

---

## 📱 Customer-Facing Pages (No Login Required)

| URL | Description |
|-----|-------------|
| `/order/table/{id}` | QR code ordering page for a specific table |
| `/reserve` | Online reservation form for customers |

To generate and print QR codes for tables:
1. Login as Admin
2. Go to **Table Management**
3. Click **QR Code** button on any table (or **Print all QR codes** for a one-page sheet of every table)
4. Print the QR page and place it on the table

---

## 📶 Letting customers scan the table QR codes (LAN demo)

The QR codes encode `APP_URL`. The default `APP_URL=http://localhost:8000` is **not reachable from a phone**, so the QR won't work until you point it at an address the phone can hit:

1. Find your computer's LAN IP (Windows: `ipconfig` → e.g. `192.168.1.23`).
2. In `.env`, set `APP_URL=http://192.168.1.23:8000` (use your own IP).
3. Run `php artisan optimize:clear`.
4. Start the server bound to all interfaces: `php artisan serve --host=0.0.0.0 --port=8000`.
5. Connect the phone to the **same Wi-Fi network**.
6. In the app, open **Tables → QR** (a single table) or **Tables → Print all QR codes**, then scan with the phone camera. (The QR page shows a warning while `APP_URL` is still `localhost`.)

End-to-end flow: the customer's phone shows the menu → places the order → is taken to a status page telling them to **pay at the cashier** → the kasir opens the order in **Orders**, clicks **Collect Payment**, records the method and amount received → the order moves to **Processing** and the phone's status page updates automatically.

---

## 🗂️ Project Structure

```
app/
├── Http/Controllers/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── OrderController.php
│   ├── MenuController.php        (+ OtherControllers.php)
│   ├── ReservationController.php (+ MoreControllers.php)
│   └── ...
├── Models/
│   ├── User.php, Order.php, Menu.php, Table.php
│   ├── Ingridient.php, Inventory.php, Supplier.php
│   ├── Reservation.php, Employee.php
│   ├── PurchaseOrder.php, Component.php
│   └── ...
database/
├── migrations/   ← Single migration file creates all tables
├── seeders/      ← DatabaseSeeder.php with full demo data
resources/views/
├── layouts/app.blade.php         ← Main admin layout
├── auth/login.blade.php
├── dashboard/index.blade.php
├── orders/{index,create,show,receipt}.blade.php
├── menus/index.blade.php
├── tables/{index,qr}.blade.php
├── inventory/index.blade.php
├── suppliers/index.blade.php
├── purchase-orders/{index,show}.blade.php
├── reservations/index.blade.php
├── employees/index.blade.php
├── reports/index.blade.php
└── customer/{order,reservation}.blade.php
routes/web.php    ← All routes
```

---

## 🔧 Useful Artisan Commands

```bash
# Reset and re-seed database
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear

# View all routes
php artisan route:list
```

---

## 🛠️ Customization

### Adding a new menu category
1. Go to **Menu Items** → the category dropdown is populated from `category_menus` table
2. Add directly to the database or extend `CategoryMenu` controller

### Configuring tax rate
In `OrderController::store()` and `CustomerOrderController::store()`, change `1.11` (11% tax) to your desired rate.

### Adding payment methods
Insert into `payment_types` table and the `payment_type` field in the order form will reflect it.

---

## 📦 Dependencies

- **Laravel 10** — PHP framework
- **simplesoftwareio/simple-qrcode** — QR code generation (optional, SVG fallback included)
- **Font Awesome 6** — Icons (CDN)
- **Google Fonts** — Playfair Display, DM Sans (CDN)

---

## 🐛 Troubleshooting

**Blank page / 500 error:**
```bash
php artisan config:clear
php artisan cache:clear
tail -f storage/logs/laravel.log
```

**Migrations fail:**
- Check DB credentials in `.env`
- Ensure `restaurant_pos` database exists

**Phone can't scan the table QR code:**
- It's almost always `APP_URL`. See *"Letting customers scan the table QR codes (LAN demo)"* above — set `APP_URL` to your machine's LAN IP, run `php artisan optimize:clear`, and serve with `php artisan serve --host=0.0.0.0`.
- The QR page shows a red warning banner whenever `APP_URL` points at `localhost`/`127.0.0.1`.

---

*Built for Vincentius Nathanael Gunawan — Universitas Surabaya, Jurusan Sistem Informasi Bisnis*
