<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class APIController extends Controller
{
    /**
     * @param $status_code
     * @param $message
     *
     * @return JsonResponse
     */
    protected function responseWithError($status_code, $message): JsonResponse
    {
        return response()->json([
            'error' => [
                'status' => $status_code,
                'message' => $message,
            ],
        ], $status_code);
    }
}
