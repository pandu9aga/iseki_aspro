<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';

    protected $primaryKey = 'Id_Team';

    public $timestamps = false;

    protected $fillable = ['Id_Team', 'Name_Team'];
}
