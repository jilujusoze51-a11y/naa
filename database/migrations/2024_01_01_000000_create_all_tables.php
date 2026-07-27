<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── USERS ───────────────────────────────────────────────
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('phone')->nullable();
            $t->string('city')->nullable();
            $t->string('company')->nullable();
            $t->string('password');
            $t->string('role')->default('bidder');      // bidder | admin
            $t->boolean('is_business')->default(false);
            $t->boolean('verified')->default(false);     // KYC passed → can bid
            $t->string('status')->default('pending');    // pending | active | deactivated
            $t->string('source')->nullable();
            $t->string('doc_front')->nullable();
            $t->string('doc_back')->nullable();
            $t->text('admin_notes')->nullable();
            $t->string('decision')->nullable();          // why approved/rejected
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });

        // ── VEHICLES ────────────────────────────────────────────
        Schema::create('vehicles', function (Blueprint $t) {
            $t->id();
            $t->string('lot')->index();
            $t->integer('year')->nullable();
            $t->string('make')->nullable();
            $t->string('model')->nullable();
            $t->string('trim')->nullable();
            $t->string('vin')->nullable()->index();
            $t->integer('miles')->default(0);
            $t->string('title_status')->default('Clean');
            $t->string('location')->nullable();
            $t->string('engine')->nullable();
            $t->string('transmission')->nullable();
            $t->string('color')->nullable();
            $t->decimal('start_bid', 12, 2)->default(0);
            $t->decimal('reserve', 12, 2)->default(0);
            $t->decimal('buy_now', 12, 2)->default(0);
            $t->decimal('current_bid', 12, 2)->default(0);
            $t->string('status')->default('draft');   // draft | upcoming | live | sold | unsold
            $t->boolean('hot')->default(false);
            $t->string('agent')->nullable();
            $t->foreignId('winner_id')->nullable();
            $t->timestamp('ends_at')->nullable();     // when the live lot closes
            $t->timestamps();

            // FIX: the scheduler scans this pair every minute.
            $t->index(['status','ends_at'], 'vehicles_status_ends_idx');
        });

        // ── PHOTOS ──────────────────────────────────────────────
        Schema::create('photos', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->integer('sort')->default(0);
            $t->timestamps();
        });

        // ── BIDS ────────────────────────────────────────────────
        Schema::create('bids', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('bidder_name');
            $t->decimal('amount', 12, 2);
            $t->decimal('max_amount', 12, 2)->nullable();  // proxy ceiling
            $t->boolean('is_bot')->default(false);
            $t->timestamps();

            // FIX: the leader lookup runs on every single bid and had no
            // supporting index — it was a full scan of the bids table.
            $t->index(['vehicle_id','max_amount','id'], 'bids_leader_idx');
            $t->index(['created_at'], 'bids_created_idx');
        });

        // ── SALES (buy now + won lots awaiting approval) ─────────
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('kind');                       // bid | buynow | offer
            $t->decimal('amount', 12, 2);
            $t->string('status')->default('pending'); // pending | sold | unsold | rejected
            $t->string('decision')->nullable();
            $t->timestamps();

            // FIX: the database now refuses to record the same car sold twice,
            // so a race between buyNow() and the scheduler cannot double-sell.
            $t->unique(['vehicle_id','kind'], 'sales_vehicle_kind_unique');
        });

        // ── LEADS (contact form + offers) ────────────────────────
        Schema::create('leads', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('subject')->default('Contact'); // Contact | Make Offer
            $t->foreignId('vehicle_id')->nullable();
            $t->decimal('offer', 12, 2)->nullable();
            $t->text('message')->nullable();
            $t->string('stage')->default('new');       // new|na|call|int|paper|inv|paid
            $t->string('lead_status')->nullable();     // won|lost|aband
            $t->string('agent')->nullable();
            $t->boolean('read')->default(false);
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        // ── CACHE ───────────────────────────────────────────────
        // FIX: SETUP.md instructed deleting Laravel's cache migration, but the
        // default .env ships CACHE_STORE=database. Without these tables any
        // cache or rate-limiter call throws. Recreated here so the stock
        // configuration works untouched.
        Schema::create('cache', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->mediumText('value');
            $t->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->string('owner');
            $t->integer('expiration');
        });

        // ── SETTINGS ────────────────────────────────────────────
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings','cache_locks','cache','leads','sales','bids','photos','vehicles','sessions','users'] as $x) {
            Schema::dropIfExists($x);
        }
    }
};
