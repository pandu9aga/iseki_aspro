<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    /**
     * Koneksi database eksternal (iseki_rifa).
     */
    protected $connection = 'rifa';

    /**
     * Nama tabel di database iseki_rifa.
     */
    protected $table = 'employees';

    /**
     * Primary key tabel.
     */
    protected $primaryKey = 'id';

    /**
     * Kolom yang bisa diisi mass assignment.
     */
    protected $fillable = ['nama', 'nik', 'team', 'division_id', 'status'];

    public $timestamps = false;
}
