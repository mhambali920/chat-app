<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    public function isAdmin(): bool
    {
        return $this->email === 'mhambali920@gmail.com';
    }

    // Relasi dengan conversation melalui tabel pivot
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('is_admin')
            ->withTimestamps();
    }

    // Relasi dengan pesan-pesan yang dikirim user
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Relasi dengan pesan yang telah dibaca
    public function messageReads()
    {
        return $this->hasMany(MessageRead::class);
    }

    // Relasi dengan kontak yang dimiliki user
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    // Relasi dengan status pesan
    public function messageStatuses()
    {
        return $this->hasMany(MessageStatus::class);
    }

    // Helper untuk mendapatkan kontak yang memblokir atau diblokir
    public function blockedContacts()
    {
        return $this->hasMany(Contact::class)->where('is_blocked', true);
    }
}
