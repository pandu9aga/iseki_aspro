<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $table = 'processes';

    protected $primaryKey = 'Id_Process';

    public $timestamps = false;

    protected $fillable = ['Id_Team', 'Name_Process', 'Time_Created_Process'];

    public function team()
    {
        return $this->belongsTo(Team::class, 'Id_Team', 'Id_Team');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'Id_Process', 'Id_Process');
    }
}
