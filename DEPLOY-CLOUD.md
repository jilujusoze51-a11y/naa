# Deploying to Laravel Cloud

This repository is ready to deploy. Nothing needs installing on your computer.

---

## 1 · Put this code on GitHub

1. Go to **https://github.com** and sign up (free) if you don't have an account.
2. Click **New repository**. Name it `naa`. Choose **Private**. Click **Create**.
3. On the next screen click **uploading an existing file**.
4. Drag **everything inside this folder** into the browser. Include the files
   starting with a dot (`.env.example`, `.gitignore`) — they matter.
5. Click **Commit changes**.

---

## 2 · Connect Laravel Cloud

1. Go to **https://cloud.laravel.com** and sign in with GitHub.
2. **Create application** → pick your `naa` repository.
3. Choose the **Starter** plan.
4. When it asks for a database, choose one and let Cloud create it.
   Either Postgres or MySQL is fine — both support the row locking the
   bidding engine depends on. **Do not pick SQLite.**
5. Deploy.

---

## 3 · Set the deploy commands

In the Cloud dashboard, find **Deploy commands** and make sure these run:

```
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

For the very first deploy only, add `--seed` so you get an admin account:

```
php artisan migrate --force --seed
```

**Remove `--seed` after the first deploy**, or it will keep re-adding demo data.

---

## 4 · Turn the scheduler on  ← DO NOT SKIP

In the dashboard, open your **App compute cluster** and enable the
**task scheduler**.

This runs `auction:close` every minute. Without it **lots never close** —
no winner is chosen and nothing reaches Sale Approvals. There is no auction
without this step.

Scale to Zero is safe to leave on: a sleeping environment wakes automatically
to run scheduled tasks. Note that because our schedule runs every minute, the
app will wake every minute, so expect little idle saving in practice.

---

## 5 · Sign in

Your site is at the URL Cloud gives you.

| Role | Email | Password |
|---|---|---|
| Admin | `admin@naa.test` | `password` |
| Buyer (verified) | `buyer@naa.test` | `password` |

**Change both passwords immediately.** Admin → Users.

---

## 6 · Before real users — file storage

Cloud containers are rebuilt on every deploy, and **anything on the local disk
is wiped**. That means uploaded vehicle photos and ID documents disappear.

For testing this is fine. Before real registrations, create an S3-compatible
bucket (AWS S3, Cloudflare R2) and set these environment variables in Cloud:

```
PRIVATE_DISK=s3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=...
```

The bucket holding `PRIVATE_DISK` **must not be public**. Identity documents
are served only through the admin-only route; a public bucket would undo that.

---

## Checking it works

In the Cloud dashboard's command runner:

```
php artisan test --filter=AuctionTest
```

Nine tests, all should pass. If any fail, stop and fix before continuing.
