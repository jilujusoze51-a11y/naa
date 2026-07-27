<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden  = ['password','remember_token'];
    protected $casts   = ['verified'=>'boolean','is_business'=>'boolean','password'=>'hashed'];

    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function canBid(): bool    { return $this->verified && $this->status === 'active'; }
    public function bids()            { return $this->hasMany(Bid::class); }
}
