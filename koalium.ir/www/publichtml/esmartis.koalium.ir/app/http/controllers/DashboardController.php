namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeviceLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $logs = DeviceLog::orderBy('created_at', 'desc')->paginate(50);
        $alertsCount = DeviceLog::where(function ($query) {
            $query->where('fire', true)
                  ->orWhere('motion', true);
        })->where('created_at', '>', Carbon::now()->subDay())
          ->count();

        return view('dashboard.index', compact('logs', 'alertsCount'));
    }
}
