namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceLog;
use Illuminate\Support\Facades\Log;
use App\Events\DeviceStatusUpdated;

class DeviceController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'device_id'   => 'required|string',
            'temperature' => 'required|numeric',
            'humidity'    => 'required|numeric',
            'doorLocked'  => 'required|boolean',
            'motion'      => 'required|boolean',
            'fire'        => 'required|boolean',
            'ambient'     => 'nullable|array'
        ]);

        $log = DeviceLog::create([
            'device_id'   => $data['device_id'],
            'temperature' => $data['temperature'],
            'humidity'    => $data['humidity'],
            'door_locked' => $data['doorLocked'],
            'motion'      => $data['motion'],
            'fire'        => $data['fire'],
            'ambient'     => $data['ambient'] ?? null,
        ]);

        // Fire a real-time event for dashboard updates
        broadcast(new DeviceStatusUpdated($log))->toOthers();

        return response()->json(['message' => 'Telemetry logged'], 200);
    }

    public function command(Request $request)
    {
        $data = $request->validate([
            'device_id' => 'required|string',
            'command'   => 'required|string',
            'pin'       => 'required|string|size:4'
        ]);

        // Verify command PIN (for example, using a simple check)
        if ($data['pin'] !== '1234') { // Replace with a secure check in production
            return response()->json(['message' => 'Invalid PIN'], 403);
        }

        // Here, forward the command via MQTT or other means.
        Log::info("Command for device {$data['device_id']}: " . $data['command']);

        return response()->json(['message' => 'Command forwarded'], 200);
    }
}
