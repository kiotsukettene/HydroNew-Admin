<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * 
 * User model for the HydroNew Admin application.
 * 
 * The 'status' field is automatically updated based on user activity:
 * - 'active': User is currently online/logged in to the application
 * - 'inactive': User is currently offline/logged out
 * 
 * The 'is_archived' field is used for soft-archiving users without deletion.
 * Archived users are excluded from most analytics and listings.
 * 
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $role
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $profile_picture
 * @property string|null $address
 * @property string $status
 * @property bool $is_archived
 * @property bool|null $first_time_login
 * @property Carbon|null $last_login_at
 * @property string|null $verification_code
 * @property Carbon|null $verification_expires_at
 * @property Carbon|null $last_otp_sent_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Feedback[] $feedback
 * @property Collection|LoginHistory[] $login_histories
 * @property Collection|Notification[] $notifications
 * @property Collection|Device[] $devices
 *
 * @package App\Models
 */
class User extends Authenticatable
{
	use Notifiable;
	
	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime',
		'first_time_login' => 'bool',
		'is_archived' => 'bool',
		'last_login_at' => 'datetime',
		'verification_expires_at' => 'datetime',
		'last_otp_sent_at' => 'datetime'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'first_name',
		'last_name',
		'role',
		'email',
		'email_verified_at',
		'password',
		'profile_picture',
		'address',
		'status',
		'is_archived',
		'first_time_login',
		'last_login_at',
		'verification_code',
		'verification_expires_at',
		'last_otp_sent_at',
		'remember_token'
	];

	public function feedback()
	{
		return $this->hasMany(Feedback::class);
	}

	public function login_histories()
	{
		return $this->hasMany(LoginHistory::class);
	}

	public function notifications()
	{
		return $this->hasMany(Notification::class);
	}

	public function devices()
	{
		return $this->belongsToMany(Device::class, 'device_users', 'user_id', 'device_id');
	}

	/**
	 * Scope to filter only active users
	 */
	public function scopeActive($query)
	{
		return $query->where('status', 'active')->where('is_archived', false);
	}

	/**
	 * Scope to filter only inactive users
	 */
	public function scopeInactive($query)
	{
		return $query->where('status', 'inactive')->where('is_archived', false);
	}

	/**
	 * Scope to filter archived users
	 */
	public function scopeArchived($query)
	{
		return $query->where('is_archived', true);
	}

	/**
	 * Scope to filter non-archived users
	 */
	public function scopeNotArchived($query)
	{
		return $query->where('is_archived', false);
	}

	/**
	 * Scope to filter only regular users (non-admin)
	 */
	public function scopeRegularUsers($query)
	{
		return $query->where('role', 'user');
	}

	/**
	 * Mark user as online/active
	 */
	public function markAsActive()
	{
		$this->update(['status' => 'active', 'last_login_at' => now()]);
	}

	/**
	 * Mark user as offline/inactive
	 */
	public function markAsInactive()
	{
		$this->update(['status' => 'inactive']);
	}

	/**
	 * Archive the user
	 */
	public function archive()
	{
		$this->update(['is_archived' => true]);
	}

	/**
	 * Unarchive the user
	 */
	public function unarchive()
	{
		$this->update(['is_archived' => false]);
	}

	/**
	 * Check if user is currently active/online
	 */
	public function isActive(): bool
	{
		return $this->status === 'active' && !$this->is_archived;
	}

	/**
	 * Check if user is archived
	 */
	public function isArchived(): bool
	{
		return $this->is_archived;
	}
}
