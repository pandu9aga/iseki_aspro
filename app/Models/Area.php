<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';
    protected $primaryKey = 'Id_Area';
    public $timestamps = false;

    protected $fillable = ['Name_Area', 'Name_Tractor'];

    public function tractor()
    {
        return $this->belongsTo(Tractor::class, 'Name_Tractor', 'Name_Tractor');
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class, 'Name_Area', 'Name_Area');
    }
}
