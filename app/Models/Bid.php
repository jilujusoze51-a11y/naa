<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $guarded = [];
    protected $casts = ['is_bot'=>'boolean'];
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
