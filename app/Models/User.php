<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements HasAvatar
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'address',
        'phone'
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_url) {
            return $this->avatar_url;
        }

        $roleAvatars = [
            'admin' => 'https://cdn-icons-png.freepik.com/512/3281/3281355.png',
            'patient' => 'https://cdn-icons-png.freepik.com/512/3135/3135768.png',
            'doctor' => 'https://cdn-icons-png.freepik.com/512/921/921059.png',
        ];

        return $roleAvatars[$this->role] ?? 'https://source.boringavatars.com/beam/120/' . urlencode($this->name);
    }




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function patient(){
        return $this->hasOne(Patient::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }



    // protected static function booted(): void
    // {
    //     static::creating(function ($user) {
    //         if (empty($user->role)) {
    //             $user->role = 'patient';
    //         }

    //         if (!empty($user->password)) {
    //             $user->password = Hash::make($user->password);
    //         }
    //     });

    //     static::created(function ($user) {
    //         if ($user->role === 'patient') {
    //             Patient::create([
    //                 'user_id' => $user->id,
    //                 'gender' => $user->gender ?? null,
    //                 'dob' => $user->dob ?? null,
    //             ]);
    //         }
    //     });
    // }

}
