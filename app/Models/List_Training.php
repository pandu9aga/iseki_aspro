<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class List_Training extends Model
{
    protected $table = 'list_trainings';

    protected $primaryKey = 'Id_List_Training';

    public $timestamps = false;

    protected $fillable = [
        'Id_Training',
        'Name_Procedure',
        'Name_Area',
        'Name_Tractor',
        'Item_Procedure',
        'Time_List_Report',
        'Time_Approved_Leader',
        'Time_Approved_Auditor',
        'Reporter_Name',
        'Leader_Name',
        'Auditor_Name',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class, 'Id_Training', 'Id_Training');
    }

    public function getDisplayNameAttribute()
    {
        return preg_replace('/ - Retrain \d+$/', '', $this->Name_Procedure);
    }
}
