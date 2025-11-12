<?php

namespace App\Helper;

use Illuminate\Support\Str;

class ToolsHelper
{
    // Menyimpan auth token ke session
    public static function setAuthToken($authToken)
    {
        session(['auth_token' => $authToken]);
    }

    // Mendapatkan auth token dari session
    public static function getAuthToken()
    {
        return session('auth_token', '');
    }

    // Menghasilkan UUID sebagai ID unik
    public static function generateId()
    {
        return Str::uuid()->toString();
    }

    // Mengonversi angka desimal (1-10) ke angka Romawi
    public static function desimalToRomawi($desimal)
    {
        $romawi = [
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

        return $romawi[$desimal] ?? '';
    }

    // Memeriksa apakah $roles ada dalam $allowedRoles (bisa array atau string)
    public static function checkRoles($roles, $allowedRoles)
    {
        if (is_array($allowedRoles)) {
            return in_array($roles, $allowedRoles);
        } elseif (is_string($allowedRoles)) {
            return $roles === $allowedRoles;
        }

        return false;
    }

    // Menghasilkan array kolom Excel dari $start hingga $end (misal: A hingga D menghasilkan [A, B, C, D])
    public static function excelColumnRange($start, $end)
    {
        $columns = [];
        $current = $start;
        while (true) {
            $columns[] = $current;
            if ($current === $end) {
                break;
            }
            $current = ++$current;  // increment seperti Excel (A → B → ... → Z → AA)
        }

        return $columns;
    }

    /**
     * Mendapatkan nilai RAW dari sel Excel (nilai asli yang tersimpan)
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $worksheet  Worksheet object
     * @param  int|string  $colIndex  Indeks kolom (1-based: 1 = A, 2 = B, atau string A/B/C)
     * @param  int  $rowIndex  Indeks baris
     * @return string Nilai sel dalam bentuk string yang di-trim
     */
    public static function getValueExcel($worksheet, $colIndex, $rowIndex)
    {
        $columnLetter = is_int($colIndex) ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) : $colIndex;
        $cellAddress = $columnLetter.$rowIndex;
        $cell = $worksheet->getCell($cellAddress);

        return trim((string) $cell->getValue());  // Mengambil nilai RAW
    }

    /**
     * Mendapatkan nilai yang sudah DIFORMAT sesuai tampilan di Excel
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $worksheet  Worksheet object
     * @param  int|string  $colIndex  Indeks kolom (1-based: 1 = A, 2 = B, atau string A/B/C)
     * @param  int  $rowIndex  Indeks baris
     * @return string Nilai yang sudah diformat dalam bentuk string yang di-trim
     */
    public static function getFormattedValueExcel($worksheet, $colIndex, $rowIndex)
    {
        $columnLetter = is_int($colIndex) ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) : $colIndex;
        $cellAddress = $columnLetter.$rowIndex;
        $cell = $worksheet->getCell($cellAddress);

        return trim((string) $cell->getFormattedValue());  // Mengambil nilai yang sudah diformat
    }
}
