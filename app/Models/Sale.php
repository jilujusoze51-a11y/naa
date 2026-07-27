<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $guarded = [];
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
