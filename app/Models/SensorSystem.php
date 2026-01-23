<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SensorSystem
 * 
 * @property int $id
 * @property int $device_id
 * @property string $system_type
 * @property string|null $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Device $device
 * @property Collection|SensorReading[] $sensor_readings
 *
 * @package App\Models
 */
class SensorSystem extends Model
{
	protected $table = 'sensor_systems';

	protected $casts = [
		'device_id' => 'int',
		'is_active' => 'bool'
	];

	protected $fillable = [
		'device_id',
		'system_type',
		'name',
		'is_active'
	];

	public function device()
	{
		return $this->belongsTo(Device::class);
	}

	public function sensor_readings()
	{
		return $this->hasMany(SensorReading::class);
	}
}
