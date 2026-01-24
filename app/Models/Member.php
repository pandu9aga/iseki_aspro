<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $connection = 'mysql2';

    protected $table = 'employees';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = ['nik', 'nama', 'team'];
}
