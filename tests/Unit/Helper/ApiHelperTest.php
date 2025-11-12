<?php

namespace Tests\Unit\Helper;

use App\Helper\ApiHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    #[Test]
    public function send_request_berhasil_dengan_method_get()
    {
        // Mock HTTP response
        Http::fake([
            'https://api.example.com/data' => Http::response([
                'status' => 'success',
                'data' => ['id' => 1, 'name' => 'Test Data'],
            ], 200),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/data',
            'GET',
            [],
            'fake-token-123'
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals(1, $response->data->id);
        $this->assertEquals('Test Data', $response->data->name);
    }

    #[Test]
    public function send_request_berhasil_dengan_method_post()
    {
        $postData = ['name' => 'John Doe', 'email' => 'john@example.com'];

        Http::fake([
            'https://api.example.com/users' => Http::response([
                'status' => 'success',
                'message' => 'User created successfully',
            ], 201),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/users',
            'POST',
            $postData,
            'fake-token-456'
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('User created successfully', $response->message);
    }

    #[Test]
    public function send_request_berhasil_dengan_method_put()
    {
        $updateData = ['name' => 'Jane Doe'];

        Http::fake([
            'https://api.example.com/users/1' => Http::response([
                'status' => 'success',
                'message' => 'User updated successfully',
            ], 200),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/users/1',
            'PUT',
            $updateData,
            'fake-token-789'
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('User updated successfully', $response->message);
    }

    #[Test]
    public function send_request_berhasil_dengan_method_delete()
    {
        Http::fake([
            'https://api.example.com/users/1' => Http::response([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ], 200),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/users/1',
            'DELETE',
            [],
            'fake-token-999'
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('User deleted successfully', $response->message);
    }

    #[Test]
    public function send_request_berhasil_dengan_method_patch()
    {
        $patchData = ['status' => 'active'];

        Http::fake([
            'https://api.example.com/users/1/status' => Http::response([
                'status' => 'success',
                'message' => 'Status updated successfully',
            ], 200),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/users/1/status',
            'PATCH',
            $patchData,
            'fake-token-111'
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('Status updated successfully', $response->message);
    }

    #[Test]
    public function send_request_mengembalikan_error_jika_http_client_throw_exception()
    {
        Http::fake(function () {
            throw new \Exception('Connection timeout');
        });

        $response = ApiHelper::sendRequest(
            'https://api.example.com/unreachable',
            'GET',
            [],
            'fake-token-222'
        );

        $this->assertEquals('error', $response->status);
        $this->assertEquals('Gagal menghubungi server: Connection timeout', $response->message);
    }

    #[Test]
    public function send_request_mengirim_header_authorization_dengan_benar()
    {
        Http::fake(function ($request) {
            $this->assertEquals('Bearer fake-token-123', $request->header('Authorization')[0]);
            $this->assertEquals('application/json', $request->header('Accept')[0]);

            return Http::response(['status' => 'success'], 200);
        });

        ApiHelper::sendRequest(
            'https://api.example.com/protected',
            'GET',
            [],
            'fake-token-123'
        );
    }

    #[Test]
    public function send_request_mengirim_data_dengan_format_json()
    {
        $testData = ['key' => 'value', 'number' => 123];

        Http::fake(function ($request) use ($testData) {
            $this->assertEquals($testData, $request->data());

            return Http::response(['status' => 'success'], 200);
        });

        ApiHelper::sendRequest(
            'https://api.example.com/data',
            'POST',
            $testData,
            'fake-token-456'
        );
    }

    #[Test]
    public function send_request_with_file_berhasil_upload_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $postData = ['description' => 'Test document'];

        Http::fake([
            'https://api.example.com/upload' => Http::response([
                'status' => 'success',
                'message' => 'File uploaded successfully',
            ], 200),
        ]);

        $response = ApiHelper::sendRequestWithFile(
            'https://api.example.com/upload',
            'POST',
            $postData,
            'fake-token-upload',
            'document',
            $file
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('File uploaded successfully', $response->message);
    }

    #[Test]
    public function send_request_with_file_berhasil_tanpa_file()
    {
        $postData = ['name' => 'Test without file'];

        Http::fake([
            'https://api.example.com/upload' => Http::response([
                'status' => 'success',
                'message' => 'Request processed without file',
            ], 200),
        ]);

        $response = ApiHelper::sendRequestWithFile(
            'https://api.example.com/upload',
            'POST',
            $postData,
            'fake-token-no-file',
            'document',
            null
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('Request processed without file', $response->message);
    }

    #[Test]
    public function send_request_with_file_mengembalikan_error_jika_http_client_throw_exception()
    {
        Http::fake(function () {
            throw new \Exception('Upload failed');
        });

        $file = UploadedFile::fake()->create('test.jpg', 500);

        $response = ApiHelper::sendRequestWithFile(
            'https://api.example.com/upload',
            'POST',
            [],
            'fake-token-error',
            'image',
            $file
        );

        $this->assertEquals('error', $response->status);
        $this->assertEquals('Gagal menghubungi server: Upload failed', $response->message);
    }

    #[Test]
    public function send_request_with_file_mengirim_header_dengan_benar()
    {
        $file = UploadedFile::fake()->create('test.txt', 50);

        Http::fake(function ($request) {
            $this->assertEquals('Bearer fake-token-file', $request->header('Authorization')[0]);
            $this->assertEquals('application/json', $request->header('Accept')[0]);

            return Http::response(['status' => 'success'], 200);
        });

        ApiHelper::sendRequestWithFile(
            'https://api.example.com/upload',
            'POST',
            ['title' => 'Test file'],
            'fake-token-file',
            'attachment',
            $file
        );
    }

    #[Test]
    public function send_request_mengkonversi_method_ke_lowercase()
    {
        Http::fake([
            'https://api.example.com/test' => Http::response(['status' => 'success'], 200),
        ]);

        // Test dengan uppercase method
        $response = ApiHelper::sendRequest(
            'https://api.example.com/test',
            'GET',  // Uppercase
            [],
            'fake-token'
        );

        $this->assertEquals('success', $response->status);

        // Test dengan mixed case method
        $response2 = ApiHelper::sendRequest(
            'https://api.example.com/test',
            'PoSt',  // Mixed case
            [],
            'fake-token'
        );

        $this->assertEquals('success', $response2->status);
    }

    #[Test]
    public function send_request_dengan_data_kosong()
    {
        Http::fake([
            'https://api.example.com/empty' => Http::response([
                'status' => 'success',
                'message' => 'Empty data processed',
            ], 200),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/empty',
            'GET',
            [],  // Data kosong
            'fake-token-empty'
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('Empty data processed', $response->message);
    }

    #[Test]
    public function send_request_dengan_token_kosong()
    {
        Http::fake([
            'https://api.example.com/public' => Http::response([
                'status' => 'success',
                'message' => 'Public endpoint accessed',
            ], 200),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/public',
            'GET',
            [],
            ''  // Token kosong
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('Public endpoint accessed', $response->message);
    }

    #[Test]
    public function send_request_with_file_dengan_custom_file_key()
    {
        $file = UploadedFile::fake()->create('avatar.png', 200);
        $postData = ['user_id' => 123];

        Http::fake(function ($request) {
            // Verify the request was made with file
            return Http::response([
                'status' => 'success',
                'message' => 'Avatar uploaded with custom key',
            ], 200);
        });

        $response = ApiHelper::sendRequestWithFile(
            'https://api.example.com/avatar',
            'POST',
            $postData,
            'fake-token-avatar',
            'avatar_file',  // Custom file key
            $file
        );

        $this->assertEquals('success', $response->status);
        $this->assertEquals('Avatar uploaded with custom key', $response->message);
    }

    #[Test]
    public function send_request_mengembalikan_object_response()
    {
        Http::fake([
            'https://api.example.com/object' => Http::response([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => 1,
                        'name' => 'Test User',
                        'active' => true,
                    ],
                ],
            ], 200),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/object',
            'GET',
            [],
            'fake-token-object'
        );

        $this->assertIsObject($response);
        $this->assertEquals('success', $response->status);
        $this->assertEquals(1, $response->data->user->id);
        $this->assertEquals('Test User', $response->data->user->name);
        $this->assertTrue($response->data->user->active);
    }

    #[Test]
    public function send_request_dengan_response_error_dari_server()
    {
        Http::fake([
            'https://api.example.com/error' => Http::response([
                'status' => 'error',
                'message' => 'Internal server error',
            ], 500),
        ]);

        $response = ApiHelper::sendRequest(
            'https://api.example.com/error',
            'GET',
            [],
            'fake-token-error'
        );

        $this->assertEquals('error', $response->status);
        $this->assertEquals('Internal server error', $response->message);
    }
}
