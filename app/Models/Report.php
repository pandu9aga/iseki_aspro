<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $primaryKey = 'Id_Report';
    public $timestamps = false;

    protected $fillable = ['Start_Report', 'Name_Report', 'Id_Member'];

    public function member()
    {
        return $this->belongsTo(Member::class, 'Id_Member', 'id');
    }

    public function list_report()
    {
        return $this->hasMany(List_Report::class, 'Id_Report', 'Id_Report');
    }
}
