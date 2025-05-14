
// app/Http/Controllers/DeviceCommandController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeviceCommandController extends Controller
{
    public function execute(Request $request)
    {
        $validated = $request->validate([
            'command' => 'required|string',
            'pin'     => 'required|string|size:4',
        ]);

        // Here you could forward the command to the device via MQTT,
        // store it in the database, or invoke a push notification.
        // For this example, we simply return a confirmation.
        return response()->json(['message' => 'Command forwarded: ' . $validated['command']]);
    }
}
