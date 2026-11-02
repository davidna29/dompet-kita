@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
    <div class="space-y-5">
        <header class="page-head">
            <div>
                <h1 class="page-title">Pengaturan</h1>
                <p class="page-sub">Akun, tampilan, integrasi Hermes, dan data keluarga. Rahasia tidak pernah ditampilkan di layar ini.</p>
            </div>
        </header>

        @if($demoMode)
            <div class="flex items-start gap-3 rounded-xl border border-butter/60 bg-butter-soft px-4 py-3 text-sm text-ink">
                <x-icon name="info" class="mt-0.5 size-5 shrink-0 text-ochre" />
                <div>
                    <p class="font-bold">Mode data contoh aktif.</p>
                    <p class="mt-0.5 text-xs leading-relaxed">Transaksi di papan adalah data sintetis untuk pengembangan, bukan riwayat keluarga asli. Matikan <span class="font-mono">DEMO_MODE</span> dan impor CSV asli sebelum dipakai.</p>
                </div>
            </div>
        @endif

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="paper p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold">Akun</h2>
                        <p class="mt-1 text-sm text-muted">Pencatat diisi otomatis dari akun yang masuk.</p>
                    </div>
                    <span class="grid size-10 place-items-center rounded-full bg-olive-soft font-mono text-xs font-semibold text-olive-deep">{{ inisial(auth()->user()->name) }}</span>
                </div>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-3 border-b border-line pb-2">
                        <dt class="text-muted">Nama</dt>
                        <dd class="text-right font-semibold">{{ auth()->user()->name }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3 border-b border-line pb-2">
                        <dt class="text-muted">Pencatat</dt>
                        <dd class="text-right font-semibold">{{ auth()->user()->pencatat }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-muted">Email</dt>
                        <dd class="text-right font-mono text-[11px]">{{ auth()->user()->email }}</dd>
                    </div>
                </dl>
                <form method="POST" action="{{ route('logout') }}" class="mt-5 border-t border-line pt-4">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm"><x-icon name="logout" class="size-4" /> Keluar dari akun</button>
                </form>
            </article>

            <article class="paper p-5">
                <h2 class="font-display text-lg font-semibold">Tampilan</h2>
                <p class="mt-1 text-sm text-muted">Papan mengikuti preferensi perangkat, lalu pilihan yang tersimpan di browser ini.</p>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <button type="button" data-theme-toggle class="btn btn-soft btn-sm w-fit">
                        <x-icon name="sun" class="theme-icon-sun size-4" />
                        <x-icon name="moon" class="theme-icon-moon size-4" />
                        <span data-theme-label>Ganti mode</span>
                    </button>
                    <span class="text-[11px] text-muted">Tersimpan lokal · tidak mengubah data.</span>
                </div>

                <div class="mt-6 border-t border-line pt-4">
                    <h3 class="text-sm font-bold">Pasang di layar utama</h3>
                    <p class="mt-1 text-xs leading-relaxed text-muted">PWA menyimpan aset statis agar papan terasa seperti aplikasi. Data transaksi tetap online-only dan tidak disimpan di perangkat.</p>
                    <button type="button" data-install-app hidden class="btn btn-primary btn-sm mt-3"><x-icon name="download" class="size-4" /> Pasang Dompet Kita</button>
                    <p class="mt-2 text-[11px] text-muted">Di iPhone, gunakan menu Bagikan → Tambah ke Layar Utama.</p>
                </div>
            </article>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.1fr_.9fr]">
            <article class="paper p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold">Integrasi Hermes</h2>
                        <p class="mt-1 text-sm text-muted">STB memakai token admin terpisah dari sesi UI.</p>
                    </div>
                    <span class="tag {{ $adminTokenSiap ? 'tag--masuk' : 'tag--keluar' }}">{{ $adminTokenSiap ? 'Token siap' : 'Token kosong' }}</span>
                </div>

                <div class="mt-4 rounded-xl border border-line bg-canvas px-3 py-2.5">
                    <p class="label">Base URL</p>
                    <p class="mt-1 break-all font-mono text-[11px]">{{ $baseUrl }}</p>
                </div>

                <div class="mt-4 space-y-2 text-xs">
                    @foreach([
                        ['GET', '/health', 'Cek koneksi dan waktu server'],
                        ['GET', '/transaksi?since=', 'Tarik perubahan, termasuk soft-deleted'],
                        ['POST', '/transaksi', 'Kirim catatan Telegram, idempoten'],
                        ['PUT', '/transaksi/{id}', 'Koreksi lewat chat'],
                        ['DELETE', '/transaksi/{id}', 'Soft delete lewat chat'],
                        ['GET', '/kantong', 'Saldo dihitung server'],
                        ['GET', '/pengingat', 'Jadwal dan jatuh tempo'],
                    ] as [$metode, $path, $ket])
                        <div class="flex items-start gap-2 border-b border-line/70 pb-2 last:border-b-0 last:pb-0">
                            <span class="tag tag--netral shrink-0">{{ $metode }}</span>
                            <span class="min-w-0">
                                <span class="block font-mono text-[10px] text-ink">{{ $path }}</span>
                                <span class="block text-[10px] text-muted">{{ $ket }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
                @unless($adminTokenSiap)
                    <p class="mt-3 rounded-lg bg-tomato-soft px-3 py-2 text-[11px] leading-relaxed text-tomato">ADMIN_TOKEN belum diisi di environment. API akan menolak semua permintaan sampai token dibuat dan diisi.</p>
                @endunless
            </article>

            <div class="space-y-4">
                <article class="paper p-5">
                    <h2 class="font-display text-lg font-semibold">Data</h2>
                    <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="label">Transaksi</dt><dd class="amount mt-1">{{ $jumlahTransaksi }}</dd></div>
                        <div><dt class="label">Kantong</dt><dd class="amount mt-1">{{ $jumlahKantong }}</dd></div>
                        <div><dt class="label">Jadwal aktif</dt><dd class="amount mt-1">{{ $jumlahJadwal }}</dd></div>
                        <div><dt class="label">Anggaran bulan ini</dt><dd class="amount mt-1">{{ $jumlahAnggaran }}</dd></div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-line pt-4">
                        <a href="{{ route('transaksi.ekspor', ['semua' => 1]) }}" class="btn btn-ghost btn-sm"><x-icon name="download" class="size-4" /> Ekspor semua CSV</a>
                        <a href="{{ route('transaksi.index', ['semua' => 1]) }}" class="btn btn-ghost btn-sm"><x-icon name="list" class="size-4" /> Semua transaksi</a>
                    </div>
                </article>

                <article class="paper p-5">
                    <h2 class="font-display text-lg font-semibold">Jadwal laporan Hermes</h2>
                    <div class="mt-3 divide-y divide-line text-sm">
                        <div class="flex items-center justify-between gap-3 py-2"><span>Harian</span><span class="font-mono text-xs">21:30 WIB</span></div>
                        <div class="flex items-center justify-between gap-3 py-2"><span>Mingguan</span><span class="font-mono text-xs">Minggu 19:00</span></div>
                        <div class="flex items-center justify-between gap-3 py-2"><span>Bulanan</span><span class="font-mono text-xs">Tanggal 1, 09:00</span></div>
                    </div>
                    <p class="mt-3 text-[11px] leading-relaxed text-muted">Pull wajib sebelum laporan. Perubahan UI sebelum jadwal tersebut ikut terhitung.</p>
                </article>
            </div>
        </section>

        <section class="paper p-5">
            <h2 class="font-display text-lg font-semibold">Papan lain</h2>
            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                @foreach([
                    ['route' => 'anggaran.index', 'ikon' => 'target', 'label' => 'Anggaran', 'ket' => 'Batas belanja bulanan'],
                    ['route' => 'berulang.index', 'ikon' => 'repeat', 'label' => 'Berulang', 'ket' => 'Tagihan dan pengingat'],
                    ['route' => 'kategori.index', 'ikon' => 'tag', 'label' => 'Kategori', 'ket' => 'Ikon dan warna'],
                ] as $tautan)
                    <a href="{{ route($tautan['route']) }}" class="flex min-h-14 items-center gap-3 rounded-xl border border-line bg-canvas/60 px-3 py-2.5 hover:border-line-strong hover:bg-olive-soft/50">
                        <span class="grid size-8 place-items-center rounded-lg bg-paper text-muted"><x-icon :name="$tautan['ikon']" class="size-4" /></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold">{{ $tautan['label'] }}</span>
                            <span class="block text-[11px] text-muted">{{ $tautan['ket'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        <p class="pb-2 text-center text-[11px] leading-relaxed text-muted">Dompet Kita · Laravel {{ app()->version() }} · Zona waktu Asia/Jakarta. Tidak ada data transaksi yang disimpan di perangkat.</p>
    </div>
@endsection
