use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\SocialController;

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::get('auth/{provider}', [SocialController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialController::class, 'handleProviderCallback'])->name('social.callback');

use App\Http\Controllers\Auth\SocialController;

Route::get('auth/{provider}', [SocialController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialController::class, 'handleProviderCallback'])->name('social.callback');

