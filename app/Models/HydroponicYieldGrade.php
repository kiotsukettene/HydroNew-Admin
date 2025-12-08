<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HydroponicYieldGrade
 * 
 * @property int $id
 * @property int $hydroponic_yield_id
 * @property string $grade
 * @property int $count
 * @property float|null $weight
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property HydroponicYield $hydroponic_yield
 *
 * @package App\Models
 */
class HydroponicYieldGrade extends Model
{
	protected $table = 'hydroponic_yield_grades';

	protected $casts = [
		'hydroponic_yield_id' => 'int',
		'count' => 'int',
		'weight' => 'float'
	];

	protected $fillable = [
		'hydroponic_yield_id',
		'grade',
		'count',
		'weight'
	];

	public function hydroponic_yield()
	{
		return $this->belongsTo(HydroponicYield::class);
	}
}
