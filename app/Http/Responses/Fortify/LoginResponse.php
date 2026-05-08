<?php

namespace App\Http\Responses\Fortify;

use App\Support\AppUrl;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $intended = $request->session()->pull('url.intended');

        return redirect()->to(AppUrl::normalizeInternalRedirect($intended ?: route('dashboard')));
    }
}
