<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $profile_picture
 * @property string|null $address
 * @property bool|null $first_time_login
 * @property Carbon|null $last_login_at
 * @property string|null $verification_code
 * @property Carbon|null $verification_expires_at
 * @property Carbon|null $last_otp_sent_at
 * @property string $roles
 * @property string $status
 * @property bool $is_archived
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Device[] $devices
 * @property Collection|LoginHistory[] $login_histories
 * @property Collection|Notification[] $notifications
 *
 * @package App\Models
 */
class User extends Authenticatable
{
	use HasFactory;
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime',
		'first_time_login' => 'bool',
		'last_login_at' => 'datetime',
		'verification_expires_at' => 'datetime',
		'last_otp_sent_at' => 'datetime',
		'is_archived' => 'bool'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'first_name',
		'last_name',
		'email',
		'email_verified_at',
		'password',
		'profile_picture',
		'address',
		'first_time_login',
		'last_login_at',
		'verification_code',
		'verification_expires_at',
		'last_otp_sent_at',
		'remember_token',
		'is_archived'
	];

	protected $appends = [
		'name',
		'status'
	];

	public function devices()
	{
		return $this->hasMany(Device::class);
	}

	public function login_histories()
	{
		return $this->hasMany(LoginHistory::class);
	}

	public function notifications()
	{
		return $this->hasMany(Notification::class);
	}

	/**
	 * Get the user's full name.
	 *
	 * @return string
	 */
	public function getNameAttribute(): string
	{
		return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
	}

	/**
	 * Get the user's status based on last login.
	 * Active: logged in within the last 7 days
	 * Inactive: not logged in for more than 7 days (or never logged in)
	 *
	 * @return string
	 */
	public function getStatusAttribute(): string
	{
		if (!$this->last_login_at) {
			return 'inactive';
		}

		$daysSinceLastLogin = Carbon::now()->diffInDays($this->last_login_at);

		return $daysSinceLastLogin <= 7 ? 'active' : 'inactive';
	}

    public function verified(): string
    {
        return $this->email_verified_at ? 'verified' : 'unverified';
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'] ?? '')) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }
    }
}
