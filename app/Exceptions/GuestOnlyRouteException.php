<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

final class GuestOnlyRouteException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => __('Already Authenticated')], Response::HTTP_FORBIDDEN);
    }
}
