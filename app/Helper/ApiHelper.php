<?php

namespace App\Helper;

use Illuminate\Support\Facades\Http;

class ApiHelper
{
    public static function sendRequest($url, $method, $data = [], $authToken = '')
    {
        $method = strtolower($method);

        try {
            $response = Http::asJson()
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$authToken,
                ])
                ->$method($url, $data);

            return $response->object();
        } catch (\Throwable $e) {
            return (object) [
                'status' => 'error',
                'message' => 'Gagal menghubungi server: '.$e->getMessage(),
            ];
        }
    }

    // Fungsi khusus untuk mengirimkan file (upload)
    public static function sendRequestWithFile($url, $method, $data = [], $authToken = '', $fileKey = 'file', $file = null)
    {
        try {
            $request = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$authToken,
            ]);

            $method = strtolower($method);

            if ($file) {
                $response = $request->attach(
                    $fileKey,
                    fopen($file->getRealPath(), 'r'),
                    $file->getClientOriginalName()
                )->$method($url, $data);
            } else {
                $response = $request->$method($url, $data);
            }

            return $response->object();
        } catch (\Throwable $e) {
            return (object) [
                'status' => 'error',
                'message' => 'Gagal menghubungi server: '.$e->getMessage(),
            ];
        }
    }
}
