<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Class User
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $status
 * @property string $role
 * @property string $password
 * @property string|null $profile_picture
 * @property string|null $address
 * @property bool|null $first_time_login
 * @property Carbon|null $last_login_at
 * @property string|null $verification_code
 * @property Carbon|null $verification_expires_at
 * @property Carbon|null $last_otp_sent_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
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
class User extends Model implements Authenticatable, CanResetPassword, MustVerifyEmail
{
	use HasFactory, AuthenticatableTrait, CanResetPasswordTrait, TwoFactorAuthenticatable;

	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime',
		'first_time_login' => 'bool',
		'last_login_at' => 'datetime',
		'verification_expires_at' => 'datetime',
		'last_otp_sent_at' => 'datetime',
		'two_factor_confirmed_at' => 'datetime',
		'role' => 'string'
	];

	protected $hidden = [
		'password',
		'remember_token',
		'two_factor_secret',
		'two_factor_recovery_codes'
	];

	protected $fillable = [
		'first_name',
		'last_name',
		'name',
		'email',
		'email_verified_at',
		'status',
		'role',
		'password',
		'profile_picture',
		'address',
		'first_time_login',
		'last_login_at',
		'verification_code',
		'verification_expires_at',
		'last_otp_sent_at',
		'two_factor_secret',
		'two_factor_recovery_codes',
		'two_factor_confirmed_at',
		'remember_token'
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
	 * Get the user's full name as a single field
	 */
	public function getNameAttribute(): string
	{
		return "{$this->first_name} {$this->last_name}";
	}

	/**
	 * Set the user's name by splitting into first and last
	 */
	public function setNameAttribute($value): void
	{
		$parts = explode(' ', trim($value), 2);
		$this->attributes['first_name'] = $parts[0];
		$this->attributes['last_name'] = $parts[1] ?? '';
	}

	/**
	 * Check if user is an admin
	 */
	public function isAdmin(): bool
	{
		return $this->role === 'admin';
	}

	/**
	 * Check if user is a regular user
	 */
	public function isUser(): bool
	{
		return $this->role === 'user';
	}

	/**
	 * Determine if the user has verified their email address.
	 */
	public function hasVerifiedEmail(): bool
	{
		return ! is_null($this->email_verified_at);
	}

	/**
	 * Mark the given user's email as verified.
	 */
	public function markEmailAsVerified(): bool
	{
		return $this->forceFill([
			'email_verified_at' => $this->freshTimestamp(),
		])->save();
	}

	/**
	 * Send the email verification notification.
	 */
	public function sendEmailVerificationNotification(): void
	{
		// Implement using Fortify's built-in mechanism
	}

	/**
	 * Get the email address that should be used for verification.
	 */
	public function getEmailForVerification(): string
	{
		return $this->email;
	}

	/**
	 * Send the password reset notification.
	 */
	public function sendPasswordResetNotification($token): void
	{
		$this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
	}

	// ===== Query Scopes for Search, Filter, and Sort =====

	/**
	 * Search users by first name, last name, or email
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param string $search
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeSearch($query, $search = '')
	{
		if (empty($search)) {
			return $query;
		}

		return $query->where(function ($q) use ($search) {
			$q->where('first_name', 'like', "%{$search}%")
				->orWhere('last_name', 'like', "%{$search}%")
				->orWhere('email', 'like', "%{$search}%");
		});
	}

	/**
	 * Filter users by status (active, inactive)
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param string $status
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeFilterByStatus($query, $status = '')
	{
		if (empty($status) || !in_array($status, ['active', 'inactive'])) {
			return $query;
		}

		return $query->where('status', $status);
	}

	/**
	 * Filter users by email verification status
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param string $verified ('yes', 'no', or empty string)
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeFilterByVerified($query, $verified = '')
	{
		if ($verified === 'yes') {
			return $query->whereNotNull('email_verified_at');
		} elseif ($verified === 'no') {
			return $query->whereNull('email_verified_at');
		}

		return $query;
	}

	/**
	 * Filter users by role
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param string $role ('admin', 'user', or empty string)
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeFilterByRole($query, $role = '')
	{
		if (empty($role) || !in_array($role, ['admin', 'user'])) {
			return $query;
		}

		return $query->where('role', $role);
	}

	/**
	 * Sort users by column in specified direction
	 *
	 * Allowed columns: id, first_name, last_name, email, status, role, email_verified_at, created_at, updated_at
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param string $column
	 * @param string $direction ('asc' or 'desc')
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeSort($query, $column = 'created_at', $direction = 'desc')
	{
		$allowedColumns = ['id', 'first_name', 'last_name', 'email', 'status', 'role', 'email_verified_at', 'created_at', 'updated_at'];
		$direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

		if (!in_array($column, $allowedColumns)) {
			$column = 'created_at';
		}

		return $query->orderBy($column, $direction);
	}
}
