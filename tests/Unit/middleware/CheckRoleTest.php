<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CheckRoleTest extends TestCase
{
    /**
     * Helper untuk membuat request dengan mock auth attribute
     */
    protected function createRequestWithAuth($akses = null, $roles = null)
    {
        $request = Request::create('/test', 'GET');

        $auth = new stdClass;

        // Simulasi kondisi property akses
        if ($akses !== 'UNDEFINED') {
            $auth->akses = $akses;
        }

        // Simulasi kondisi property roles
        if ($roles !== null) {
            $auth->roles = $roles;
        }

        $request->attributes->set('auth', $auth);

        return $request;
    }

    /**
     * Helper untuk mock closure $next
     */
    protected function getNextClosure()
    {
        return function () {
            return new Response('OK');
        };
    }

    // 1. Test Akses String (Parsing Comma Separated)
    public function test_allows_access_when_akses_is_string_and_matches()
    {
        // akses: "Dosen, Admin" (String)
        $request = $this->createRequestWithAuth('Dosen, Admin', []);
        $middleware = new CheckRole;

        $response = $middleware->handle($request, $this->getNextClosure(), 'Admin');

        $this->assertEquals('OK', $response->getContent());
    }

    // 2. Test Akses Array
    public function test_allows_access_when_akses_is_array_and_matches()
    {
        // akses: ["Dosen", "Staff"] (Array)
        $request = $this->createRequestWithAuth(['Dosen', 'Staff'], []);
        $middleware = new CheckRole;

        $response = $middleware->handle($request, $this->getNextClosure(), 'Staff');

        $this->assertEquals('OK', $response->getContent());
    }

    // 3. Test Roles User (Bukan Akses)
    public function test_allows_access_when_roles_matches_required()
    {
        // akses kosong, tapi roles punya "Editor"
        $request = $this->createRequestWithAuth([], ['Editor']);
        $middleware = new CheckRole;

        $response = $middleware->handle($request, $this->getNextClosure(), 'Editor');

        $this->assertEquals('OK', $response->getContent());
    }

    // 4. Test "Lppm Ketua" Bypass (Super Admin Logic)
    public function test_allows_access_for_lppm_ketua_regardless_of_requirement()
    {
        // User punya "Lppm Ketua", tapi route butuh "Dosen"
        // Logika kode: if (in_array('Lppm Ketua', $aksesUser)... $hasAccess = true
        $request = $this->createRequestWithAuth(['Lppm Ketua'], []);
        $middleware = new CheckRole;

        $response = $middleware->handle($request, $this->getNextClosure(), 'Dosen'); // Requirement tidak match

        $this->assertEquals('OK', $response->getContent());
    }

    // 5. Test Multiple Required Roles (Pipe Separator)
    public function test_allows_access_with_pipe_separated_requirements()
    {
        // Route butuh: "Admin|Manager", User punya: "Manager"
        $request = $this->createRequestWithAuth(['Manager'], []);
        $middleware = new CheckRole;

        $response = $middleware->handle($request, $this->getNextClosure(), 'Admin|Manager');

        $this->assertEquals('OK', $response->getContent());
    }

    // 6. Test Deny Access (403)
    // 6. Test Deny Access (403)
    public function test_denies_access_if_role_mismatch()
    {
        // User: Mahasiswa, Req: Dosen
        $request = $this->createRequestWithAuth(['Mahasiswa'], []);
        $middleware = new CheckRole;

        try {
            $middleware->handle($request, $this->getNextClosure(), 'Dosen');

            // Jika baris di atas tidak melempar error, berarti test gagal (harusnya error)
            $this->fail('Expected HttpException 403 was not thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Assert Status Code HTTP benar-benar 403
            $this->assertEquals(403, $e->getStatusCode());
            $this->assertEquals('You do not have access to this page.', $e->getMessage());
        }
    }

    // 7. Coverage: Parsing Edge Cases (Else Block & Isset)
    public function test_handles_edge_cases_for_auth_attributes()
    {
        $middleware = new CheckRole;

        // Case A: Auth->akses tidak di-set (masuk !isset)
        $req1 = $this->createRequestWithAuth('UNDEFINED', []);
        try {
            $middleware->handle($req1, $this->getNextClosure(), 'Admin');
        } catch (\Exception $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // Case B: Auth->akses tipe data aneh (masuk block else terakhir)
        // Misal integer, bukan string/array
        $req2 = $this->createRequestWithAuth(12345, []);
        try {
            $middleware->handle($req2, $this->getNextClosure(), 'Admin');
        } catch (\Exception $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // Case C: Auth->roles tidak ada (isset check)
        // Kita kirim null ke helper agar property roles tidak dibuat
        $req3 = $this->createRequestWithAuth([], null);
        try {
            $middleware->handle($req3, $this->getNextClosure(), 'Admin');
        } catch (\Exception $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }
}
