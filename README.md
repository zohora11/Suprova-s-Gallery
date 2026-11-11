# Suprova's Gallery — E-Commerce Website

**A complete, production-style women's fashion e-commerce website built with PHP, MySQL, HTML, CSS & JavaScript. Runs on XAMPP.**

---

## 🚀 Quick Setup (5 Steps)

### Step 1 — Requirements
- XAMPP installed (PHP 8.0+, MySQL 5.7+, Apache)
- Browser (Chrome/Firefox recommended)

### Step 2 — Copy Project Files
Place the `232311064` folder inside:
```
C:\xampp\htdocs\232311064\
```

### Step 3 — Import Database

1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Click **"New"** → Create database named `suprovadb` *(optional — SQL file auto-creates it)*
3. Click **Import** tab
4. Choose file: `232311064/database.sql`
5. Click **Go** → ✅ Import complete

### Step 4 — Run Setup Script

Open in browser:
```
http://localhost/232311064/setup.php
```
This script:
- Creates the `/uploads/products/` folder
- Fixes the admin password hash in the database

### Step 5 — Launch!

```
http://localhost/232311064/
```

---

## 🔐 Admin Access

| Field | Value |
|-------|-------|
| URL | `http://localhost/232311064/admin/` |
| Email | `admin@suprovasgallery.com` |
| Password | `admin123` |

---

## 👤 Sample Customer Account

| Field | Value |
|-------|-------|
| Email | `fatema@example.com` |
| Password | `admin123` |

---

## 📁 Folder Structure

```
232311064/
├── index.php           — Homepage
├── shop.php            — Product listing
├── product.php         — Product detail
├── cart.php            — Shopping cart
├── wishlist.php        — Wishlist
├── checkout.php        — Checkout
├── order_success.php   — Order confirmation
├── orders.php          — My orders
├── profile.php         — User profile
├── login.php           — Login
├── register.php        — Register
├── contact.php         — Contact form
├── logout.php          — Logout
├── setup.php           — One-time setup helper
├── database.sql        — Full SQL database dump
│
├── /config
│   └── db.php          — Database connection & constants
│
├── /includes
│   ├── header.php      — Site header/nav
│   ├── footer.php      — Site footer
│   ├── auth.php        — Session & authentication
│   ├── functions.php   — Helper functions
│   └── product_card.php— Product card component
│
├── /admin
│   ├── index.php       — Dashboard
│   ├── products.php    — Product list
│   ├── product_form.php— Add/edit product
│   ├── categories.php  — Category CRUD
│   ├── orders.php      — Order management
│   └── users.php       — Customer list
│
├── /api
│   ├── cart.php        — Cart AJAX endpoint
│   ├── wishlist.php    — Wishlist AJAX endpoint
│   ├── search.php      — Live search endpoint
│   └── newsletter.php  — Newsletter subscribe
│
├── /assets
│   ├── /css
│   │   ├── style.css   — Main stylesheet
│   │   └── admin.css   — Admin panel CSS
│   ├── /js
│   │   ├── main.js     — Core interactions
│   │   ├── cart.js     — Cart functions
│   │   ├── shop.js     — Shop page
│   │   └── checkout.js — Checkout validation
│   └── /images
│       └── product-placeholder.svg
│
└── /uploads
    └── /products       — Uploaded product images
```

---

## ✨ Features

### Customer
- Browse, search, and filter products
- Add to cart & wishlist
- Checkout with form validation
- View order history
- Edit profile & change password
- Newsletter subscription

### Admin
- Dashboard with revenue, orders, top sellers
- Add/Edit/Delete products with image upload
- Category management
- Order status management (Pending → Delivered → Cancelled)
- Customer monitoring

---

## 🎨 Design

| Token | Value |
|-------|-------|
| Primary | `#FF7EB6` |
| Accent | `#D63384` |
| Background | `#FFF9FB` |
| Heading Font | Playfair Display |
| Body Font | Poppins |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Backend | Core PHP 8.0+ |
| Database | MySQL 5.7+ |
| Server | Apache (XAMPP) |
| Auth | PHP Sessions + remember-me cookies |
| Images | PHP file upload (JPG, PNG, WEBP) |

---

## ❗ Troubleshooting

**"Connection failed"** → Make sure MySQL is running in XAMPP Control Panel.

**Images not showing** → Run `setup.php` to create the uploads folder.

**Admin login fails** → Run `setup.php` to update the password hash.

**404 errors** → Ensure the folder is in `htdocs` and Apache is running.

---

*Built with ❤️ — Suprova's Gallery © 2025*
