namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceLog extends Model
{
    protected $fillable = [
        'device_id', 'temperature', 'humidity', 'door_locked',
        'motion', 'fire', 'ambient'
    ];

    protected $casts = [
        'ambient' => 'array',
        'door_locked' => 'boolean',
        'motion' => 'boolean',
        'fire' => 'boolean',
    ];
}
