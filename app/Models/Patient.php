<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'user_id',
        'gender',
        'dob',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }


    public function appointments(){
        return $this->hasMany(Appointment::class);
    }
}
