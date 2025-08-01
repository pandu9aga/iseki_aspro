<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'Id_Report';
    public $timestamps = false;

    protected $fillable = ['Id_Process', 'Id_Member', 'Time_Created_Report'];

    public function member()
    {
        return $this->belongsTo(Member::class, 'Id_Member', 'Id_Member');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'Id_Process', 'Id_Process');
    }

    public function list_report()
    {
        return $this->hasMany(List_Report::class, 'Id_Report', 'Id_Report');
    }
}
