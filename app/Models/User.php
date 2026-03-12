<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
        'xp',
        'daily_streak',
        'last_practiced_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'last_practiced_at' => 'datetime',
        ];
    }

    public function addXp(int $amount = 10): void
    {
        $this->xp = max(0, ($this->xp ?? 0) + $amount);
        $this->save();
    }

    public function updatePracticeStreak(): void
    {
        $now = now();
        $threshold = $now->copy()->subHours(24);
        $last = $this->last_practiced_at ? $this->last_practiced_at : null;

        if (! $last) {
            $this->daily_streak = 1;
        } else {
            // If last practiced more than 24 hours ago, reset streak
            if ($last->lt($threshold)) {
                $this->daily_streak = 1;
            } else {
                // Only increment once per day
                if (! $last->isSameDay($now)) {
                    $this->daily_streak = ($this->daily_streak ?? 0) + 1;
                }
            }
        }

        $this->last_practiced_at = $now;
        $this->save();
    }

    /**
     * Added minimal role support: `admin`, `teacher`, `student` via `role` column.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
