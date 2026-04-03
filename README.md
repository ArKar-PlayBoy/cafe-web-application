☕ AI Barista – Cafe Management System

AI Barista is a modern full-stack cafe management platform built with Laravel 12, designed to streamline cafe operations while providing customers with an intelligent drink recommendation experience.

The system combines order management, reservations, inventory tracking, payment processing, and AI-powered drink recommendations into one integrated platform.

🚀 Features
👤 Customer Features

User registration, login, logout

Browse menu by category

Search drinks and food items

Add items to cart with notes

Checkout with multiple payment methods

View order history

Upload payment screenshot (KBZ Pay)

Make table reservations

Profile management

Save payment methods (Stripe)

👨‍💼 Admin Features

Admin dashboard with analytics

Category management (CRUD)

Menu item management with images

Menu Cost Analysis with profit rate calculation

Cost breakdown per menu item

Low profit rate warnings (<20%)

Cafe table management

User management (create/edit/ban)

Order management

Payment verification

Delivery status tracking

Inventory and stock management

Stock alerts (low stock / expiring)

Sales reports and analytics

Permission management

Audit logs

👨‍🍳 Staff Features

View today's orders

Update order status

Verify payments

Kitchen display system

Reservation management

Add stock

Log waste

Adjust inventory

🧠 AI Barista Recommendation System

The system suggests drinks based on:

🌤 Weather Conditions

Weather data is fetched from Open-Meteo API (Yangon).

Weather	Recommendation
Rainy	Hot drinks
Hot (>25°C)	Cold drinks
Cold (<15°C)	Hot drinks
Mild	Specialty drinks
😊 Mood Detection
Mood	Drinks
Tired	Espresso, Americano
Relaxed	Chai Latte, Matcha
Sweet	Mocha, Caramel Macchiato
Energetic	Espresso, Matcha
Sad	Hot Chocolate
Happy	Frappuccino
⏰ Time-Based Suggestions
Time	Drinks
Morning	Latte, Cappuccino
Afternoon	Iced Latte, Cold Brew
Evening	Decaf, Herbal Tea
🏗 Tech Stack
Layer	Technology
Backend	Laravel 12.x
Language	PHP 8.2+
Frontend	Tailwind CSS 4 + Alpine.js
Database	MySQL
Build Tool	Vite
Payments	Stripe SDK
API	Open-Meteo
🏛 System Architecture
app/
 ├── Http/Controllers
 │   ├── Admin
 │   ├── Staff
 │   ├── Customer
 │   └── Auth
 │
 ├── Models
 ├── Services
 ├── Middleware
 ├── Events
 ├── Listeners
 ├── Mail
 ├── Policies
 └── Traits
🔐 Authentication

The application uses Multi-Guard Authentication.

Guard	Purpose	Route
web	Customers	/login
admin	Admin users	/admin/login
staff	Staff users	/staff/login
🛡 Authorization (RBAC)

Role-based access control is implemented with 32+ permissions.

Notable permissions:
- `menu.view_cost` - View cost and profit rate in menu items

Roles

Super Admin

Admin

Staff

Customer

Critical Actions (Require Approval)

Deleting categories

Deleting admins

Permission changes

🗄 Database Overview

Main tables:

users
roles
permissions
orders
order_items
menu_items
categories
carts
reservations
cafe_tables
stock_items
stock_batches
stock_movements
stock_alerts
waste_logs
kitchen_tickets
audit_logs
approval_requests
💳 Payment System

Supported payment methods:

| Method | Verification | Notes |
|---|---|---|
| Stripe | Automatic | Verified through secure Stripe flow and webhook handling |
| Saved Card (Stripe) | Automatic | Enforces payment-method ownership before charging |
| KBZ Pay | Manual | Staff/Admin review uploaded screenshot |
| COD | On delivery | Marked verified after cash collection |

#### KBZ Pay Manual Review Rules
Manual review eligibility is centralized in `Order::canReviewPayment()`.

A payment is reviewable only when all are true:
- Payment method is not COD
- Screenshot exists
- `payment_status` is `pending` or `awaiting_verification`

Terminal / non-reviewable statuses:
- `verified`
- `paid`
- `failed` (rejected; cannot be reviewed again)

### Stripe Webhook & Verification Security
Hardened webhook and verification behavior includes:

- Webhook signature verification is required outside local development.
- Invalid signature/payload is fail-closed (rejected).
- Replay protection binds verification to the expected `order_id` and `user_id` metadata.
- Cancelled/failed orders are not reopened by late webhook events.
- Duplicate webhook events do not create duplicate kitchen tickets.
- Payment status transitions are normalized to avoid inconsistent order states.

📦 Inventory System

The system implements FIFO inventory tracking.

Features:

Stock batch tracking

Expiry monitoring

Waste logging

Low stock alerts (automatic on stock create/update)

Expiring stock alerts

Unit cost tracking (per unit of measure: kg, g, L, ml, pcs)

Profit rate calculation per menu item

### 💰 Cost & Profit Management

The system calculates profit based on ingredient costs.

**Features:**

- Stock item unit costs (per kg, g, L, ml, pcs)
- Recipe-based cost calculation  
- Profit rate percentage per menu item
- Low profit rate warnings (<20%)
- Cost Analysis page for detailed breakdown

**Flow:**

1. Set unit cost in Stock items (e.g., Coffee Beans: $20/kg)
2. Link stock items to menu items via Recipe page
3. System auto-calculates: `Cost = Σ(stock_cost × quantity_needed)`
4. Profit = `Price - Cost`
5. Profit Rate = `(Profit ÷ Price) × 100%`
6. View detailed breakdown in Menu → Cost Analysis

**Validation:**

- Profit only shows when recipe exists AND unit costs are set
- Warning displayed if unit costs not configured
- Low profit rate warning if below 20%

🍳 Kitchen Display System

Order lifecycle:

pending → preparing → ready → completed

Kitchen tickets track food preparation progress.

📅 Reservation System

Flow:

Customer selects date, time, party size

System checks available tables

Staff confirms or rejects reservation

Confirmation email sent

🔒 Security Features

Implemented security protections:

- Multi-guard authentication
- Role-based access control
- CSRF protection
- Rate limiting
- IDOR protection
- Security headers
- Password hashing (bcrypt)
- Audit logging
- Ban system
- Approval workflow

### Payment Security Hardening
Payment security protections now include:

- Saved-card ownership checks before charge or delete operations.
- Forensic retention of failed/blocked payment attempts (`status=cancelled`, `payment_status=failed`, diagnostic note).
- Sanitized error responses (no sensitive internal exception leakage to clients).
- Throttling on payment-sensitive endpoints.
- Screenshot file path hardening (directory traversal protection).
- Multi-guard auth + RBAC gate enforcement for admin/staff review actions.

Security headers:

X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy
Strict-Transport-Security
🔌 API Endpoints
Public API
GET /api/weather
GET /api/recommend
GET /api/drinks
Authenticated API
GET /api/orders
GET /api/orders/{id}
GET /api/menu
GET /api/menu/{id}
⚙️ Installation
1️⃣ Clone Repository
git clone https://github.com/ArKar-PlayBoy/Cafe-web-application
cd Cafe-web-application
2️⃣ Install Dependencies
composer install
npm install
3️⃣ Environment Setup
cp .env.example .env
php artisan key:generate
4️⃣ Configure Database

Update .env:

DB_DATABASE=cafe_db
DB_USERNAME=root
DB_PASSWORD=
5️⃣ Run Migrations
php artisan migrate --seed
6️⃣ Start Development Server
composer run dev
🧪 Running Tests
composer run test

Current status:
- 69 passing
- 1 skipped (Stripe CLI-dependent integration test)

Security-focused suites:
- `tests/Feature/SecurityMinimalPatchTest.php`
- `tests/Feature/PaymentReviewActionsTest.php`
📊 Key Services
Service	Purpose
PaymentService	Stripe payments
PermissionService	RBAC
StockService	FIFO inventory
ReportService	Sales analytics
WeatherService	Weather API
RecommendationService	AI drink suggestions

📜 License

This project is licensed under the MIT License.

👨‍💻 Author

AI Barista Project

Built with ❤️ using Laravel + Tailwind + Alpine.js
