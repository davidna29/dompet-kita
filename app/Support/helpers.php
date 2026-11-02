<?php

use Carbon\Carbon;

if (! function_exists('rupiah')) {
    function rupiah(int|float|string|null $jumlah, bool $dengan_prefix = true): string
    {
        $angka = (int) round((float) $jumlah);
        $teks = number_format(abs($angka), 0, ',', '.');

        if ($angka < 0) {
            $teks = '−'.$teks;
        }

        return $dengan_prefix ? 'Rp'.$teks : $teks;
    }
}

if (! function_exists('tanggal_id')) {
    function tanggal_id(Carbon|string|null $tanggal, bool $dengan_tahun = true): string
    {
        if ($tanggal === null) {
            return '—';
        }

        $carbon = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $teks = $carbon->day.' '.$bulan[(int) $carbon->month];

        return $dengan_tahun ? $teks.' '.$carbon->year : $teks;
    }
}

if (! function_exists('bulan_id')) {
    function bulan_id(Carbon|string|null $tanggal): string
    {
        if ($tanggal === null) {
            return '—';
        }

        $carbon = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $bulan[(int) $carbon->month].' '.$carbon->year;
    }
}

if (! function_exists('bulan_singkat')) {
    function bulan_singkat(Carbon|string|null $tanggal): string
    {
        if ($tanggal === null) {
            return '—';
        }

        $carbon = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][(int) $carbon->month - 1];
    }
}

if (! function_exists('hari_id')) {
    function hari_id(Carbon|string|null $tanggal): string
    {
        if ($tanggal === null) {
            return '—';
        }

        $carbon = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][(int) $carbon->dayOfWeek];
    }
}

if (! function_exists('inisial')) {
    function inisial(?string $nama): string
    {
        $nama = trim((string) $nama);
        if ($nama === '') {
            return '?';
        }

        $bagian = preg_split('/\s+/', $nama) ?: [];

        return mb_strtoupper(mb_substr($bagian[0], 0, 1).(isset($bagian[1]) ? mb_substr($bagian[1], 0, 1) : ''));
    }
}

if (! function_exists('angka_input')) {
    function angka_input(int|string|null $jumlah): string
    {
        return number_format((int) $jumlah, 0, ',', '.');
    }
}

if (! function_exists('waktu_api')) {
    function waktu_api(?Carbon $waktu = null): string
    {
        return ($waktu ?? now())->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:s');
    }
}
