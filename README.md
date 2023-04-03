<h1>Laravel Passport</h1>
<h2>Passport installation</h2>
<p>To get started, create a new Laravel project named <b>passport</b>:</p>
<pre>composer create-project --prefer-dist laravel/laravel <b>passport</b></pre>
<p>Now, you need to move your CLI directory to your project folder:</p>
<pre>cd passport</pre>
<p>Install Passport</p>
<pre>composer require laravel/passport</pre>
<p>Now, you should migrate your database after installing the package</p>
<pre>php artisan migrate</pre>
<p>Next, you should execute the <code>passport:install</code> Artisan command. This command will create the encryption keys needed to generate secure access tokens.
<pre>php artisan passport:install</pre>
<p>After running the <code>passport:install</code> command, add the <code>Laravel\Passport\HasApiTokens</code> trait to your <code>App\Models\User</code> model:</p>
<blockquote><pre>
class User extends Authenticatable
{
use HasApiTokens, HasFactory, Notifiable;
}
</pre></blockquote>
<p><b>Note:</b> If your model is already using the <code>Laravel\Sanctum\HasApiTokens</code> trait, you may remove that trait.</p>
<p>Finally, in your application's <code>config/auth.php</code> configuration file, you should define an api authentication guard and set the driver option to passport:</p>
<blockquote><pre>
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
 
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
</pre></blockquote>
<h2>User authentication</h2>
<p>To use this API server, a client should register first. Create a <code>RegisterController</code>:</p>
<blockquote><pre>
class RegisterController extends Controller
{
    function register(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['min:8', 'confirmed']
        ]);
        $validatedData['password'] = Hash::make($validatedData['password']);
        $user = User::create($validatedData);
        $token = $user->createToken('auth_token')->accessToken;
        return response()->json(
          [
              'token' => $token,
              'user' => $user,
              'message' => 'User created successfully',
              'status' => 1
          ]
        );
    }
}
</pre></blockquote>
<p>Then, crate a <code>LoginController</code>:</p>
<blockquote><pre>
class LoginController extends Controller
{
    function login(Request $request) {
        $validatedData = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);
        $user = User::where('email', $validatedData['email'])->first();

        if(Hash::check($validatedData['password'], $user->password)) {
            $token = $user->createToken('auth_token')->accessToken;
            return response()->json(
                [
                    'token' => $token,
                    'user' => $user,
                    'message' => 'Logged in successfully',
                    'status' => 1
                ]
            );
        }
        return response()->json(
            [
                'message' => 'Email or Password does not match',
                'status' => 0
            ]
        );
    }
}
</pre></blockquote>
<p>Next, create two routes in <code>routes\api.php</code>:</p>
<blockquote><pre>
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
</pre></blockquote>
<p><b>Done!</b> API server is ready. Now, create a api middleware group with few routes to get resources from server:</p>
<blockquote><pre>
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:api')->group(function () {
    //API authenticated routes will be here
});
</pre></blockquote>