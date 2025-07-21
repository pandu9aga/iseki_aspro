<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'Id_Report';
    public $timestamps = false;

    protected $fillable = ['Id_User', 'Name_Area', 'Name_Tractor', 'Time_Report', 'Reporter_Name', 'Approver_Name'];

    public function user()
    {
        return $this->belongsTo(User::class, 'Id_User', 'Id_User');
    }

    public function list_report()
    {
        return $this->hasMany(List_Report::class, 'Id_Report', 'Id_Report');
    }
}
