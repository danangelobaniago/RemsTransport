# Deployment Guide — REM'S TRANSPORT (Laravel Cloud)

## Stack
- Laravel 10, PHP 8.1+ (use PHP 8.3 on Laravel Cloud)
- MySQL
- Vite (asset build)
- SMTP mail (OTP login / register / password reset)
- PayMongo checkout (redirect flow, no webhook)
- Google Maps JS API (booking pages)
- File uploads: van / tour images, driver licenses (`public` disk)

---

## 1. Push to GitHub
```bash
git init              # (already done)
git add .
git commit -m "Prepare for deployment"
git remote add origin https://github.com/<username>/rems-transport.git
git branch -M main
git push -u origin main
```
Repo should be **private** (contains business logic; `.env` stays ignored).

## 2. Create the app on Laravel Cloud
1. cloud.laravel.com → Sign in with GitHub → new Organization.
2. Create Application → connect the `rems-transport` repo, branch `main`.
3. Environment: `production`. PHP 8.3, Node 20.

## 3. Add MySQL
Database tab → Create Database → MySQL. `DB_*` vars are injected automatically — do NOT set them manually.

## 4. (Optional) Add KV / Redis
If added, set:
```
CACHE_STORE=redis
SESSION_DRIVER=redis
```

## 5. Add Object Storage (for uploads — IMPORTANT)
Laravel Cloud containers have an **ephemeral disk**: uploaded images are wiped on every deploy.
1. Storage tab → create a bucket, make objects publicly readable.
2. It injects `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`,
   `AWS_BUCKET`, `AWS_ENDPOINT`, `AWS_URL`, `AWS_USE_PATH_STYLE_ENDPOINT`.
3. Add one more env var:
   ```
   FILESYSTEM_PUBLIC_DRIVER=s3
   ```
   The `public` disk in `config/filesystems.php` already switches to S3 when this is set.
   Blade views now use `Storage::disk('public')->url(...)`, so image URLs resolve to the bucket.

> Local dev: leave `FILESYSTEM_PUBLIC_DRIVER` unset → keeps using the local `storage/app/public` symlink.

## 6. Environment variables (Environment tab)
Do NOT add `DB_*` (auto). Use the real values from your local `.env`.
```env
APP_NAME="REM'S TRANSPORT"
APP_ENV=production
APP_DEBUG=false
APP_KEY=            # click "Generate" in Laravel Cloud, or: php artisan key:generate --show
APP_URL=https://<your-domain-or-laravel-cloud-url>

LOG_CHANNEL=stack
LOG_LEVEL=error

QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
FILESYSTEM_PUBLIC_DRIVER=s3     # only after Step 5

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=remstransport1@gmail.com
MAIL_PASSWORD=<gmail app password>
MAIL_FROM_ADDRESS=remstransport1@gmail.com
MAIL_FROM_NAME="Rem's Transport"

PAYMONGO_SECRET_KEY=<sk_live_... for real payments, or sk_test_... for testing>
PAYMONGO_PUBLIC_KEY=<pk_live_... / pk_test_...>

GOOGLE_MAPS_KEY=<google maps js api key>
```

## 7. Deploy commands
**Build:**
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```
**Release / post-deploy:**
```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
> `config:cache` is safe now — all runtime `env()` calls were moved into `config/`.
> If you change any env var later in the dashboard, trigger a redeploy so the cache rebuilds.

## 8. First deploy → create an admin
No seeder exists. Open Laravel Cloud's Tinker/console:
```php
\App\Models\User::create([
    'first_name' => 'Admin',
    'last_name'  => 'User',
    'email'      => 'admin@remstransport.com',
    'password'   => bcrypt('CHANGE-ME-NOW'),
    'role'       => 'admin',
]);
```
Staff login page: `/admin/login`.

## 9. Custom domain
1. Buy domain (Porkbun / Cloudflare Registrar `.com`, or `remstransport.ph` via dot.ph).
2. Laravel Cloud → Domains → Add → set the CNAME/A record it shows at your registrar.
3. SSL auto-provisions. Then set `APP_URL=https://remstransport.com` and redeploy.

## 10. PayMongo
- Update the checkout success/cancel redirect URLs if they are hardcoded — check
  `BookingController@paymongoCheckout` / `TourController` / `JoinerTripController`
  for `success_url` / `cancel_url` (they should use `url()` / `route()` so `APP_URL` drives them).
- Switch to `sk_live_` / `pk_live_` keys only when going live.

---

## Known issues (not deploy blockers — fix later)
- `routes/web.php` line ~173: `Route::post('/paymongo/webhook', [BookingController::class, 'paymongoWebhook'])`
  points to a **method that does not exist**. Payments rely on the redirect flow, so this
  route is dead; remove it or implement the handler (and add `paymongo/webhook` to
  `VerifyCsrfToken::$except` if you implement it).
- `routes/web.php` has many duplicate route definitions (`/my-bookings` defined 5×,
  `/paymongo/checkout` 2×, etc.). Last definition wins; worth cleaning up.
- `.env` currently holds a real Gmail app password and Google Maps key. If this repo
  was ever shared or pushed publicly before, rotate both.
