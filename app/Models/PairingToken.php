<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PairingToken
 * 
 * @property int $id
 * @property int $user_id
 * @property string $token_hash
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class PairingToken extends Model
{
	protected $table = 'pairing_tokens';

	protected $casts = [
		'user_id' => 'int',
		'expires_at' => 'datetime',
		'used_at' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'token_hash',
		'expires_at',
		'used_at'
	];
}
