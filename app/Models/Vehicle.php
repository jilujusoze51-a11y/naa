<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $guarded = [];

    protected $casts = [
        'hot'         => 'boolean',
        'ends_at'     => 'datetime',
        'start_bid'   => 'decimal:2',
        'reserve'     => 'decimal:2',
        'buy_now'     => 'decimal:2',
        'current_bid' => 'decimal:2',
    ];

    public function photos() { return $this->hasMany(Photo::class)->orderBy('sort'); }

    /**
     * FIX: this relation used to carry ->orderByDesc('amount'). Relation
     * constraints are applied before anything chained onto them, so every
     * caller that tried to re-order (e.g. by max_amount) was silently
     * overridden. Ordering now belongs to the caller.
     */
    public function bids() { return $this->hasMany(Bid::class); }

    /** Bids in display order — newest first. Use this in views. */
    public function bidsLatest() { return $this->hasMany(Bid::class)->latest('id'); }

    public function sales()  { return $this->hasMany(Sale::class); }
    public function winner() { return $this->belongsTo(User::class, 'winner_id'); }

    public function title(): string
    {
        return trim("{$this->year} {$this->make} {$this->model}");
    }

    public function isLive(): bool
    {
        return $this->status === 'live' && $this->ends_at && $this->ends_at->isFuture();
    }

    public function secondsLeft(): int
    {
        return $this->ends_at
            ? max(0, (int) now()->diffInSeconds($this->ends_at, false))
            : 0;
    }

    /** The bid holding the lot: highest ceiling, earliest submission breaks ties. */
    public function topBid(): ?Bid
    {
        return Bid::where('vehicle_id', $this->id)
            ->whereNotNull('user_id')
            ->orderByDesc('max_amount')
            ->orderBy('id')
            ->first();
    }
}
