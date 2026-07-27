<?php

use App\Http\Controllers\{AuthController, SiteController, AdminController};
use Illuminate\Support\Facades\Route;

// ── PUBLIC SITE ─────────────────────────────────────────────────
Route::get('/',                 [SiteController::class,'home'])->name('home');
Route::get('/inventory',        [SiteController::class,'inventory'])->name('inventory');
Route::get('/lot/{vehicle}',    [SiteController::class,'lot'])->name('lot');
Route::get('/lot/{vehicle}/status',[SiteController::class,'lotStatus']);   // polled JSON
Route::get('/wins',             [SiteController::class,'wins'])->name('wins');
Route::get('/p/{slug}',         [SiteController::class,'page'])->name('page');
// FIX: public write endpoints were unthrottled and open to spam floods.
Route::post('/contact',         [SiteController::class,'contact'])
    ->middleware('throttle:5,1')->name('contact');
Route::post('/lot/{vehicle}/offer',[SiteController::class,'offer'])
    ->middleware('throttle:5,1')->name('offer');

// ── AUTH ────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class,'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class,'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class,'showRegister'])->name('register');
    Route::post('/register',[AuthController::class,'register'])->middleware('throttle:5,10');
});
Route::post('/logout', [AuthController::class,'logout'])->middleware('auth')->name('logout');

// ── BIDDING (must be signed in) ─────────────────────────────────
Route::middleware('auth')->group(function () {
    // FIX: throttled so a script cannot hammer the bid endpoint.
    Route::post('/lot/{vehicle}/bid',    [SiteController::class,'bid'])
        ->middleware('throttle:30,1')->name('bid');
    Route::post('/lot/{vehicle}/buynow', [SiteController::class,'buyNow'])
        ->middleware('throttle:10,1')->name('buynow');
});

// ── ADMIN ───────────────────────────────────────────────────────
Route::middleware(['auth','admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class,'dashboard'])->name('admin');

    Route::get('/kyc',                 [AdminController::class,'kyc']);
    Route::get('/kyc/{user}',          [AdminController::class,'kycReview']);
    Route::post('/kyc/{user}',         [AdminController::class,'kycDecide']);
    // FIX: identity documents are served here, never from public storage.
    Route::get('/kyc/{user}/doc/{side}',[AdminController::class,'kycDocument'])
        ->whereIn('side',['front','back'])->name('admin.kyc.doc');

    Route::get('/vehicles',            [AdminController::class,'vehicles']);
    Route::get('/vehicles/new',        [AdminController::class,'vehicleForm']);
    Route::post('/vehicles',           [AdminController::class,'vehicleSave']);
    Route::get('/vehicles/{vehicle}',  [AdminController::class,'vehicleForm']);
    Route::post('/vehicles/{vehicle}', [AdminController::class,'vehicleSave']);
    Route::post('/vehicles/{vehicle}/delete',[AdminController::class,'vehicleDelete']);
    Route::post('/vehicles/{vehicle}/live',  [AdminController::class,'goLive']);
    Route::post('/vehicles/{vehicle}/sort',  [AdminController::class,'photoSort']);
    Route::post('/photos/{photo}/delete',    [AdminController::class,'photoDelete']);

    Route::get('/bids',                [AdminController::class,'bids']);
    Route::post('/bids/{bid}/delete',  [AdminController::class,'bidDelete']);

    Route::get('/sales',               [AdminController::class,'sales']);
    Route::post('/sales/{sale}',       [AdminController::class,'saleDecide']);

    Route::get('/leads',               [AdminController::class,'leads']);
    Route::get('/leads/{lead}',        [AdminController::class,'leadShow']);
    Route::post('/leads/{lead}',       [AdminController::class,'leadUpdate']);

    Route::get('/pipeline',            [AdminController::class,'pipeline']);
    Route::post('/pipeline/{lead}',    [AdminController::class,'pipelineMove']);

    Route::get('/users',               [AdminController::class,'users']);
    Route::post('/users/{user}/toggle',[AdminController::class,'userToggle']);
});
