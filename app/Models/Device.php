<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Device
 * 
 * @property int $id
 * @property string $device_name
 * @property string $serial_number
 * @property string|null $model
 * @property string|null $firmware_version
 * @property string|null $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $is_archived
 * 
 * @property Collection|Feedback[] $feedback
 * @property Collection|HydroponicSetup[] $hydroponic_setups
 * @property Collection|Notification[] $notifications
 * @property Collection|SensorSystem[] $sensor_systems
 * @property Collection|Sensor[] $sensors
 * @property Collection|TreatmentReport[] $treatment_reports
 *
 * @package App\Models
 */
class Device extends Model
{
	use HasFactory;
	
	protected $table = 'devices';

	protected $casts = [
		'is_archived' => 'bool'
	];

	protected $fillable = [
		'device_name',
		'serial_number',
		'model',
		'firmware_version',
		'status',
		'is_archived'
	];

	public function feedback()
	{
		return $this->hasMany(Feedback::class);
	}

	public function hydroponic_setups()
	{
		return $this->hasMany(HydroponicSetup::class);
	}

	public function notifications()
	{
		return $this->hasMany(Notification::class);
	}

	public function sensor_systems()
	{
		return $this->hasMany(SensorSystem::class);
	}

	public function sensors()
	{
		return $this->hasMany(Sensor::class);
	}

	public function treatment_reports()
	{
		return $this->hasMany(TreatmentReport::class);
	}

	public function users()
	{
		return $this->belongsToMany(User::class, 'device_users', 'device_id', 'user_id');
	}
}
