namespace App\Events;

use App\Models\DeviceLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deviceLog;

    public function __construct(DeviceLog $deviceLog)
    {
        $this->deviceLog = $deviceLog;
    }

    public function broadcastOn()
    {
        return new Channel('device-updates');
    }
}
