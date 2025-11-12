<?php

namespace Tests\Unit\Helper;

use App\Helper\ToolsHelper;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ToolsHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Session::flush();  // Clear session sebelum setiap test
    }

    #[Test]
    public function set_auth_token_menyimpan_token_ke_session()
    {
        $token = 'sample-auth-token-123';

        ToolsHelper::setAuthToken($token);

        $this->assertEquals($token, Session::get('auth_token'));
    }

    #[Test]
    public function get_auth_token_mengembalikan_token_dari_session()
    {
        $token = 'test-token-456';
        Session::put('auth_token', $token);

        $result = ToolsHelper::getAuthToken();

        $this->assertEquals($token, $result);
    }

    #[Test]
    public function get_auth_token_mengembalikan_string_kosong_jika_token_tidak_ada()
    {
        Session::forget('auth_token');

        $result = ToolsHelper::getAuthToken();

        $this->assertEquals('', $result);
    }

    #[Test]
    public function generate_id_mengembalikan_uuid_string()
    {
        $result = ToolsHelper::generateId();

        $this->assertIsString($result);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $result
        );
    }

    #[Test]
    public function generate_id_mengembalikan_uuid_unik()
    {
        $uuid1 = ToolsHelper::generateId();
        $uuid2 = ToolsHelper::generateId();

        $this->assertNotEquals($uuid1, $uuid2);
    }

    #[Test]
    public function desimal_to_romawi_mengkonversi_angka_1_10_ke_romawi()
    {
        $testCases = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
        ];

        foreach ($testCases as $desimal => $expectedRomawi) {
            $result = ToolsHelper::desimalToRomawi($desimal);
            $this->assertEquals($expectedRomawi, $result, "Failed for decimal: $desimal");
        }
    }

    #[Test]
    public function desimal_to_romawi_mengembalikan_string_kosong_untuk_angka_diluar_1_10()
    {
        $this->assertEquals('', ToolsHelper::desimalToRomawi(0));
        $this->assertEquals('', ToolsHelper::desimalToRomawi(11));
        $this->assertEquals('', ToolsHelper::desimalToRomawi(-1));
        $this->assertEquals('', ToolsHelper::desimalToRomawi(100));
    }

    #[Test]
    public function desimal_to_romawi_mengembalikan_string_kosong_untuk_null()
    {
        $this->assertEquals('', ToolsHelper::desimalToRomawi(null));
    }

    #[Test]
    public function check_roles_berhasil_dengan_allowed_roles_array()
    {
        $allowedRoles = ['admin', 'user', 'editor'];

        $this->assertTrue(ToolsHelper::checkRoles('admin', $allowedRoles));
        $this->assertTrue(ToolsHelper::checkRoles('user', $allowedRoles));
        $this->assertTrue(ToolsHelper::checkRoles('editor', $allowedRoles));
        $this->assertFalse(ToolsHelper::checkRoles('guest', $allowedRoles));
    }

    #[Test]
    public function check_roles_berhasil_dengan_allowed_roles_string()
    {
        $allowedRoles = 'admin';

        $this->assertTrue(ToolsHelper::checkRoles('admin', $allowedRoles));
        $this->assertFalse(ToolsHelper::checkRoles('user', $allowedRoles));
    }

    #[Test]
    public function check_roles_mengembalikan_false_untuk_allowed_roles_bukan_array_atau_string()
    {
        $this->assertFalse(ToolsHelper::checkRoles('admin', 123));
        $this->assertFalse(ToolsHelper::checkRoles('admin', null));
        $this->assertFalse(ToolsHelper::checkRoles('admin', true));
        $this->assertFalse(ToolsHelper::checkRoles('admin', new \stdClass));
    }

    #[Test]
    public function check_roles_mengembalikan_false_untuk_roles_kosong()
    {
        $this->assertFalse(ToolsHelper::checkRoles('', ['admin', 'user']));
        $this->assertFalse(ToolsHelper::checkRoles(null, ['admin', 'user']));
    }

    #[Test]
    public function excel_column_range_berhasil_dari_a_ke_d()
    {
        $result = ToolsHelper::excelColumnRange('A', 'D');

        $expected = ['A', 'B', 'C', 'D'];
        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function excel_column_range_berhasil_dari_a_ke_a()
    {
        $result = ToolsHelper::excelColumnRange('A', 'A');

        $expected = ['A'];
        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function excel_column_range_berhasil_dari_x_ke_z()
    {
        $result = ToolsHelper::excelColumnRange('X', 'Z');

        $expected = ['X', 'Y', 'Z'];
        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function excel_column_range_berhasil_dari_z_ke_ab()
    {
        $result = ToolsHelper::excelColumnRange('Z', 'AB');

        $expected = ['Z', 'AA', 'AB'];
        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function excel_column_range_berhasil_dari_a_a_ke_ac()
    {
        $result = ToolsHelper::excelColumnRange('AA', 'AC');

        $expected = ['AA', 'AB', 'AC'];
        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function get_value_excel_dengan_col_index_int()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('B5')
            ->willReturn($cellMock);

        $cellMock
            ->method('getValue')
            ->willReturn('Test Value');

        $result = ToolsHelper::getValueExcel($worksheet, 2, 5);  // colIndex = 2 (B), rowIndex = 5

        $this->assertEquals('Test Value', $result);
    }

    #[Test]
    public function get_value_excel_dengan_col_index_string()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('C10')
            ->willReturn($cellMock);

        $cellMock
            ->method('getValue')
            ->willReturn('String Value');

        $result = ToolsHelper::getValueExcel($worksheet, 'C', 10);

        $this->assertEquals('String Value', $result);
    }

    #[Test]
    public function get_value_excel_trim_spaces()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('A1')
            ->willReturn($cellMock);

        $cellMock
            ->method('getValue')
            ->willReturn('  Value with spaces  ');

        $result = ToolsHelper::getValueExcel($worksheet, 1, 1);

        $this->assertEquals('Value with spaces', $result);
    }

    #[Test]
    public function get_value_excel_dengan_nilai_kosong()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('D15')
            ->willReturn($cellMock);

        $cellMock
            ->method('getValue')
            ->willReturn('');

        $result = ToolsHelper::getValueExcel($worksheet, 'D', 15);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function get_value_excel_dengan_nilai_null()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('E20')
            ->willReturn($cellMock);

        $cellMock
            ->method('getValue')
            ->willReturn(null);

        $result = ToolsHelper::getValueExcel($worksheet, 'E', 20);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function get_formatted_value_excel_dengan_col_index_int()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('F8')
            ->willReturn($cellMock);

        $cellMock
            ->method('getFormattedValue')
            ->willReturn('Formatted Value');

        $result = ToolsHelper::getFormattedValueExcel($worksheet, 6, 8);  // colIndex = 6 (F), rowIndex = 8

        $this->assertEquals('Formatted Value', $result);
    }

    #[Test]
    public function get_formatted_value_excel_dengan_col_index_string()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('G12')
            ->willReturn($cellMock);

        $cellMock
            ->method('getFormattedValue')
            ->willReturn('$1,000.00');

        $result = ToolsHelper::getFormattedValueExcel($worksheet, 'G', 12);

        $this->assertEquals('$1,000.00', $result);
    }

    #[Test]
    public function get_formatted_value_excel_trim_spaces()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('H3')
            ->willReturn($cellMock);

        $cellMock
            ->method('getFormattedValue')
            ->willReturn('  Formatted with spaces  ');

        $result = ToolsHelper::getFormattedValueExcel($worksheet, 'H', 3);

        $this->assertEquals('Formatted with spaces', $result);
    }

    #[Test]
    public function get_formatted_value_excel_dengan_nilai_kosong()
    {
        $worksheet = $this->createMock(Worksheet::class);
        $cellMock = $this->createMock(\PhpOffice\PhpSpreadsheet\Cell\Cell::class);

        $worksheet
            ->method('getCell')
            ->with('I7')
            ->willReturn($cellMock);

        $cellMock
            ->method('getFormattedValue')
            ->willReturn('');

        $result = ToolsHelper::getFormattedValueExcel($worksheet, 'I', 7);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function coordinate_string_from_column_index_berfungsi_dengan_benar()
    {
        // Test helper untuk memastikan Coordinate::stringFromColumnIndex bekerja
        $this->assertEquals('A', Coordinate::stringFromColumnIndex(1));
        $this->assertEquals('B', Coordinate::stringFromColumnIndex(2));
        $this->assertEquals('Z', Coordinate::stringFromColumnIndex(26));
        $this->assertEquals('AA', Coordinate::stringFromColumnIndex(27));
        $this->assertEquals('AB', Coordinate::stringFromColumnIndex(28));
    }

    #[Test]
    public function session_persistence_antara_set_dan_get_auth_token()
    {
        $token = 'persistent-token-789';

        // Set token
        ToolsHelper::setAuthToken($token);

        // Get token - harus sama
        $retrievedToken = ToolsHelper::getAuthToken();

        $this->assertEquals($token, $retrievedToken);
    }

    #[Test]
    public function multiple_set_auth_token_menimpa_nilai_sebelumnya()
    {
        ToolsHelper::setAuthToken('first-token');
        ToolsHelper::setAuthToken('second-token');

        $result = ToolsHelper::getAuthToken();

        $this->assertEquals('second-token', $result);
    }
}
