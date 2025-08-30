<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function floors()
    {
        return $this->hasMany(Floor::class, 'building_id', 'id');
    }
}
