<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id', 'id');
    }
}
