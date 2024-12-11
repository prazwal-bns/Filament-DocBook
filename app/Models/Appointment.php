<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'status',
        'start_time',
        'end_time',
        'day',
        'appointment_reason'
    ];

    public function doctor(){
        return $this->belongsTo(Doctor::class);
    }

    public function patient(){
        return $this->belongsTo(Patient::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class, 'appointment_id', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }


}
