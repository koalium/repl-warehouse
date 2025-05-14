use App\Http\Controllers\Api\DeviceController;
use Illuminate\Support\Facades\Route;

Route::post('/device/update', [DeviceController::class, 'update']);
Route::post('/device/command', [DeviceController::class, 'command']);
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceCommandController;

Route::post('/device/command', [DeviceCommandController::class, 'execute']);
