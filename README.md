# Ride4Study

**Ride4Study** is a carpooling platform built for students. It connects students traveling between cities for classes, letting them offer or request rides, split costs, and reduce their carbon footprint together.

Built as a final project for DAW (Web Application Development), it goes beyond a basic CRUD — it's a full-featured SaaS-style application with payments, real-time chat, moderation, and more.

---

## Features

### Core

- **Ride Publishing** — Post rides as "I offer" or "I'm looking for". Includes origin/destination with locality database, departure/return times, available seats, and pricing.
- **Booking System** — Request seats on offered rides or offer to drive someone who's looking. Accept, reject, or cancel reservations with full status tracking.
- **Advanced Search & Filters** — Filter by origin, destination, date, type, max price, minimum seats, verified users only. Multiple sort options with AJAX pagination.
- **Real-time Chat** — AJAX-based messaging between users. Edit/delete messages, load older messages, offer rides directly from chat. Conversation management with full delete support.
- **Rating System** — Rate users after completed trips (1-5 stars) with category breakdowns (punctuality, communication, vehicle, driving, behavior). Includes comments, replies, and a 24h-to-30-day rating window.

### Maps & Routes

- **Interactive Maps** — Leaflet.js with CartoDB dark tiles for route visualization.
- **Route Calculation** — OpenRouteService API for polyline generation, distance (km), and duration (min) estimation.
- **Route Preview** — Map preview on ride creation form and detail modals.

### Sustainability

- **CO2 Tracker** — Automatic CO2 savings calculation per completed trip using Haversine distance formula (distance x 1.3 road factor x 0.12 kg CO2/km).
- **CO2 Ranking** — Community leaderboard showing top contributors with tree equivalence stats and personal position.

### Premium (Stripe)

- **Stripe Checkout** — Secure payment integration (EUR) for 30-day premium subscriptions.
- **Premium Benefits** — Unlimited ride posts, featured/highlighted rides, premium badge on profile.
- **Auto-expiration** — Cron job warns 3 days before expiry and auto-deactivates expired subscriptions.

### Student Verification

- **Document Upload** — Students submit institutional documentation (PDF/images, max 5MB) for verification.
- **Admin Review** — Admins approve or reject verifications. Verified badge displayed on profiles and ride cards.

### Notifications

- **In-app Notifications** — Real-time notification bell with unread count, mark as read, and deep links.
- **Email Notifications** — Transactional emails via Brevo API for: new messages, reservation updates, trip reminders (1 day before), rating requests (24-48h after trip), password changes, premium expiration, and account deletion.
- **User Preference** — Opt-in/opt-out toggle for email notifications.

### Moderation & Admin

- **Report System** — Users can report other users, rides, or chat messages with categorized reasons (spam, offensive, impersonation, fraud, etc.). Duplicate prevention included.
- **Admin Panel** — Full dashboard with:
  - User management (roles, bans with reason/expiry, CSV export)
  - Student verification review
  - Report resolution with actions (warn user, delete content)
  - Institution management (CRUD)
  - Ride/ad oversight and deletion
  - Premium subscription management (grant/revoke)
  - Admin profile and password settings
- **Admin 2FA** — Two-factor authentication via email codes (6-digit, 10-minute expiry) required on every admin login.

### Security

- **Password Hashing** — `PASSWORD_DEFAULT` (bcrypt).
- **SQL Injection Prevention** — PDO prepared statements everywhere.
- **XSS Prevention** — `htmlspecialchars()` on all user-generated output.
- **Flash Messages** — Success/error messages via `$_SESSION` instead of URL parameters to prevent UI manipulation.
- **POST for State Changes** — All state-changing operations (reserve, delete, etc.) use POST, not GET.
- **Authorization Checks** — Every endpoint validates resource ownership (conversation membership, ride ownership, trip participation).
- **Secure Cookies** — `httponly`, `SameSite=Lax`.
- **GDPR Compliance** — Privacy policy, terms of service, cookie consent banner, account deletion.

### Internationalization

- **Bilingual** — Full Spanish and English support with 400+ translation keys.
- **Language Switcher** — Cookie-based persistence (1-year expiry) with automatic fallback to Spanish.

---

## Tech Stack

| Layer | Technologies |
|-------|-------------|
| **Backend** | PHP 8 · PDO · Custom MVC Router |
| **Frontend** | HTML5 · TailwindCSS · JavaScript (ES6+) · Font Awesome |
| **Database** | MySQL 10.4+ |
| **Maps** | Leaflet.js · OpenRouteService API · CartoDB Tiles |
| **Payments** | Stripe Checkout API |
| **Email** | Brevo (Sendinblue) Transactional API |
| **Infrastructure** | XAMPP (dev) · InfinityFree (prod) · Git/GitHub |

---

## Project Structure

```
Ride4Study/
├── app/
│   ├── controllers/         # 8 user controllers + 6 admin controllers
│   ├── models/              # User, Ride, Message, Rating, Report, Notification, Conversation, Institution
│   ├── Router.php           # Custom URL router
│   └── helpers.php          # URL helpers, translations, flash messages
├── config/
│   ├── database.php         # PDO connection
│   ├── env.php              # .env loader
│   └── lang/                # es.php, en.php (400+ keys each)
├── services/
│   ├── MailService.php      # Brevo API wrapper
│   ├── StripeService.php    # Stripe Checkout wrapper
│   └── RatingNotificationService.php
├── scripts/                 # Cron jobs (CO2, reminders, premium expiry, ratings)
├── views/
│   ├── user/                # Dashboard, profile, chat, publish, my-rides, rating, premium, ranking
│   ├── admin/               # Dashboard, users, reports, instituciones, ads, premium, profile
│   ├── auth/                # Login, register, reset password, admin 2FA
│   ├── public/              # Landing, privacy, terms, cookies, safety, support
│   └── layouts/             # Header, footer
├── public/
│   ├── img/                 # Logos, icons
│   └── uploads/             # Profile photos, verification documents
├── bbdd/                    # SQL dumps and migrations
├── routes.php               # All route definitions
└── .env                     # API keys (Stripe, Brevo, ORS)
```

---

## Setup

1. **Clone** the repository into your XAMPP `htdocs/` folder
2. **Import** the database from `bbdd/if0_40294789_ride4study.sql` into phpMyAdmin
3. **Configure** `config/database.php` with your MySQL credentials
4. **Create** `.env` in the project root with:
   ```
   BREVO_API_KEY=your_brevo_key
   STRIPE_SECRET_KEY=your_stripe_secret
   STRIPE_PUBLISHABLE_KEY=your_stripe_publishable
   ORS_API_KEY=your_openrouteservice_key
   ```
5. **Access** `http://localhost/Ride4Study/`

---

## Automated Tasks

The application uses a pseudo-cron system (scripts execute on dashboard load):

| Script | Purpose |
|--------|---------|
| `cron_update_co2.php` | Recalculates CO2 savings for all users |
| `cron_trip_reminders.php` | Sends reminders 1 day before departure |
| `cron_send_rating_notifications.php` | Sends rating requests 24-48h after trip |
| `cron_premium_expiration.php` | Warns before expiry + auto-deactivates |

---

## Author

**Antonio Jesus Gonzalez Domingo**
- Web Application Development Student (2 DAW) — Huelva, Spain
- Portfolio: [antoniojesusportfolio.netlify.app](https://antoniojesusportfolio.netlify.app)
- Email: antoniojesusgonzalezdomingo4@gmail.com
