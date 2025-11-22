<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HelpCenter
 * 
 * @property int $id
 * @property string|null $question
 * @property string|null $answer
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class HelpCenter extends Model
{
	protected $table = 'help_centers';

	protected $fillable = [
		'question',
		'answer'
	];
}
