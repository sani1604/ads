# Digital Agency Client Portal

Laravel 11 · MySQL · Blade · Razorpay · DomPDF

A complete portal for a digital marketing agency:

- Public website (services & pricing)
- Client portal (self-service billing, creatives, leads, reports)
- Admin panel (clients, subscriptions, invoices, support, webhooks)

---

## 1. Features

### Public Website

- Landing page with services and CTAs.
- Services page (Meta Ads, Google Ads, Social Media, SEO).
- Pricing page with packages.
- About & Contact pages (contact form → email).
- Login / Register links integrated.

### Client Portal (`/client`)

- **Dashboard**
  - Wallet balance, leads this month, impressions, spend.
  - 7/30-day performance chart.
  - Pending creatives & recent leads.

- **Subscriptions**
  - View current plan, billing cycle, days remaining.
  - Browse plans per service category.
  - Razorpay checkout for new subscriptions.
  - Cancel plan with reason.

- **Wallet (Ad Spend)**
  - Wallet balance & transaction history.
  - Razorpay recharge (min amount from settings).
  - Quick amounts (₹5k, 10k, etc.).
  - Auto-generate invoice on recharge.

- **Creatives**
  - Gallery of all creatives with status badges.
  - Detail view with preview, files, metadata.
  - Comment threads (feedback) & “changes requested”.
  - Approve / request changes workflow.

- **Leads**
  - Filterable leads table (status, source, quality, date).
  - Lead details: contact, campaign, notes, history.
  - Mark status (new/contacted/qualified/converted/etc.).
  - Hot/Warm/Cold quality tags.
  - CSV export.

- **Reports**
  - Summary stats (impressions, clicks, leads, spend).
  - Chart (leads & spend over time).
  - Platform breakdown (CPL, spend).
  - Daily report table (per campaign).
  - CSV export.

- **Invoices**
  - List by type/status/date range.
  - View detailed invoice.
  - Download PDF (DomPDF).

- **Support**
  - Create tickets (billing, technical, creatives, leads, general).
  - Attach files.
  - View thread & reply.
  - Close/reopen.

- **Profile & Activity**
  - Edit profile, company, address, GST.
  - Change password & upload avatar.
  - View own activity log.

- **Notifications**
  - Topbar dropdown + unread count.
  - Notification center with mark-read / mark-all-read.

---

## 2. Admin Panel (`/admin`)

### Dashboard

- KPIs: total/active clients, active subs, revenue, leads, tickets.
- Revenue chart (12 months).
- Widgets: recent clients, recent payments, pending creatives, recent leads, expiring subscriptions.

### Clients

- List + filters (industry, status, subscription).
- Export CSV.
- Detail:
  - Overview, billing, stats (leads/creatives/revenue).
  - Wallet credit/debit.
  - Impersonate “login as client”.
- Create / edit / delete (soft delete).

### Packages & Services

- Manage packages:
  - Name, category, industry, price, billing cycle/days.
  - Features & deliverables.
  - Max creatives/month, revisions.
  - Featured & active flags.
- Service categories (Meta Ads, Social Media, etc.).
- Industries (Real Estate, Healthcare, etc.).

### Subscriptions

- List by client, package, status, expiring in X days.
- Manual creation (with payment method & discount).
- View usage: creatives used/remaining, leads.
- Tabs: invoices, transactions, creatives, leads.
- Actions:
  - Extend, pause/resume, cancel, change package, manual renew.

### Invoices

- Filter by client, type (subscription/wallet/one-time), status, dates.
- Stats: paid count, overdue, total paid.
- Manual invoice creation (custom line items).
- View/download PDF, mark paid, cancel.
- Generate recurring invoices (cron friendly).

### Transactions

- Filter by client, type, method, status, date.
- Manual transactions (cash/bank/manual; optional wallet credit).
- View details + gateway refs.
- Mark status, process refunds (creates refund transaction).

### Leads (Admin)

- Global leads view with client/source/status filters.
- Import/export CSV.
- Create/edit leads for a client.
- Analytics endpoint to build charts (optional dedicated view).

### Creatives (Admin)

- Global list with filters (client, status, platform, category).
- Bulk approve.
- View full creative (preview, comments).
- Upload/edit creatives on behalf of clients.
- Download individual files or ZIP.

### Support Tickets (Admin)

- Filter by client/status/priority/category/assignee.
- Assign to staff; mark resolved/closed.
- Reply with attachments.
- Internal notes (not visible to client).
- Export CSV & statistics endpoint.

### Settings / Webhooks / Logs

- **Settings** (tabbed):
  - General: name, logo, contact info.
  - Payment: currency, tax, min wallet recharge, Razorpay keys.
  - Invoice: company info, GST/PAN, footer & terms.
  - Email: SMTP settings, send test email.
  - Notifications: toggles for new lead, creative, payment, expiry.
  - Social: links to FB/IG/Twitter/LinkedIn/YT.
  - API: Meta & Google webhook secrets, “enable webhooks”.
- **Webhooks**:
  - Show callback URLs:
    - Meta: `/webhooks/meta`
    - Google: `/webhooks/google`
    - Razorpay: `/webhooks/razorpay`
  - Configure:
    - Meta verify token, access token, page → client mapping.
    - Google customer ID → client mapping.
- **Activity Logs**:
  - Filter by user, type, date range.
  - See details with JSON properties.
  - Export CSV.
  - Clear old (e.g. >90 days).

---

## 3. Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **DB**: MySQL (or MariaDB)
- **Frontend**: Blade, Bootstrap 5, Font Awesome, Chart.js, Select2, Flatpickr
- **Payments**: Razorpay (checkout + webhooks)
- **PDF**: barryvdh/laravel-dompdf
- **Notifications/Emails**: Laravel Mail + custom notification model
- **Queues**: database/redis (recommended for production)
- **Cache**: file/redis

---

## 4. Installation

```bash
git clone <repo-url> agency-portal
cd agency-portal

composer install
cp .env.example .env
php artisan key:generate