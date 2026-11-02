<?php

namespace App\Support;

class Icon
{
    public const PATHS = [
        'home' => '<path d="M3.5 10.5 12 3l8.5 7.5"/><path d="M5.5 9.5V21h13V9.5"/><path d="M9.5 21v-6h5v6"/>',
        'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'wallet' => '<path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H20v14H6.5A2.5 2.5 0 0 1 4 16.5z"/><path d="M4 8h16"/><path d="M16 12h4"/>',
        'settings' => '<path d="M4 6h16M4 12h16M4 18h16"/><circle cx="9" cy="6" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="8" cy="18" r="2"/>',
        'filter' => '<path d="M3 5h18l-7 8v6l-4 2v-8z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'check' => '<path d="m5 12 5 5L20 7"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'trash' => '<path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 14h10l1-14"/><path d="M9 7V4h6v3"/>',
        'edit' => '<path d="M4 20h4L20 8l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
        'arrow-right' => '<path d="M4 12h16M14 6l6 6-6 6"/>',
        'arrow-left' => '<path d="M20 12H4M10 18l-6-6 6-6"/>',
        'arrow-up-right' => '<path d="M7 17 17 7M7 7h10v10"/>',
        'arrow-down-left' => '<path d="M17 7 7 17M17 17H7V7"/>',
        'download' => '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 21h16"/>',
        'upload' => '<path d="M12 21V9M7 14l5-5 5 5"/><path d="M4 3h16"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon' => '<path d="M20 14.5A8.5 8.5 0 0 1 9.5 4 8.5 8.5 0 1 0 20 14.5Z"/>',
        'logout' => '<path d="M10 4H5v16h5"/><path d="M14 8l4 4-4 4M18 12H9"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'alert' => '<path d="M12 4 2.5 20h19z"/><path d="M12 9v5M12 17h.01"/>',
        'repeat' => '<path d="M17 2l4 4-4 4"/><path d="M3 11V9a3 3 0 0 1 3-3h15"/><path d="M7 22l-4-4 4-4"/><path d="M21 13v2a3 3 0 0 1-3 3H3"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'pie' => '<path d="M21 12A9 9 0 1 1 12 3v9z"/><path d="M15 3.5A9 9 0 0 1 20.5 9H15z"/>',
        'receipt' => '<path d="M5 3h14v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6M9 16h3"/>',
        'pin' => '<path d="M9 4h6l-1 6 3 3H7l3-3z"/><path d="M12 13v8"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="7.5" r="1"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'refresh' => '<path d="M20 11a8 8 0 0 0-14.5-4L3 10"/><path d="M3 4v6h6"/><path d="M4 13a8 8 0 0 0 14.5 4L21 14"/><path d="M21 20v-6h-6"/>',
        'eye' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
        'eye-off' => '<path d="M3 3l18 18"/><path d="M10.6 6.2A10.5 10.5 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-3.1 3.8M6.2 6.2A17 17 0 0 0 2 12s3.5 6 10 6a10 10 0 0 0 4.1-.8"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
        'circle-check' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>',
        'wifi-off' => '<path d="M3 3l18 18"/><path d="M8.5 8.5A11 11 0 0 1 21 9M3 9a15 15 0 0 1 3.4-1.9"/><path d="M6.5 12.5A10 10 0 0 1 12 11c1 0 2 .1 2.9.4M9.5 16a5 5 0 0 1 5 0"/><circle cx="12" cy="19" r="1"/>',
        'utensils' => '<path d="M4 3v7a2 2 0 0 0 2 2h1V3M6 12v9M15 3v18M15 3c2.2 0 3 2 3 5s-.8 5-3 5"/>',
        'cart' => '<path d="M3 4h2l2.5 11h11L21 7H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
        'fuel' => '<path d="M4 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16M3 21h12"/><path d="M6 8h6M14 7h2a2 2 0 0 1 2 2v6a2 2 0 0 0 4 0V9l-3-3"/>',
        'bolt' => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
        'heart' => '<path d="M20.8 5.7a5.5 5.5 0 0 0-7.8 0L12 6.8l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 22l8.8-8.5a5.5 5.5 0 0 0 0-7.8Z"/>',
        'book' => '<path d="M4 4h7a3 3 0 0 1 3 3v14a3 3 0 0 0-3-3H4z"/><path d="M20 4h-3a3 3 0 0 0-3 3v14a3 3 0 0 1 3-3h3z"/>',
        'car' => '<path d="M5 17h14v-4l-2-5H7l-2 5z"/><path d="M3 17v3h3M18 17v3h3"/><circle cx="8" cy="17" r="1.5"/><circle cx="16" cy="17" r="1.5"/>',
        'gift' => '<rect x="3" y="9" width="18" height="12" rx="1"/><path d="M12 9v12M3 13h18"/><path d="M12 9H8.5a2.5 2.5 0 1 1 0-5C12 4 12 9 12 9ZM12 9h3.5a2.5 2.5 0 1 0 0-5C12 4 12 9 12 9Z"/>',
        'wifi' => '<path d="M2.5 9a15 15 0 0 1 19 0M5.5 12.5a10 10 0 0 1 13 0M9 16a5 5 0 0 1 6 0"/><circle cx="12" cy="19" r="1"/>',
        'droplet' => '<path d="M12 2.5S5 9.5 5 14a7 7 0 0 0 14 0c0-4.5-7-11.5-7-11.5Z"/>',
        'shirt' => '<path d="M8 3 4 6l3 4 2-1v12h6V9l2 1 3-4-4-3-2 2h-2z"/>',
        'pill' => '<path d="M8 16 16 8a4 4 0 0 1 5.7 5.7l-8 8A4 4 0 0 1 8 16Z"/><path d="m11 11 6 6"/>',
        'plane' => '<path d="m3 11 18-7-7 18-2-7z"/><path d="m14 13 4-4"/>',
        'paw' => '<circle cx="6" cy="9" r="2"/><circle cx="10" cy="5" r="2"/><circle cx="15" cy="6" r="2"/><circle cx="18" cy="10" r="2"/><path d="M7 17c0-3 2.5-5 5-5s5 2 5 5-2 4-5 4-5-1-5-4Z"/>',
        'baby' => '<circle cx="12" cy="9" r="5"/><path d="M9 21c0-3 1-5 3-5s3 2 3 5M9 8h.01M15 8h.01M10 11c1 1 3 1 4 0"/>',
        'coffee' => '<path d="M4 8h13v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5z"/><path d="M17 9h2a3 3 0 0 1 0 6h-2M4 22h13"/>',
        'tools' => '<path d="m14 6 4 4M4 20l6-6M14 4l6 6-4 4-6-6zM4 14l6 6-2 2H2v-6z"/>',
        'phone' => '<rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/>',
        'bank' => '<path d="M3 9 12 3l9 6M4 21h16M6 9v9M10 9v9M14 9v9M18 9v9"/>',
        'cash' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 10v4M18 10v4"/>',
        'shopping-bag' => '<path d="M6 8V6a6 6 0 0 1 12 0v2"/><path d="M4 8h16l-1 13H5z"/>',
        'graduation-cap' => '<path d="m2 9 10-5 10 5-10 5z"/><path d="M6 11v5c3 3 9 3 12 0v-5M22 9v6"/>',
        'music' => '<path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/>',
        'dumbbell' => '<path d="M6 8v8M3 10v4M18 8v8M21 10v4M6 12h12"/>',
        'leaf' => '<path d="M20 4C10 4 4 9 4 16c0 2 1 4 1 4s2-7 9-9c-4 4-6 7-6 9 6 0 12-4 12-16Z"/>',
        'stethoscope' => '<path d="M6 3v6a5 5 0 0 0 10 0V3M6 6H4M16 6h2"/><path d="M14 14v2a4 4 0 0 0 4 4h1"/><circle cx="20" cy="20" r="1"/>',
        'bus' => '<rect x="4" y="3" width="16" height="15" rx="2"/><path d="M4 11h16M8 18v2M16 18v2"/><circle cx="8" cy="15" r="1"/><circle cx="16" cy="15" r="1"/>',
        'ticket' => '<path d="M3 8a2 2 0 0 0 2-2h14a2 2 0 0 0 2 2v8a2 2 0 0 0-2 2H5a2 2 0 0 0-2-2z"/><path d="M13 6v12"/>',
        'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.4 2.4-3-3z"/>',
        'piggy-bank' => '<path d="M4 12a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2a6 6 0 0 1-6 6H9l-2 2v-3a6 6 0 0 1-3-5z"/><path d="M20 12h2M8 9h.01M9 17v2"/>',
        'sparkles' => '<path d="m12 3 1.2 3.8L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.2z"/><path d="m19 14 .8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8zM5 15l.6 1.4L7 17l-1.4.6L5 19l-.6-1.4L3 17l1.4-.6z"/>',
    ];

    public static function svg(string $name, string $class = 'size-5', float $stroke = 1.7): string
    {
        $body = self::PATHS[$name] ?? self::PATHS['tag'];

        return '<svg class="'.e($class).'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="'.$stroke.'" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$body.'</svg>';
    }

    public static function ada(string $name): bool
    {
        return array_key_exists($name, self::PATHS);
    }

    public static function untukKategori(?string $kategori): string
    {
        return match (mb_strtolower((string) $kategori)) {
            'makanan', 'makan', 'dapur' => 'utensils',
            'belanja', 'belanja bulanan' => 'cart',
            'transportasi', 'transport', 'bensin' => 'car',
            'tagihan', 'listrik' => 'bolt',
            'kesehatan' => 'heart',
            'pendidikan', 'sekolah' => 'book',
            'rumah', 'sewa' => 'home',
            'hadiah', 'donasi' => 'gift',
            'internet', 'pulsa' => 'wifi',
            'air' => 'droplet',
            'pakaian' => 'shirt',
            'obat' => 'pill',
            'liburan' => 'plane',
            'hewan' => 'paw',
            'anak' => 'baby',
            'kopi', 'jajan' => 'coffee',
            'perbaikan' => 'wrench',
            'gaji', 'pemasukan' => 'bank',
            'mutasi' => 'repeat',
            'tabungan' => 'piggy-bank',
            default => 'tag',
        };
    }
}
