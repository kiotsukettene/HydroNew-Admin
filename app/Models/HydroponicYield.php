<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class HydroponicYield
 * 
 * @property int $id
 * @property int $hydroponic_setup_id
 * @property float|null $total_weight
 * @property int|null $total_count
 * @property string|null $notes
 * @property bool $is_archived
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property HydroponicSetup $hydroponic_setup
 * @property Collection|HydroponicYieldGrade[] $hydroponic_yield_grades
 *
 * @package App\Models
 */
class HydroponicYield extends Model
{
	use HasFactory;
	protected $table = 'hydroponic_yields';

	protected $casts = [
		'hydroponic_setup_id' => 'int',
		'total_weight' => 'float',
		'total_count' => 'int',
		'is_archived' => 'bool'
	];

	protected $fillable = [
		'hydroponic_setup_id',
		'total_weight',
		'total_count',
		'notes',
		'is_archived'
	];

	public function hydroponic_setup()
	{
		return $this->belongsTo(HydroponicSetup::class);
	}

	public function hydroponic_yield_grades()
	{
		return $this->hasMany(HydroponicYieldGrade::class);
	}
}
