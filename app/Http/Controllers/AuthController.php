<?php

namespace App\Http\Controllers;

use App\Services\UserProviderService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    protected $redirectTo = '/home';

    protected $provider = 'google';

    public function signup()
    {
        try {
            $urlRedirect = Socialite::driver($this->provider)
                ->with(['hd' => 'melhorenvio.com'])
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'success' => true,
                'url'     => $urlRedirect
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    public function callbackProvider()
    {
        try {
            $userProvider = Socialite::driver($this->provider)->user();

            $user = (new UserProviderService())->saveOrUpdate($userProvider, $this->provider);

            $tokenResult = $user->createToken('Personal Access Token');

            $token = $tokenResult->token;

            if ($user->remember_me) {
                $token->expires_at = Carbon::now()->addWeeks(1);
            }

            $token->save();

            return response()->json([
                'success'      => true,
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
                'expires_at'   => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
                ], 200
            );
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
