<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function areas()
    {
        return $this->hasMany(Area::class, 'floor_id', 'id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }
}
