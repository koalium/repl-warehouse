namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeviceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DetectAnomalies extends Command
{
    protected $signature = 'anomalies:detect';
    protected $description = 'Detect anomalies in device telemetry';

    public function handle()
    {
        // Get logs from the last hour (or desired timeframe)
        $logs = DeviceLog::where('created_at', '>', Carbon::now()->subHour())->get();

        foreach ($logs as $log) {
            // Example: if temperature is unusually high, trigger an alert.
            if ($log->temperature > 50) {
                Log::warning("Anomaly detected on device {$log->device_id}: High temperature {$log->temperature}");
                // Further integration: push a notification or update a status flag.
            }
            // You can add more complex anomaly detection here.
        }

        $this->info("Anomaly detection completed.");
    }
}
