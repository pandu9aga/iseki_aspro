<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tractor extends Model
{
    protected $table = 'tractors';
    protected $primaryKey = 'Id_Tractor';
    public $timestamps = false;

    protected $fillable = ['Name_Tractor', 'Photo_Tractor'];

    public function areas()
    {
        return $this->hasMany(Area::class, 'Name_Tractor', 'Name_Tractor');
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class, 'Name_Tractor', 'Name_Tractor');
    }
}
