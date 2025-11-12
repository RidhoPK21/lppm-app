<?php

namespace App\Http\Middleware;

use App\Http\Api\UserApi;
use App\Models\HakAksesModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiCheckTokenMiddleware
{
    /**
     * @return Response|\Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil token dari header Authorization
        $authToken = $request->bearerToken();

        if (empty($authToken)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access, token is missing',
            ], 403);
        }

        $response = UserApi::getMe($authToken);

        if (! isset($response->data->user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access, token is invalid',
            ], 403);
        }

        // Menambahkan properti auth sebagai object
        $request->auth = $response->data->user;

        // Ambil akses user
        /** @var HakAksesModel|null $akses */
        $akses = HakAksesModel::where('user_id', $request->auth->id ?? 0)->first();

        // Pastikan $request->auth->akses selalu array
        $request->auth->akses = $akses ? explode(',', $akses->akses ?? '') : [];

        return $next($request);
    }
}
