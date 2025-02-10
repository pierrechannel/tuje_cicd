<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CsrfCookieController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['csrfToken' => $request->session()->token()]);
    }
}

