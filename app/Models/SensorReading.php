<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SensorReading
 * 
 * @property int $id
 * @property int $sensor_system_id
 * @property float|null $ph
 * @property float|null $tds
 * @property float|null $turbidity
 * @property float|null $water_level
 * @property float|null $humidity
 * @property float|null $temperature
 * @property float|null $ec
 * @property float|null $electric_current
 * @property Carbon|null $reading_time
 * 
 * @property SensorSystem $sensor_system
 *
 * @package App\Models
 */
class SensorReading extends Model
{
	protected $table = 'sensor_readings';
	public $timestamps = false;

	protected $casts = [
		'sensor_system_id' => 'int',
		'ph' => 'float',
		'tds' => 'float',
		'turbidity' => 'float',
		'water_level' => 'float',
		'humidity' => 'float',
		'temperature' => 'float',
		'ec' => 'float',
		'electric_current' => 'float',
		'reading_time' => 'datetime'
	];

	protected $fillable = [
		'sensor_system_id',
		'ph',
		'tds',
		'turbidity',
		'water_level',
		'humidity',
		'temperature',
		'ec',
		'electric_current',
		'reading_time'
	];

	public function sensor_system()
	{
		return $this->belongsTo(SensorSystem::class);
	}
}
