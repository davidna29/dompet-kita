<?php

namespace Tests\Unit;

use App\Support\JumlahParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JumlahParserTest extends TestCase
{
    #[DataProvider('nilaiValid')]
    public function test_jumlah_valid_dinormalisasi(mixed $input, int $harapan): void
    {
        $this->assertSame($harapan, JumlahParser::parse($input));
    }

    public static function nilaiValid(): array
    {
        return [
            'digit polos' => [35000, 35000],
            'titik ribuan' => ['35.000', 35000],
            'rp dan spasi' => ['Rp 35.000', 35000],
            'suffix rb' => ['35rb', 35000],
            'suffix ribu' => ['35 ribu', 35000],
            'suffix juta desimal koma' => ['1,5jt', 1500000],
            'suffix juta desimal titik' => ['1.5jt', 1500000],
            'string integer' => ['35000', 35000],
            'kapital' => ['35RB', 35000],
        ];
    }

    #[DataProvider('nilaiTidakValid')]
    public function test_jumlah_tidak_valid_ditolak(mixed $input): void
    {
        $this->assertNull(JumlahParser::parse($input));
    }

    public static function nilaiTidakValid(): array
    {
        return [
            'kosong' => [''],
            'nol' => ['0'],
            'negatif' => ['-100'],
            'huruf' => ['abc'],
            'campuran aneh' => ['35x000'],
        ];
    }

    public function test_saldo_awal_nol_diterima_hanya_oleh_parse_non_negative(): void
    {
        $this->assertSame(0, JumlahParser::parseNonNegative('0'));
        $this->assertSame(0, JumlahParser::parseNonNegative('0.000'));
        $this->assertSame(1_500_000, JumlahParser::parseNonNegative('1.500.000'));
        $this->assertNull(JumlahParser::parseNonNegative(''));
        $this->assertNull(JumlahParser::parseNonNegative('abc'));
    }
}
