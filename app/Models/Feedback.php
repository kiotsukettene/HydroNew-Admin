<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Feedback
 * 
 * @property int $id
 * @property int $user_id
 * @property int $device_id
 * @property string $category
 * @property string|null $subject
 * @property string $message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Device $device
 * @property User $user
 *
 * @package App\Models
 */
class Feedback extends Model
{
	use HasFactory;
	
	protected $table = 'feedback';

	protected $casts = [
		'user_id' => 'int',
		'device_id' => 'int',
		'replied' => 'boolean'
	];

	protected $fillable = [
		'user_id',
		'device_id',
		'category',
		'subject',
		'message',
		'replied'
	];

	public function device()
	{
		return $this->belongsTo(Device::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
