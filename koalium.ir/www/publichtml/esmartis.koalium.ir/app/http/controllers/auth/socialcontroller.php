
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    // Redirect to provider's auth page.
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // Handle provider callback.
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors('Login failed via ' . ucfirst($provider));
        }

        // Find or create the user.
        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name'     => $socialUser->getName() ?? $socialUser->getNickname(),
                'password' => bcrypt(Str::random(16)), // Social login only.
            ]
        );

        Auth::login($user, true);

        return redirect('/dashboard');
    }
}
