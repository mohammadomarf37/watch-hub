# WatchHub - Premium Watch Store

A full-stack e-commerce platform for selling premium watches. Built with
Flutter 3 (mobile app) + Laravel 13 (backend API) + MySQL.

------------------------------------------------------------------------

## 📱 About

WatchHub is a personal e-commerce project where users can browse,
search, and purchase watches. The platform includes a complete mobile
app for customers and a web-based admin panel for store management.

### Key Features

**Customer App:** - Browse watches by brand, category, or price - Search
and filter products - Add items to cart and wishlist - Secure checkout
process - Order tracking and history - Write and view product reviews -
Guest mode browsing

**Admin Panel:** - Dashboard with sales overview - Product management
(CRUD) - Order management and status updates - User management - Review
moderation - Coupon management - Brand and category management -
Settings management

------------------------------------------------------------------------

## 🛠️ Tech Stack

  Layer                  Technology
  ---------------------- -----------------------------------
  **Frontend**           Flutter 3 (Dart)
  **Backend**            Laravel 13 (PHP)
  **Database**           MySQL
  **State Management**   GetX
  **API Client**         Dio
  **Local Storage**      GetStorage + FlutterSecureStorage
  **Authentication**     Laravel Sanctum

------------------------------------------------------------------------

## 🚀 Quick Start

### Prerequisites

-   PHP 8.1+
-   Composer
-   Node.js 16+
-   Flutter 3.x
-   MySQL 8.0+

### Backend Setup

``` bash
git clone <repository-url>
cd watch-hub/watch_hub_backend

composer install

cp .env.example .env

php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_watchhub
DB_USERNAME=root
DB_PASSWORD=

php artisan migrate:fresh --seed

php artisan serve
```

### Frontend Setup

``` bash
cd watch_hub_frontend

flutter pub get

flutter run
```

## 📁 Project Structure

``` text
watch-hub/
├── watch_hub_backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/
│   │   │   │   └── Admin/
│   │   │   └── Middleware/
│   │   └── Models/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
│       └── api.php
└── watch_hub_frontend/
    ├── lib/
    │   ├── core/
    │   ├── models/
    │   ├── providers/
    │   ├── screens/
    │   └── widgets/
    └── pubspec.yaml
```

## 📊 Database

-   28+ Tables
-   Foreign key relationships
-   Soft Deletes
-   Pagination (15 per page)

### Sample Data

-   1 Admin + 5 Customers
-   10 Brands
-   20 Watches
-   100+ Reviews
-   5 Coupons
-   Sample Orders & Cart Data

**Admin Login**

-   Email: admin@watchhub.com
-   Password: password

## 🔑 API Endpoints

### Public

``` text
POST   /auth/register
POST   /auth/login
POST   /auth/forgot-password
GET    /watches
GET    /watches/featured
GET    /watches/new-arrivals
GET    /brands
GET    /categories
```

### Authenticated

``` text
GET    /profile
GET    /cart
POST   /cart/add
POST   /orders
GET    /wishlist
POST   /wishlist/add
```

### Admin

``` text
POST   /admin/login
GET    /admin/dashboard
GET    /admin/products
POST   /admin/products
PUT    /admin/products/{id}
PUT    /admin/orders/{id}/status
```

> For the complete list of 170+ routes, see `routes/api.php`.

## 📱 Screens

### Customer

-   Splash
-   Login / Register
-   Home
-   Catalog
-   Product Details
-   Search
-   Cart
-   Checkout
-   Orders
-   Order Tracking
-   Wishlist
-   Profile & Settings
-   Address Management
-   Reviews
-   Notifications
-   Support & FAQ

### Admin

-   Login
-   Dashboard
-   Product Management
-   Order Management
-   User Management
-   Review Moderation
-   Coupon Management
-   Brand & Category Management
-   Settings

## 🎨 Design System

-   Primary: Deep Navy (#1A1A2E)
-   Accent: Gold Metallic (#C9A84C)
-   Background: White / Light Gray
-   Typography: Inter
-   Rating: Star-based
-   Stock Status: Color-coded

## 📦 Packages

### Backend

-   Laravel Sanctum
-   Laravel CORS
-   MySQL

### Frontend

-   GetX
-   Dio
-   GetStorage
-   FlutterSecureStorage
-   CachedNetworkImage
-   FlutterRatingBar

## 🤝 Contributing

This is a personal project.


## 👤 Author

A personal project by an independent developer.
