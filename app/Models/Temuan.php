<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Temuan extends Model
{
    protected $table = 'temuans';

    protected $primaryKey = 'Id_Temuan';

    public $timestamps = false;

    protected $fillable = [
        'Id_Temuan',
        'Id_List_report',
        'Id_User',
        'Object_Temuan',
        'Time_Temuan',
        'Time_Penanganan',
        'Tipe_Temuan',
        'Status_Temuan',
    ];

    public function ListReport()
    {
        return $this->belongsTo(List_Report::class, 'Id_List_Report', 'Id_List_Report');
    }

    public function User()
    {
        return $this->belongsTo(User::class, 'Id_User', 'Id_User');
    }

    public static function getIncrementedId()
    {
        $lastTemuan = self::orderBy('Id_Temuan', 'desc')->first();
        if ($lastTemuan) {
            return $lastTemuan->Id_Temuan + 1;
        } else {
            return 1; // Start from 1 if no records exist
        }
    }

    protected $casts = [
        'Status_Temuan' => 'boolean',
        'Time_Temuan' => 'datetime',
        'Time_Penanganan' => 'datetime',
    ];
}
