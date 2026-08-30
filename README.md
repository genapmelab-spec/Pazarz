# Pazarz

**Platform e-commerce marketplace multi-vendor premium** yang mempertemukan Customer, Seller, dan Admin dalam satu ekosistem transaksi yang aman, cepat, dan dapat dipercaya.

Pazarz bukan sekadar katalog produk — ini adalah infrastruktur perdagangan digital yang menangani seluruh siklus hidup transaksi: dari penemuan produk, pemilihan varian, checkout, pembayaran, pengiriman, hingga purna-jual (review, dispute, retur).

> **Status: Fully Implemented.** Backend Laravel, REST API, Admin Blade Dashboard, Seller Blade Dashboard, dan React Customer Frontend sudah terbangun dan terintegrasi.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Directory Structure](#directory-structure)
- [Features](#features)
- [Getting Started](#getting-started)
- [API Reference](#api-reference)
- [Database Schema](#database-schema)
- [Authentication & Authorization](#authentication--authorization)
- [Business Logic](#business-logic)
- [Frontend Pages](#frontend-pages)
- [Admin Dashboard](#admin-dashboard)
- [Seller Dashboard](#seller-dashboard)
- [Development Workflow](#development-workflow)
- [Testing](#testing)
- [Documentation](#documentation)

---

## Tech Stack

| Layer | Technology | Description |
|---|---|---|
| **Customer Frontend** | React + Vite + TypeScript | SPA untuk customer-facing website |
| **Backend API** | Laravel 13 REST API | RESTful API yang dikonsumsi React frontend |
| **Seller Dashboard** | Laravel Blade | Dashboard untuk seller mengelola toko |
| **Admin Dashboard** | Laravel Blade | Dashboard untuk admin mengelola platform |
| **Database** | MySQL | Relational database untuk seluruh data |
| **State Management** | Zustand | Lightweight state management untuk React |
| **Styling** | Tailwind CSS | Utility-first CSS framework |
| **Icons** | Lucide React | Icon library untuk React frontend |

---

## Architecture

```text
┌─────────────────────────────────────────────────────┐
│                    Customer                          │
│                 React + Vite (SPA)                   │
│                    localhost:5173                     │
└──────────────────────┬──────────────────────────────┘
                       │ REST API calls
                       ▼
┌─────────────────────────────────────────────────────┐
│               Laravel Backend (API)                  │
│                  127.0.0.1:8000                      │
│                                                      │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │
│  │  REST API   │  │  Blade UI   │  │   Services  │ │
│  │  /api/*     │  │  /admin/*   │  │  Business   │ │
│  │             │  │  /seller/*  │  │   Logic     │ │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘ │
│         └────────────────┼────────────────┘         │
│                          ▼                           │
│                    ┌──────────┐                      │
│                    │  MySQL   │                      │
│                    │ Database │                      │
│                    └──────────┘                      │
└─────────────────────────────────────────────────────┘
```

### Key Principles

- **Customer** menggunakan React + Vite sebagai SPA yang terpisah, berkomunikasi via REST API.
- **Seller & Admin** menggunakan Laravel Blade yang di-serve langsung dari backend yang sama.
- **Backend** adalah single source of truth untuk seluruh business logic.
- **React frontend** dan **Laravel backend** berjalan di port terpisah selama development.
- Seller hanya dapat mengakses resource miliknya sendiri. Admin memiliki akses lintas kepemilikan untuk moderasi.
- Data sensitif (price, total, stock, seller_id, user_id) **selalu** divalidasi dan diproses di backend.

---

## Directory Structure

```text
Pazarz/
│
├── backend/                          # Laravel Backend
│   ├── app/
│   │   ├── Exceptions/               # Custom exception classes
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/              # REST API controllers (for React)
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── ProductController.php
│   │   │   │   │   ├── CartController.php
│   │   │   │   │   ├── CheckoutController.php
│   │   │   │   │   ├── OrderController.php
│   │   │   │   │   ├── WishlistController.php
│   │   │   │   │   ├── AddressController.php
│   │   │   │   │   ├── ProfileController.php
│   │   │   │   │   └── ReviewController.php
│   │   │   │   ├── Web/
│   │   │   │   │   ├── Auth/         # Login/Register (Blade)
│   │   │   │   │   ├── Admin/        # Admin Blade controllers
│   │   │   │   │   └── Seller/       # Seller Blade controllers
│   │   │   │   └── Controller.php
│   │   │   └── Middleware/
│   │   │       ├── EnsureUserHasRole.php
│   │   │       └── EnsureUserIsActive.php
│   │   ├── Models/                   # Eloquent models
│   │   ├── Policies/                 # Authorization policies
│   │   ├── Providers/
│   │   └── Services/                 # Business logic services
│   │       ├── CartService.php
│   │       ├── CheckoutService.php
│   │       ├── OrderService.php
│   │       └── ProductService.php
│   ├── database/
│   │   ├── factories/                # Model factories
│   │   ├── migrations/               # Database migrations
│   │   └── seeders/                  # Database seeders
│   ├── resources/views/
│   │   ├── admin/                    # Admin Blade views
│   │   ├── seller/                   # Seller Blade views
│   │   ├── auth/                     # Auth Blade views
│   │   └── layouts/                  # Shared Blade layouts
│   ├── routes/
│   │   ├── api.php                   # API routes (for React)
│   │   └── web.php                   # Web routes (Blade dashboards)
│   └── tests/                        # Laravel tests
│
├── frontend/                         # React Customer Frontend
│   ├── public/
│   │   └── images/                   # Static images
│   ├── src/
│   │   ├── components/
│   │   │   ├── layout/               # Header, Footer, MainLayout
│   │   │   ├── shared/               # ProductCard, etc.
│   │   │   └── ui/                   # Button, Badge, Skeleton, etc.
│   │   ├── features/
│   │   │   ├── auth/                 # Login, Register pages
│   │   │   ├── catalog/              # HomePage, ProductList, ProductDetail, Search
│   │   │   ├── cart/                 # CartPage
│   │   │   ├── checkout/             # CheckoutPage
│   │   │   ├── orders/               # OrderList, OrderDetail
│   │   │   └── profile/              # Profile, Addresses, Wishlist
│   │   ├── lib/                      # API client, utils, constants
│   │   └── store/                    # Zustand stores (auth, cart)
│   └── vite.config.ts
│
├── docs/                             # Project documentation
│   ├── PRD.md                        # Product Requirements Document
│   ├── FEATURES.md                   # Feature inventory (MVP vs Future)
│   ├── USER-FLOW.md                  # Customer/Seller/Admin flows
│   ├── ARCHITECTURE.md               # System architecture
│   ├── DATABASE.md                   # Database schema & conventions
│   ├── API.md                        # REST API specification
│   ├── DESIGN.md                     # Design system (tokens, components)
│   ├── ROUTES.md                     # Route list + page specs
│   ├── IMPLEMENTATION-PLAN.md        # Phased development plan
│   └── DECISIONS.md                  # Architectural decisions
│
└── README.md                         # This file
```

---

## Features

### Customer (React Frontend)

| Feature | Status |
|---|---|
| Landing page with hero, trending, categories | ✅ |
| Product browsing with filter, sort, search | ✅ |
| Product detail with variants, images, reviews | ✅ |
| Shopping cart (multi-seller, grouped by store) | ✅ |
| Checkout with address, shipping, payment | ✅ |
| Order tracking with status timeline | ✅ |
| User authentication (login, register) | ✅ |
| Profile & address management | ✅ |
| Wishlist | ✅ |
| Review & rating (post-purchase) | ✅ |

### Seller Dashboard (Blade)

| Feature | Status |
|---|---|
| Dashboard with sales overview | ✅ |
| Product management (CRUD, variants, images) | ✅ |
| Inventory management per variant | ✅ |
| Order management (confirm, ship, cancel) | ✅ |
| Store settings (profile, description) | ✅ |
| Promotion & coupon management | ✅ |
| Review management & replies | ✅ |

### Admin Dashboard (Blade)

| Feature | Status |
|---|---|
| Platform overview (GMV, orders, users) | ✅ |
| User management (suspend, activate) | ✅ |
| Seller management (approve, reject) | ✅ |
| Product moderation (activate, deactivate) | ✅ |
| Category management (CRUD, hierarchy) | ✅ |
| Order monitoring | ✅ |
| Dispute resolution | ✅ |
| Audit log | ✅ |
| Platform settings | ✅ |

---

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL 8.0+

### Installation

```bash
# Clone repository
git clone <repository-url>
cd Pazarz

# Backend setup
cd backend
cp .env.example .env          # Configure DB credentials
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000

# Frontend setup (new terminal)
cd frontend
npm install
npm run dev
```

### Default Credentials

| Role | Email | Password | Access URL |
|---|---|---|---|
| **Admin** | admin@pazarz.com | password | `http://127.0.0.1:8000/admin` |
| **Seller** | seller1@pazarz.com | password | `http://127.0.0.1:8000/seller` |
| **Customer** | customer@pazarz.com | password | `http://localhost:5173/login` |

### Running the Application

```bash
# Backend (Laravel) — serves Admin/Seller Blade dashboards + REST API
cd backend
php artisan serve --host=127.0.0.1 --port=8000

# Frontend (React) — serves Customer landing page
cd frontend
npm run dev
```

| URL | Purpose |
|---|---|
| `http://localhost:5173/` | Customer landing page (React) |
| `http://127.0.0.1:8000/admin` | Admin dashboard (Blade) |
| `http://127.0.0.1:8000/seller` | Seller dashboard (Blade) |
| `http://127.0.0.1:8000/api/*` | REST API endpoints |

---

## API Reference

### Authentication

```
POST   /api/login              # Customer login, returns token
POST   /api/register           # Customer registration
POST   /api/logout             # Customer logout (requires token)
```

### Products

```
GET    /api/products           # List products (paginated, filterable)
GET    /api/products/{slug}    # Product detail with variants & reviews
GET    /api/categories         # List categories
```

### Cart

```
GET    /api/cart               # Get current cart (grouped by store)
POST   /api/cart               # Add item to cart
PATCH  /api/cart/{item}        # Update cart item quantity
DELETE /api/cart/{item}        # Remove cart item
```

### Checkout & Orders

```
POST   /api/checkout           # Create order from cart
GET    /api/orders             # List customer orders
GET    /api/orders/{id}        # Order detail with sub-orders
POST   /api/orders/{number}/complete  # Confirm order received
```

### Wishlist & Addresses

```
GET    /api/wishlist           # List wishlist items
POST   /api/wishlist           # Add product to wishlist
DELETE /api/wishlist/{id}      # Remove from wishlist
GET    /api/addresses          # List addresses
POST   /api/addresses          # Create address
PUT    /api/addresses/{id}     # Update address
DELETE /api/addresses/{id}     # Delete address
```

### Profile & Reviews

```
GET    /api/profile            # Get user profile
PUT    /api/profile            # Update profile
POST   /api/reviews            # Create review
GET    /api/reviews            # List reviews
```

All authenticated endpoints require `Authorization: Bearer <token>` header.

---

## Database Schema

### Core Entities

```
users ──────────┬─────────────────────── addresses (polymorphic)
                │
                ├─── seller ──── store ──── products ──── product_variants
                │                                    ├─── inventories
                │                                    └─── product_images
                │
                ├─── orders ──── sub_orders ──── order_items
                │                 ├─── shipments ──── shipment_tracking_events
                │                 └─── disputes ──── dispute_messages
                │
                ├─── payments
                ├─── reviews
                ├─── wishlists
                └─── notifications

categories (hierarchical parent-child)
coupons
coupon_usages
audit_logs
platform_settings
```

### Key Relationships

- **Order → SubOrders**: One order splits into multiple sub-orders (one per seller)
- **SubOrder → OrderItems**: Each sub-order contains line items with price/name snapshots
- **Product → Variants**: Products have variants (e.g., Size + Color combinations)
- **Variant → Inventory**: Each variant tracks stock independently
- **Seller → Store → Products**: Seller owns a store which contains products

---

## Authentication & Authorization

### Auth Strategy

| Surface | Auth Method | Token Type |
|---|---|---|
| React Customer | Token-based (Sanctum) | Bearer token |
| Seller Blade | Session-based | Cookie session |
| Admin Blade | Session-based | Cookie session |

### Roles (Spatie Permission)

| Role | Can Access |
|---|---|
| `customer` | React SPA + REST API |
| `seller` | `/seller/*` Blade routes |
| `admin` | `/admin/*` Blade routes |

### Authorization Rules

- **Seller** can only manage their own products, orders, store settings, and inventory.
- **Admin** has cross-ownership access for moderation (approve/reject sellers, suspend users, manage categories, resolve disputes).
- All sensitive admin actions are recorded in `audit_logs`.
- Price, stock, seller_id, and user_id are **never** trusted from frontend — always validated server-side.

---

## Business Logic

### Order Flow

```
Customer Checkout
       │
       ▼
   Order Created (status: pending_payment)
   ├── SubOrder 1 (Store A) → pending
   └── SubOrder 2 (Store B) → pending
       │
       ▼ (Payment Success)
   Order Status: paid
   ├── Stock deducted from inventory
   └── Seller notified
       │
       ▼ (Seller Confirms)
   SubOrder Status: confirmed → processing
       │
       ▼ (Seller Ships)
   SubOrder Status: shipped
   └── Tracking number recorded
       │
       ▼ (Customer Confirms Receipt)
   SubOrder Status: completed
   └── Order Status: completed (if all sub-orders completed)
```

### Multi-Vendor Order Splitting

When a customer checks out items from multiple sellers:

1. One `order` is created with a single payment (grand total).
2. The order is split into `sub_orders` — one per seller/store.
3. Each sub-order has its own shipping cost, status, and processing lifecycle.
4. Stock is reserved per variant at checkout, deducted on payment success.
5. If one seller cancels, only that sub-order is affected — the order continues.

### Stock Management

- **Reserve** at checkout (prevents oversell)
- **Deduct** on payment success
- **Release** on payment failure or seller cancellation
- Each variant tracks: `quantity`, `reserved`, `low_stock_threshold`

---

## Frontend Pages

### Customer React App (`localhost:5173`)

| Route | Page | Description |
|---|---|---|
| `/` | HomePage | Hero, categories, trending, featured products |
| `/products` | ProductListPage | Browse all products with filters |
| `/products/:slug` | ProductDetailPage | Product detail with variants, reviews |
| `/categories` | CategoryPage | Browse by category |
| `/search` | SearchPage | Search results |
| `/cart` | CartPage | Shopping cart |
| `/checkout` | CheckoutPage | Address, shipping, payment |
| `/login` | LoginPage | Customer login |
| `/register` | RegisterPage | Customer registration |
| `/account/orders` | OrderListPage | Order history |
| `/account/orders/:id` | OrderDetailPage | Order detail with timeline |
| `/account/profile` | ProfilePage | Edit profile |
| `/account/addresses` | AddressesPage | Manage addresses |
| `/account/wishlist` | WishlistPage | Saved products |

---

## Admin Dashboard

### Routes (`127.0.0.1:8000/admin`)

| Route | Page | Description |
|---|---|---|
| `/admin` | Dashboard | Platform overview (GMV, orders, users, sellers) |
| `/admin/users` | Users | Manage users (suspend/activate) |
| `/admin/sellers` | Sellers | Approve/reject seller applications |
| `/admin/products` | Products | Moderate products (activate/deactivate) |
| `/admin/categories` | Categories | CRUD categories with hierarchy |
| `/admin/orders` | Orders | Monitor all orders |
| `/admin/disputes` | Disputes | Resolve customer disputes |
| `/admin/audit-logs` | Audit Logs | View admin action history |
| `/admin/settings` | Settings | Platform configuration |

---

## Seller Dashboard

### Routes (`127.0.0.1:8000/seller`)

| Route | Page | Description |
|---|---|---|
| `/seller` | Dashboard | Store overview & recent orders |
| `/seller/products` | Products | Manage products list |
| `/seller/products/create` | Add Product | Create new product with variants |
| `/seller/products/:id/edit` | Edit Product | Update product details |
| `/seller/inventory` | Inventory | Manage stock per variant |
| `/seller/orders` | Orders | View & process sub-orders |
| `/seller/orders/:id` | Order Detail | Confirm, ship, or cancel |
| `/seller/settings` | Store Settings | Store profile & configuration |
| `/seller/promotions` | Promotions | Create & manage promotions |
| `/seller/reviews` | Reviews | View & reply to reviews |

---

## Development Workflow

### Backend (Laravel)

```bash
cd backend

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed

# Run tests
php artisan test

# Start development server
php artisan serve --host=127.0.0.1 --port=8000
```

### Frontend (React)

```bash
cd frontend

# Install dependencies
npm install

# Start development server (with HMR)
npm run dev

# Build for production
npm run build

# Type check
npx tsc --noEmit
```

### Backend API Routes

```bash
cd backend
php artisan route:list
```

---

## Testing

```bash
cd backend

# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Test Coverage

- **Authentication**: Login, register, logout, token management
- **Products**: CRUD, filtering, pagination
- **Cart**: Add, update, remove items
- **Checkout**: Order creation, stock validation
- **Orders**: Status transitions, sub-order lifecycle
- **Authorization**: Role-based access control
- **Business Logic**: Price calculation, stock management

---

## Documentation

| Document | Description |
|---|---|
| [PRD.md](docs/PRD.md) | Product Requirements — why Pazarz exists, who it's for, scope |
| [FEATURES.md](docs/FEATURES.md) | Feature inventory — granular list per domain, MVP vs Future |
| [USER-FLOW.md](docs/USER-FLOW.md) | User flows — how each role navigates core processes |
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | System architecture — backend, frontend, database, auth |
| [DATABASE.md](docs/DATABASE.md) | Database schema — entities, relationships, conventions |
| [API.md](docs/API.md) | REST API specification — endpoints, request/response format |
| [DESIGN.md](docs/DESIGN.md) | Design system — colors, typography, spacing, components |
| [ROUTES.md](docs/ROUTES.md) | Route list — per surface + page specs + design references |
| [IMPLEMENTATION-PLAN.md](docs/IMPLEMENTATION-PLAN.md) | Development plan — phased implementation |
| [DECISIONS.md](docs/DECISIONS.md) | Architectural decisions — key choices & open assumptions |

### Reading Order (for new contributors)

1. `docs/PRD.md` — understand the product vision
2. `docs/FEATURES.md` — see what's built
3. `docs/ARCHITECTURE.md` — understand the system design
4. `docs/DATABASE.md` — learn the data model
5. `docs/API.md` — understand the API contract
6. `docs/DESIGN.md` — learn the design system

---

## License

This project is proprietary software. All rights reserved.
