<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HydroponicSetup
 * 
 * @property int $id
 * @property int $user_id
 * @property string $crop_name
 * @property int $number_of_crops
 * @property string $bed_size
 * @property string|null $pump_config
 * @property string|null $nutrient_solution
 * @property float $target_ph_min
 * @property float $target_ph_max
 * @property float $target_tds_min
 * @property float $target_tds_max
 * @property string|null $water_amount
 * @property Carbon|null $setup_date
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|HydroponicYield[] $hydroponic_yields
 *
 * @package App\Models
 */
class HydroponicSetup extends Model
{
	protected $table = 'hydroponic_setup';

	protected $casts = [
		'user_id' => 'int',
		'number_of_crops' => 'int',
		'target_ph_min' => 'float',
		'target_ph_max' => 'float',
		'target_tds_min' => 'float',
		'target_tds_max' => 'float',
		'setup_date' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'crop_name',
		'number_of_crops',
		'bed_size',
		'pump_config',
		'nutrient_solution',
		'target_ph_min',
		'target_ph_max',
		'target_tds_min',
		'target_tds_max',
		'water_amount',
		'setup_date',
		'status'
	];

	public function hydroponic_yields()
	{
		return $this->hasMany(HydroponicYield::class);
	}
}
