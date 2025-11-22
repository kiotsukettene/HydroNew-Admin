<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HydroponicYield
 * 
 * @property int $id
 * @property int $hydroponic_setup_id
 * @property string|null $harvest_status
 * @property string|null $growth_stage
 * @property string $health_status
 * @property float|null $predicted_yield
 * @property float|null $actual_yield
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
class HydroponicYield extends Model
{
	protected $table = 'hydroponic_yields';

	protected $casts = [
		'hydroponic_setup_id' => 'int',
		'predicted_yield' => 'float',
		'actual_yield' => 'float',
		'harvest_date' => 'datetime',
		'system_generated' => 'bool'
	];

	protected $fillable = [
		'hydroponic_setup_id',
		'harvest_status',
		'growth_stage',
		'health_status',
		'predicted_yield',
		'actual_yield',
		'harvest_date',
		'system_generated',
		'notes'
	];

	public function hydroponic_setup()
	{
		return $this->belongsTo(HydroponicSetup::class);
	}
}
