<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function __invoke(Request $request)
    {
        return redirect()->route($request->user()->rol->inicio());
    }
}
