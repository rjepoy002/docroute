<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'is_active',
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
            'department_id' => 'integer',
            'is_active' => 'boolean',
            'profile_completed_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasCompleteProfile(): bool
    {
        return $this->profile_completed_at !== null
            && trim($this->name) !== '' && $this->department_id !== null
            && trim($this->designation ?? '') !== '';
    }

    public static function emailDisplayName(string $email): string
    {
        return Str::title(preg_replace('/[._-]+/', ' ', explode('@', $email)[0]));
    }

    public function getDisplayNameAttribute(): string
    {
        if (! filled($this->first_name) || ! filled($this->last_name)) {
            return $this->getRawOriginal('name') ?? $this->attributes['name'] ?? '';
        }

        return trim($this->first_name).' '.($this->middle_initial ? strtoupper(rtrim(trim($this->middle_initial), '.')).'. ' : '').trim($this->last_name);
    }

    public function getNameAttribute(?string $value): string
    {
        return filled($this->first_name) && filled($this->last_name) ? $this->display_name : ($value ?? '');
    }
}
