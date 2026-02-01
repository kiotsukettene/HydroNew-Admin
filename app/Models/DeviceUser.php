<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceUser
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $device_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class DeviceUser extends Model
{
	protected $table = 'device_users';

	protected $casts = [
		'user_id' => 'int',
		'device_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'device_id'
	];
}
