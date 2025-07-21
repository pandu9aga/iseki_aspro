<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    protected $table = 'procedures';
    protected $primaryKey = 'Id_Procedure';
    public $timestamps = false;

    protected $fillable = ['Name_Procedure', 'Name_Area', 'Name_Tractor', 'Item_Procedure'];

    public function area()
    {
        return $this->belongsTo(Area::class, 'Name_Area', 'Name_Area');
    }

    public function tractor()
    {
        return $this->belongsTo(Tractor::class, 'Name_Tractor', 'Name_Tractor');
    }
}
