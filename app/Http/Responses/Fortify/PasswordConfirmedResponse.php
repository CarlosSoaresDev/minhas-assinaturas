<?php

namespace App\Http\Responses\Fortify;

use App\Support\AppUrl;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;

class PasswordConfirmedResponse implements PasswordConfirmedResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $intended = $request->session()->pull('url.intended');

        return redirect()->to(AppUrl::normalizeInternalRedirect($intended ?: route('dashboard')));
    }
}
