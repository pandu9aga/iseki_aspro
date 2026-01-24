<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class List_Report extends Model
{
    protected $table = 'list_reports';
    protected $primaryKey = 'Id_List_Report';
    public $timestamps = false;

    protected $fillable = [
        'Id_Report',
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

    public function report()
    {
        return $this->belongsTo(Report::class, 'Id_Report', 'Id_Report');
    }

    public function Temuans()
    {
        return $this->hasMany(Temuan::class, 'Id_List_Report', 'Id_List_Report');
    }
}
