<?php

$dir = __DIR__.'/../public/icons';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

function roundedRect(GdImage $image, int $x, int $y, int $w, int $h, int $r, int $color): void
{
    imagefilledrectangle($image, $x + $r, $y, $x + $w - $r, $y + $h, $color);
    imagefilledrectangle($image, $x, $y + $r, $x + $w, $y + $h - $r, $color);
    imagefilledellipse($image, $x + $r, $y + $r, $r * 2, $r * 2, $color);
    imagefilledellipse($image, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
    imagefilledellipse($image, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
    imagefilledellipse($image, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
}

function warna(GdImage $image, string $hex): int
{
    $hex = ltrim($hex, '#');

    return imagecolorallocate(
        $image,
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    );
}

function buatIkon(int $ukuran): GdImage
{
    $gambar = imagecreatetruecolor($ukuran, $ukuran);
    imageantialias($gambar, true);
    imagealphablending($gambar, false);
    imagesavealpha($gambar, true);
    $transparan = imagecolorallocatealpha($gambar, 0, 0, 0, 127);
    imagefilledrectangle($gambar, 0, 0, $ukuran, $ukuran, $transparan);

    $skala = $ukuran / 512;
    $olive = warna($gambar, '#3D4A2E');
    $oliveDalam = warna($gambar, '#2C3621');
    $kertas = warna($gambar, '#FBF8EF');
    $tomato = warna($gambar, '#A83B24');
    $butter = warna($gambar, '#E8C36A');

    roundedRect($gambar, (int) (16 * $skala), (int) (16 * $skala), (int) (480 * $skala), (int) (480 * $skala), (int) (112 * $skala), $olive);
    roundedRect($gambar, (int) (48 * $skala), (int) (48 * $skala), (int) (416 * $skala), (int) (416 * $skala), (int) (86 * $skala), $oliveDalam);

    foreach ([150, 240, 330] as $indeks => $y) {
        $lebar = $indeks === 1 ? 220 : 290;
        roundedRect($gambar, (int) (112 * $skala), (int) ($y * $skala), (int) ($lebar * $skala), (int) (26 * $skala), (int) (13 * $skala), $kertas);
    }

    imagefilledellipse($gambar, (int) (372 * $skala), (int) (354 * $skala), (int) (96 * $skala), (int) (96 * $skala), $tomato);
    imagefilledellipse($gambar, (int) (372 * $skala), (int) (354 * $skala), (int) (46 * $skala), (int) (46 * $skala), $butter);

    return $gambar;
}

$besar = buatIkon(512);
imagepng($besar, $dir.'/icon-512.png');
imagepng(imagescale($besar, 192, 192, IMG_BICUBIC), $dir.'/icon-192.png');
imagepng(imagescale($besar, 180, 180, IMG_BICUBIC), $dir.'/apple-touch-icon.png');
imagepng(imagescale($besar, 32, 32, IMG_BICUBIC), $dir.'/favicon-32.png');

echo "Ikon dibuat.\n";
