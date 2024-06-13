<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use PragmaRX\Google2FALaravel\Facade as Google2FAFacade;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthController extends Controller
{
    public function setup2FA(Request $request)
    {
        $user = Auth::user();

        $secret = Google2FAFacade::generateSecretKey();

        $inlineUrl = 'data:image/svg+xml;base64,' . base64_encode(Google2FAFacade::getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        ));

        return response()->json([
            'secret' => $secret,
            'inlineUrl' => $inlineUrl,
        ]);
    }

    public function enable2FA(Request $request)
    {
        $request->validate([
            'secret' => 'required',
            'otp' => 'required',
        ]);

        $google2fa = app('pragmarx.google2fa');

        $user = User::findOrFail(Auth::user()->id);
        $secret = $request->input('secret');
        $otp = $request->input('otp');

        if ($google2fa->verifyKey($secret, $otp)) {
            $user->google2fa_secret = $secret;
            $user->google2fa_enabled = true;
            $user->save();

            return response()->json(['message' => '2FA enabled successfully']);
        } else {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'otp' => 'required',
        ]);

        $user = Auth::user();
        $otp = $request->input('otp');
        $google2fa = app('pragmarx.google2fa');

        if ($google2fa->verifyKey($user->google2fa_secret, $otp)) {
            $request->session()->put('2fa_authenticated', true);

            return response()->json(['message' => 'OTP verified successfully']);
        } else {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }
    }

    public function disable2FA(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        $user->google2fa_secret = null;
        $user->google2fa_enabled = false;
        $user->save();

        return response()->json(['message' => '2FA disabled successfully']);
    }
}
