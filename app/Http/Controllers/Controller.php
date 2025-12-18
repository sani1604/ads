<?php
// app/Http/Controllers/Controller.php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function successResponse(string $message, $data = null, int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message, int $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function redirectWithSuccess(string $route, string $message)
    {
        return redirect()->route($route)->with('success', $message);
    }

    protected function redirectWithError(string $route, string $message)
    {
        return redirect()->route($route)->with('error', $message);
    }

    protected function backWithSuccess(string $message)
    {
        return back()->with('success', $message);
    }

    protected function backWithError(string $message)
    {
        return back()->with('error', $message);
    }
}