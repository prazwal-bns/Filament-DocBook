<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        'slug',
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


    protected static function booted()
    {
        static::saving(function ($appointment) {
            if ($appointment->appointment_date) {
                $appointment->day = Carbon::parse($appointment->appointment_date)->format('l');
            }
        });

        parent::boot();

        static::creating(function($appointment){
            $patientName = $appointment->patient->user->name;

            $uniqueNo = uniqid();
            $slug = Str::slug("{$uniqueNo}-{$patientName}");

            $appointment->slug = $slug;
            // $appointment->save();

        });
    }

    public function getRouteKeyName(){
        return'slug';
    }

}
