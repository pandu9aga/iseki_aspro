<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $table = 'trainings';

    protected $primaryKey = 'Id_Training';

    public $timestamps = false;

    protected $fillable = ['Start_Training', 'Name_Training', 'Id_Member'];

    public function getMemberAttribute()
    {
        return \App\Helpers\MemberHelper::findByIdAndDate($this->Id_Member, $this->Start_Training);
    }

    public function list_training()
    {
        return $this->hasMany(List_Training::class, 'Id_Training', 'Id_Training');
    }
}
