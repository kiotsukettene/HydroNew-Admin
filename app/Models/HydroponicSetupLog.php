<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HydroponicSetupLog
 * 
 * @property int $id
 * @property int $hydroponic_setup_id
 * @property string|null $growth_stage
 * @property float|null $ph_status
 * @property float|null $tds_status
 * @property float|null $ec_status
 * @property float|null $humidity_status
 * @property string $health_status
 * @property Carbon|null $harvest_date
 * @property bool $system_generated
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property HydroponicSetup $hydroponic_setup
 *
 * @package App\Models
 */
class HydroponicSetupLog extends Model
{
	protected $table = 'hydroponic_setup_logs';

	protected $casts = [
		'hydroponic_setup_id' => 'int',
		'ph_status' => 'float',
		'tds_status' => 'float',
		'ec_status' => 'float',
		'humidity_status' => 'float',
		'harvest_date' => 'datetime',
		'system_generated' => 'bool'
	];

	protected $fillable = [
		'hydroponic_setup_id',
		'growth_stage',
		'ph_status',
		'tds_status',
		'ec_status',
		'humidity_status',
		'health_status',
		'harvest_date',
		'system_generated',
		'notes'
	];

	public function hydroponic_setup()
	{
		return $this->belongsTo(HydroponicSetup::class);
	}
}
