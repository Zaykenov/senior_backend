use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ])->assignRole('user');

        return response()->json(['token' => $user->createToken('API Token')->plainTextToken]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        return response()->json(['token' => auth()->user()->createToken('API Token')->plainTextToken]);
    }
}