@php
    $pengguna = auth()->user();
    $nama = $pengguna?->name ?? 'Tamu';
    $pencatat = $pengguna?->pencatat ?? 'Tamu';
    $halaman = trim($__env->yieldContent('title', 'Beranda'));
    $navUtama = [
        ['route' => 'beranda', 'label' => 'Beranda', 'ikon' => 'home', 'aktif' => request()->routeIs('beranda')],
        ['route' => 'transaksi.index', 'label' => 'Transaksi', 'ikon' => 'list', 'aktif' => request()->routeIs('transaksi.*')],
        ['route' => 'ringkasan', 'label' => 'Ringkasan', 'ikon' => 'chart', 'aktif' => request()->routeIs('ringkasan')],
        ['route' => 'kantong', 'label' => 'Kantong', 'ikon' => 'wallet', 'aktif' => request()->routeIs('kantong')],
    ];
    $navLain = [
        ['route' => 'anggaran.index', 'label' => 'Anggaran', 'ikon' => 'target', 'aktif' => request()->routeIs('anggaran.*')],
        ['route' => 'berulang.index', 'label' => 'Berulang', 'ikon' => 'repeat', 'aktif' => request()->routeIs('berulang.*')],
        ['route' => 'hutang.index', 'label' => 'Hutang', 'ikon' => 'arrow-down-left', 'aktif' => request()->routeIs('hutang.*')],
        ['route' => 'kategori.index', 'label' => 'Kategori', 'ikon' => 'tag', 'aktif' => request()->routeIs('kategori.*')],
        ['route' => 'pengaturan', 'label' => 'Pengaturan', 'ikon' => 'settings', 'aktif' => request()->routeIs('pengaturan')],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" data-palette="olive">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f2edde" data-theme-color>
    <meta name="description" content="Dompet Kita — papan keuangan keluarga: catat, lihat, dan koreksi transaksi dari HP.">

    <title>{{ $halaman }} · Dompet Kita</title>

    <link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.webmanifest">

    <script>
        (function () {
            try {
                var palet = localStorage.getItem('dompet-palet');
                document.documentElement.dataset.palette = palet === 'rose' ? 'rose' : 'olive';

                var tema = localStorage.getItem('dompet-tema');
                if (tema === 'dark' || (tema === null && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-dvh bg-canvas text-ink">
    <div data-offline-banner class="offline-banner" hidden>
        <x-icon name="wifi-off" class="size-4" />
        <span>Kamu sedang offline. Data tidak disimpan di perangkat.</span>
    </div>

    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col bg-olive-deep text-shell-ink lg:flex">
        <div class="px-5 pb-5 pt-6">
            <x-logo />
            @if(config('app.demo_mode'))
                <span class="tag mt-3 bg-butter-soft text-ochre">Data contoh</span>
            @endif
        </div>

        <nav class="flex-1 overflow-y-auto px-3 pb-4" aria-label="Navigasi utama">
            <p class="label px-3 pb-2 pt-3 text-shell-ink/45">Papan</p>
            <div class="space-y-1">
                @foreach($navUtama as $item)
                    <a href="{{ route($item['route']) }}"
                       class="nav-link text-shell-ink/70 hover:bg-shell-soft hover:text-shell-ink {{ $item['aktif'] ? '!bg-shell-active !text-shell-ink' : '' }}"
                       @if($item['aktif']) aria-current="page" @endif>
                        <x-icon :name="$item['ikon']" class="size-[18px] shrink-0" />
                        <span>{{ $item['label'] }}</span>
                        <span class="nav-magnet"></span>
                    </a>
                @endforeach
            </div>

            <div class="px-3 pb-2 pt-6">
                <a href="{{ route('transaksi.create') }}" class="btn-add flex min-h-11 items-center justify-center gap-2 rounded-xl px-3 text-sm font-bold transition-colors hover:bg-action/85">
                    <x-icon name="plus" class="size-4" />
                    Tambah transaksi
                </a>
            </div>

            <p class="label px-3 pb-2 pt-5 text-shell-ink/45">Kelola</p>
            <div class="space-y-1">
                @foreach($navLain as $item)
                    <a href="{{ route($item['route']) }}"
                       class="nav-link text-shell-ink/70 hover:bg-shell-soft hover:text-shell-ink {{ $item['aktif'] ? '!bg-shell-active !text-shell-ink' : '' }}"
                       @if($item['aktif']) aria-current="page" @endif>
                        <x-icon :name="$item['ikon']" class="size-[18px] shrink-0" />
                        <span>{{ $item['label'] }}</span>
                        <span class="nav-magnet"></span>
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="border-t border-shell-line p-3">
            <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                <span class="grid size-9 place-items-center rounded-full bg-shell-soft font-mono text-xs font-semibold text-shell-ink">{{ inisial($nama) }}</span>
                <span class="min-w-0 flex-1 leading-tight">
                    <span class="block truncate text-sm font-bold">{{ $nama }}</span>
                    <span class="block truncate text-[11px] text-shell-muted">{{ $pencatat }}</span>
                </span>
                <button type="button" data-theme-toggle class="grid size-11 place-items-center rounded-lg text-shell-ink/70 hover:bg-shell-soft hover:text-shell-ink" aria-label="Ganti mode terang atau gelap">
                    <x-icon name="sun" class="theme-icon-sun size-4" />
                    <x-icon name="moon" class="theme-icon-moon size-4" />
                </button>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="flex min-h-10 w-full items-center gap-2 rounded-lg px-3 text-xs font-semibold text-shell-muted hover:bg-shell-soft hover:text-shell-ink">
                    <x-icon name="logout" class="size-4" />
                    Keluar dari akun
                </button>
            </form>
        </div>
    </aside>

    <header class="sticky top-0 z-30 bg-olive-deep text-shell-ink lg:hidden">
        @if(config('app.demo_mode'))
            <div class="bg-butter-soft px-4 py-1 text-center font-mono text-[9px] font-medium uppercase tracking-[0.14em] text-ochre">
                Data contoh · bukan riwayat keluarga asli
            </div>
        @endif
        <div class="flex h-16 items-center justify-between px-4">
            <x-logo compact />
            <div class="flex items-center gap-1">
                <button type="button" data-theme-toggle class="grid size-11 place-items-center rounded-xl text-shell-ink/75 hover:bg-shell-soft hover:text-shell-ink" aria-label="Ganti mode terang atau gelap">
                    <x-icon name="sun" class="theme-icon-sun size-5" />
                    <x-icon name="moon" class="theme-icon-moon size-5" />
                </button>
                <div x-data="{ buka: false }" class="relative">
                    <button type="button" x-on:click="buka = !buka" class="grid size-11 place-items-center rounded-xl text-shell-ink hover:bg-shell-soft" aria-label="Buka menu akun">
                        <span class="grid size-8 place-items-center rounded-full bg-shell-soft font-mono text-[10px] font-semibold">{{ inisial($nama) }}</span>
                    </button>
                    <div x-cloak x-show="buka" x-on:click.outside="buka = false" x-transition.opacity.duration.200ms
                         class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl border border-line bg-paper-raised p-1.5 text-ink shadow-xl">
                        <div class="border-b border-line px-3 py-2.5">
                            <p class="text-sm font-bold">{{ $nama }}</p>
                            <p class="text-xs text-muted">{{ $pencatat }}</p>
                            @if(config('app.demo_mode'))
                                <span class="tag mt-2 bg-butter-soft text-ochre">Data contoh</span>
                            @endif
                        </div>
                        @foreach($navLain as $item)
                            <a href="{{ route($item['route']) }}" class="flex min-h-11 items-center gap-2 rounded-lg px-3 text-sm font-semibold text-ink hover:bg-olive-soft">
                                <x-icon :name="$item['ikon']" class="size-4 text-muted" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-line pt-1">
                            @csrf
                            <button type="submit" class="flex min-h-11 w-full items-center gap-2 rounded-lg px-3 text-sm font-semibold text-tomato hover:bg-tomato-soft">
                                <x-icon name="logout" class="size-4" />
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="lg:pl-64">
        <div class="mx-auto w-full max-w-[1180px] px-4 pb-28 pt-5 sm:px-6 lg:px-10 lg:pb-16 lg:pt-8">
            @if(session('sukses'))
                <div class="toast fixed inset-x-4 top-4 z-[60] border-sage/40 bg-sage-soft text-sage sm:left-auto sm:w-[360px]" data-toast role="status">
                    <x-icon name="circle-check" class="mt-0.5 size-5 shrink-0" />
                    <p class="flex-1 leading-snug">{{ session('sukses') }}</p>
                    <button type="button" data-toast-close class="grid size-11 shrink-0 place-items-center rounded-md hover:bg-black/10" aria-label="Tutup notifikasi">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            @endif

            @if(session('gagal'))
                <div class="toast fixed inset-x-4 top-4 z-[60] border-tomato/40 bg-tomato-soft text-tomato sm:left-auto sm:w-[360px]" data-toast role="alert">
                    <x-icon name="alert" class="mt-0.5 size-5 shrink-0" />
                    <p class="flex-1 leading-snug">{{ session('gagal') }}</p>
                    <button type="button" data-toast-close class="grid size-11 shrink-0 place-items-center rounded-md hover:bg-black/10" aria-label="Tutup notifikasi">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-paper pb-[env(safe-area-inset-bottom)] lg:hidden" aria-label="Navigasi bawah">
        <div class="mx-auto grid max-w-lg grid-cols-5 items-end px-2">
            @foreach($navUtama as $index => $item)
                @if($index === 2)
                    <a href="{{ route('transaksi.create') }}" class="relative flex min-h-16 flex-col items-center justify-end pb-1.5 text-[10px] font-bold text-action" aria-label="Tambah transaksi">
                        <span class="btn-add shadow-action absolute -top-5 grid size-14 place-items-center rounded-full transition-transform active:scale-95">
                            <x-icon name="plus" class="size-6" />
                        </span>
                        <span class="mt-9">Tambah</span>
                    </a>
                @endif
                <a href="{{ route($item['route']) }}" class="bottom-link relative {{ $item['aktif'] ? 'bottom-link--aktif' : '' }}" @if($item['aktif']) aria-current="page" @endif>
                    <span class="bottom-dot"></span>
                    <x-icon :name="$item['ikon']" class="size-5" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    @livewireScripts
    @stack('scripts')
</body>
</html>
