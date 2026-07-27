<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    public static function get(string $k, $default=null)
    { return static::where('key',$k)->value('value') ?? $default; }

    public static function put(string $k, $v): void
    { static::updateOrCreate(['key'=>$k], ['value'=>$v]); }
}
