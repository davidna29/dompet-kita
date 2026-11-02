<?php

namespace App\Support;

class JumlahParser
{
    /**
     * Parse an Indonesian rupiah amount into plain positive integer digits.
     *
     * Accepts: 35000, "35.000", "Rp 35.000", "35rb", "35 ribu", "1,5jt".
     */
    public static function parse(mixed $input): ?int
    {
        if ($input === null) {
            return null;
        }

        if (is_int($input)) {
            return $input > 0 ? $input : null;
        }

        if (is_float($input)) {
            $hasil = (int) round($input);

            return $hasil > 0 ? $hasil : null;
        }

        $teks = trim((string) $input);
        if ($teks === '') {
            return null;
        }

        $teks = mb_strtolower($teks);
        $teks = str_replace(['rp.', 'rp', 'idr'], '', $teks);
        $teks = trim($teks);

        $pengali = 1;

        if (preg_match('/(juta|jt)$/u', $teks)) {
            $pengali = 1_000_000;
            $teks = preg_replace('/(juta|jt)$/u', '', $teks) ?? '';
        } elseif (preg_match('/(ribu|rb|k)$/u', $teks)) {
            $pengali = 1_000;
            $teks = preg_replace('/(ribu|rb|k)$/u', '', $teks) ?? '';
        }

        $teks = trim($teks);

        if (preg_match('/[^0-9.,\s]/u', $teks)) {
            return null;
        }

        if ($pengali > 1) {
            $teks = self::bacaDesimal($teks);
        } else {
            $teks = preg_replace('/[^0-9]/', '', $teks) ?? '';
        }

        if ($teks === '' || ! is_numeric($teks)) {
            return null;
        }

        $hasil = (int) round((float) $teks * $pengali);

        return $hasil > 0 ? $hasil : null;
    }

    private static function bacaDesimal(string $teks): string
    {
        $teks = str_replace(' ', '', $teks);

        $titik = substr_count($teks, '.');
        $koma = substr_count($teks, ',');

        if ($titik > 0 && $koma > 0) {
            $teks = str_replace('.', '', $teks);
            $teks = str_replace(',', '.', $teks);
        } elseif ($koma === 1) {
            $teks = str_replace(',', '.', $teks);
        } elseif ($titik === 1) {
            [$depan, $belakang] = explode('.', $teks, 2);
            if (strlen($belakang) > 2) {
                $teks = $depan.$belakang;
            }
        } elseif ($titik > 1) {
            $teks = str_replace('.', '', $teks);
        }

        return $teks;
    }

    /**
     * Saldo awal may legitimately be zero; JumlahParser::parse() rejects zero amounts.
     */
    public static function parseNonNegative(mixed $input): ?int
    {
        $teks = trim((string) $input);

        if ($teks === '') {
            return null;
        }

        if (preg_match('/^0+([.,]0+)?$/', $teks) === 1) {
            return 0;
        }

        return self::parse($input);
    }
}
