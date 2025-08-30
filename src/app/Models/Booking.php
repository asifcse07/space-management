<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['area_id', 'user_name', 'start_time', 'end_time'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
