<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse($success, $mensaje, $result, $status){
        $response = collect([
            'success'    => $success,
            'mensaje'   => $mensaje,
        ]);

        if (!collect($result)->has('current_page')) { $result = ['data' => $result]; }

        $combined = $response->union($result);

        return response()->json($combined, $status);
    }
}
