<?php
//ini untuk MICROSERVICE, YANG FULL STACK CONTROLLER LOGINNYA LOGINCONTROLLER
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Auth;
use App\User;
class LoginApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */
    use AuthenticatesUsers;
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
	
	
	
	
	
	public function login(Request $request)
	{
		$this->validateLogin($request);
		if ($this->attemptLogin($request)) {
			$user = $this->guard()->user();
			
			$user->generateToken();
			
			
			return response()->json([
				'data' => $user->toArray(),
				'tokens' => $user->api_token,
			]);
		}
		return $this->sendFailedLoginResponse($request);
	}
	
	public function logout(Request $request)
	{
			$this->validateLogin($request);
			$this->attemptLogin($request);
			
			$user = $this ->guard()->user();
			
			
			
		if ($user) {
			$user->api_token = null;
			$user->save();
			
			return response()->json(['data' => 'User Log Out'], 200);
		}
		return response()->json(['data' => $user], 200);
	}
}