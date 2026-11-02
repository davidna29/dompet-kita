@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    @php
        $namaDepan = explode(' ', auth()->user()->name ?? 'Tamu')[0];
        $maxTren = max(1, collect($tren)->max('total') ?? 1);
        $warnaHex = ['Tunai' => '#4F6B4A', 'BSI' => '#496976', 'Shopee' => '#87570F'];
        $labelStatus = ['aman' => 'Aman', 'hampir' => 'Hampir habis', 'lewat' => 'Lewat'];
    @endphp

    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="page-title">{{ $sapaan }}, {{ $namaDepan }}.</h1>
                </div>
                <p class="page-sub">{{ hari_id(today()) }}, {{ tanggal_id(today()) }} · papan {{ bulan_id($bulan) }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center rounded-xl border border-line bg-paper p-0.5">
                    <a href="{{ route('beranda', ['bulan' => $bulanSebelum]) }}" class="grid size-11 place-items-center rounded-lg text-muted hover:bg-olive-soft hover:text-ink" aria-label="Bulan sebelumnya">
                        <x-icon name="chevron-left" class="size-4" />
                    </a>
                    <span class="px-2 font-mono text-[10px] font-medium uppercase tracking-[0.12em] text-ink">{{ bulan_id($bulan) }}</span>
                    @if($bulanBerjalan)
                        <span class="grid size-9 place-items-center text-line" aria-hidden="true"><x-icon name="pin" class="size-4" /></span>
                    @else
                        <a href="{{ route('beranda', ['bulan' => $bulanBerikut]) }}" class="grid size-11 place-items-center rounded-lg text-muted hover:bg-olive-soft hover:text-ink" aria-label="Bulan berikutnya">
                            <x-icon name="chevron-right" class="size-4" />
                        </a>
                    @endif
                </div>
                <a href="{{ route('transaksi.create') }}" class="btn btn-primary hidden sm:inline-flex">
                    <x-icon name="plus" class="size-4" />
                    Tambah
                </a>
            </div>
        </header>

        <section class="grid gap-4 lg:grid-cols-[1.45fr_.55fr]">
            <article class="paper-raised relative p-5 sm:p-6">
                @if($bulanBerjalan)
                    <span class="pin absolute -top-1.5 right-8 pin-press" aria-hidden="true"></span>
                @endif

                <div class="flex items-start justify-between gap-4 border-b border-line pb-4">
                    <div>
                        <h2 class="font-display text-lg font-semibold tracking-[-0.02em]">Papan bulan ini</h2>
                        <p class="mt-1 text-xs text-muted">Pengeluaran belanja, tanpa Mutasi.</p>
                    </div>
                    <span class="month-tab {{ $bulanBerjalan ? 'month-tab--aktif' : '' }}">{{ strtoupper(bulan_singkat($bulan)) }} {{ $bulan->year }}</span>
                </div>

                <div class="pt-5">
                    <div class="flex flex-wrap items-end justify-between gap-x-4 gap-y-2">
                        <div>
                            <p class="text-xs font-semibold text-muted">Pengeluaran</p>
                            <p class="amount mt-1 text-[clamp(2.35rem,9vw,4rem)] leading-[0.95] {{ $transaksiBaruId ? 'total-press' : '' }}">{{ rupiah($ringkasan['keluar']) }}</p>
                        </div>
                        <div class="pb-1 text-right">
                            @if($perbandingan['persen'] === null)
                                <p class="text-xs text-muted">Belum ada pembanding</p>
                            @elseif($perbandingan['persen'] === 0)
                                <p class="text-xs font-semibold text-muted">Sama dengan bulan lalu</p>
                                <p class="mt-1 text-[10px] text-muted">Bulan lalu {{ rupiah($perbandingan['sebelumnya']) }}</p>
                            @else
                                <p class="flex items-center justify-end gap-1 text-xs font-semibold {{ $perbandingan['persen'] > 0 ? 'text-tomato' : 'text-sage' }}">
                                    <x-icon :name="$perbandingan['persen'] > 0 ? 'arrow-up-right' : 'arrow-down-left'" class="size-3.5" />
                                    {{ abs($perbandingan['persen']) }}% dari bulan lalu
                                </p>
                                <p class="mt-1 text-[10px] text-muted">Bulan lalu {{ rupiah($perbandingan['sebelumnya']) }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-3 divide-x divide-line border-t border-line">
                        <div class="py-3 pr-3">
                            <p class="label">Masuk</p>
                            <p class="amount mt-1 text-sm text-sage sm:text-base">{{ rupiah($ringkasan['masuk']) }}</p>
                        </div>
                        <div class="px-3 py-3">
                            <p class="label">Selisih</p>
                            <p class="amount mt-1 text-sm sm:text-base">{{ rupiah($ringkasan['selisih']) }}</p>
                        </div>
                        <div class="px-3 py-3">
                            <p class="label">Mutasi</p>
                            <p class="amount mt-1 text-sm text-ochre sm:text-base">{{ rupiah($ringkasan['mutasi']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 border-t border-line pt-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="label">Enam bulan terakhir</p>
                        <p class="text-[10px] text-muted">Pengeluaran non-Mutasi</p>
                    </div>
                    <div class="mt-3 flex h-24 items-end gap-2" role="img" aria-label="Grafik pengeluaran enam bulan terakhir">
                        @foreach($tren as $index => $titik)
                            @php
                                $tinggi = max(7, (int) round(($titik['total'] / $maxTren) * 100));
                                $aktif = $index === count($tren) - 1;
                            @endphp
                            <div class="flex h-full min-w-0 flex-1 flex-col justify-end gap-1.5" title="{{ $titik['label_panjang'] }}: {{ rupiah($titik['total']) }}">
                                <div class="w-full rounded-t-[4px] {{ $aktif ? 'bg-tomato' : 'bg-olive/45' }}" style="height: {{ $tinggi }}%"></div>
                                <span class="text-center font-mono text-[9px] {{ $aktif ? 'font-semibold text-ink' : 'text-muted' }}">{{ $titik['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                <article class="paper p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display text-base font-semibold">Kantong</h2>
                            <p class="mt-0.5 text-[11px] text-muted">Termasuk Mutasi</p>
                        </div>
                        <a href="{{ route('kantong') }}" class="link-aksi">Lihat</a>
                    </div>
                    <p class="amount mt-3 text-xl">{{ rupiah($totalKantong) }}</p>
                    <div class="mt-3 divide-y divide-line border-t border-line">
                        @foreach($kantong as $k)
                            <a href="{{ route('transaksi.index', ['metode' => $k['nama'], 'semua' => 1]) }}" class="flex items-center justify-between gap-3 py-2">
                                <span class="flex min-w-0 items-center gap-2 text-xs font-semibold text-muted">
                                    <span class="size-1.5 shrink-0 rounded-full" style="background: {{ $warnaHex[$k['nama']] ?? '#496976' }}"></span>
                                    <span class="truncate">{{ $k['nama'] }}</span>
                                </span>
                                <span class="amount shrink-0 text-xs">{{ rupiah($k['saldo']) }}</span>
                            </a>
                        @endforeach
                    </div>
                    @if($tanpaKantong['jumlah'] > 0)
                        <a href="{{ route('transaksi.index', ['metode' => '__none__', 'semua' => 1]) }}" class="mt-3 flex items-start gap-2 rounded-lg bg-ochre-soft px-2.5 py-2 text-[11px] leading-snug text-ink">
                            <x-icon name="alert" class="mt-0.5 size-3.5 shrink-0 text-ochre" />
                            <span>{{ $tanpaKantong['jumlah'] }} transaksi belum punya kantong. Pilih kantong agar saldo akurat.</span>
                        </a>
                    @endif
                </article>

                <article class="paper p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display text-base font-semibold">Anggaran</h2>
                            <p class="mt-0.5 text-[11px] text-muted">{{ $anggaran['dipinjam'] ? 'Meneruskan bulan lalu' : 'Bulan ini' }}</p>
                        </div>
                        <a href="{{ route('anggaran.index') }}" class="link-aksi">Atur</a>
                    </div>

                    @php $alerts = collect($anggaran['rows'])->where('status', '!=', 'aman')->sortByDesc('persen')->take(2); @endphp

                    @if($alerts->isNotEmpty())
                        <div class="mt-3 space-y-3">
                            @foreach($alerts as $row)
                                <a href="{{ route('anggaran.index', ['periode' => $bulan->format('Y-m')]) }}" class="block">
                                    <div class="flex items-center justify-between gap-2 text-xs">
                                        <span class="truncate font-semibold">{{ $row['kategori'] }}</span>
                                        <span class="shrink-0 font-mono text-[10px] {{ $row['status'] === 'lewat' ? 'text-tomato' : 'text-ochre' }}">{{ $row['persen'] }}%</span>
                                    </div>
                                    <div class="progress mt-1.5">
                                        <div class="progress__bar {{ $row['status'] === 'lewat' ? 'progress__bar--lewat' : ($row['status'] === 'hampir' ? 'progress__bar--hampir' : '') }}" style="width: {{ min(100, $row['persen']) }}%"></div>
                                    </div>
                                    <p class="mt-1 text-[10px] text-muted">{{ $row['sisa'] >= 0 ? 'Sisa '.rupiah($row['sisa']) : 'Lewat '.rupiah(abs($row['sisa'])) }}</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-4 flex items-start gap-2 rounded-lg bg-sage-soft px-3 py-2.5 text-xs text-sage">
                            <x-icon name="circle-check" class="mt-0.5 size-4 shrink-0" />
                            <span>Semua kategori masih di bawah ambang. Total anggaran {{ rupiah($anggaran['total_anggaran']) }}.</span>
                        </div>
                    @endif

                    <div class="mt-4 border-t border-line pt-3">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-muted">Total terpakai</span>
                            <span class="amount">{{ rupiah($anggaran['total_terpakai']) }}</span>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.45fr_.55fr]">
            <article class="paper overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3.5 sm:px-5">
                    <div>
                        <h2 class="font-display text-base font-semibold">Transaksi terakhir</h2>
                        <p class="mt-0.5 text-[11px] text-muted">Ketuk untuk melihat atau mengoreksi.</p>
                    </div>
                    <a href="{{ route('transaksi.index', ['semua' => 1]) }}" class="link-aksi">Lihat semua</a>
                </div>

                @if($transaksi->isEmpty())
                    <div class="empty">
                        <span class="empty__icon"><x-icon name="receipt" class="size-6" /></span>
                        <h3 class="mt-4 font-display text-lg font-semibold">Papan masih kosong</h3>
                        <p class="mt-1 max-w-[36ch] text-sm text-muted">Tempel transaksi pertama untuk mulai melihat ke mana uang berjalan.</p>
                        <a href="{{ route('transaksi.create') }}" class="btn btn-primary mt-4"><x-icon name="plus" class="size-4" /> Tambah transaksi</a>
                    </div>
                @else
                    @foreach($transaksi->groupBy(fn ($t) => $t->tanggal->toDateString()) as $tanggal => $kelompok)
                        <div class="flex gap-3 border-b border-line last:border-b-0">
                            <div class="w-11 shrink-0 border-r border-line/70 px-1 py-3 text-center">
                                <p class="amount text-base leading-none">{{ $kelompok->first()->tanggal->day }}</p>
                                <p class="mt-1 font-mono text-[9px] uppercase tracking-[0.08em] text-muted">{{ $kelompok->first()->tanggal->translatedFormat('M') }}</p>
                            </div>
                            <div class="min-w-0 flex-1">
                                @foreach($kelompok as $t)
                                    @php
                                        $meta = $kategoriMeta[$t->kategori] ?? null;
                                        $warna = $meta->warna ?? '#496976';
                                        $ikon = $meta->ikon ?? \App\Support\Icon::untukKategori($t->kategori);
                                    @endphp
                                    <a href="{{ route('transaksi.edit', $t) }}" class="slip {{ $transaksiBaruId === $t->id ? 'slip--baru' : '' }}">
                                        <span class="grid size-8 shrink-0 place-items-center rounded-lg" style="background: {{ $warna }}1f; color: {{ $warna }}">
                                            <x-icon :name="$ikon" class="size-4" />
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="flex flex-wrap items-center gap-1.5">
                                                <span class="truncate text-sm font-bold">{{ $t->kategori }}</span>
                                                @if($t->is_mutasi)
                                                    <span class="tag tag--mutasi">Pindah kantong</span>
                                                @endif
                                            </span>
                                            <span class="mt-0.5 block truncate text-[11px] text-muted">
                                                @if($t->is_mutasi)
                                                    <span class="font-semibold text-ochre">{{ $t->metode ?: 'Tanpa asal' }} → {{ $t->kantong_tujuan ?: 'Tanpa tujuan' }}</span>
                                                    · {{ $t->keterangan ?: 'Tanpa keterangan' }}
                                                @else
                                                    {{ $t->keterangan ?: 'Tanpa keterangan' }}
                                                    @if($t->metode) · {{ $t->metode }} @endif
                                                @endif
                                            </span>
                                        </span>
                                        <span class="shrink-0 text-right">
                                            <span class="amount block text-sm {{ $t->is_mutasi ? 'text-ochre' : ($t->jenis === 'masuk' ? 'text-sage' : '') }}">{{ $t->jenis === 'masuk' ? '+' : '−' }}{{ rupiah($t->jumlah) }}</span>
                                            <span class="mt-0.5 block text-[9px] text-muted">{{ $t->pencatat }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </article>

            <aside class="space-y-4">
                <article class="paper overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3.5">
                        <div>
                            <h2 class="font-display text-base font-semibold">Tagihan dekat</h2>
                            <p class="mt-0.5 text-[11px] text-muted">Konfirmasi satu ketukan.</p>
                        </div>
                        <a href="{{ route('berulang.index') }}" class="link-aksi">Kelola</a>
                    </div>

                    @if($pengingat->isEmpty())
                        <div class="px-4 py-6 text-center">
                            <p class="text-sm font-semibold">Tidak ada tagihan dekat.</p>
                            <p class="mt-1 text-xs text-muted">Pengingat akan muncul di sini.</p>
                        </div>
                    @else
                        <div>
                            @foreach($pengingat->take(4) as $item)
                                <a href="{{ route('berulang.index') }}" class="flex items-center gap-3 border-b border-line/70 px-4 py-3 last:border-b-0 hover:bg-olive-soft/40">
                                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-canvas text-muted">
                                        <x-icon name="{{ $item->status === 'terlambat' ? 'alert' : 'calendar' }}" class="size-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold">{{ $item->nama }}</span>
                                        <span class="mt-0.5 block text-[11px] text-muted">
                                            {{ tanggal_id($item->jatuh_tempo, false) }}
                                            @if($item->status === 'terlambat') · terlambat @elseif($item->status === 'hari-ini') · hari ini @else · segera @endif
                                        </span>
                                    </span>
                                    <span class="amount shrink-0 text-xs">{{ rupiah($item->jumlah) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </article>

                <div class="paper p-4">
                    <h2 class="font-display text-base font-semibold">Papan lain</h2>
                    <div class="mt-3 divide-y divide-line">
                        @foreach([
                            ['route' => 'anggaran.index', 'ikon' => 'target', 'label' => 'Anggaran bulanan', 'ket' => 'Atur batas per kategori'],
                            ['route' => 'berulang.index', 'ikon' => 'repeat', 'label' => 'Tagihan berulang', 'ket' => 'Jadwal dan konfirmasi'],
                            ['route' => 'kategori.index', 'ikon' => 'tag', 'label' => 'Ikon kategori', 'ket' => 'Warna dan urutan'],
                        ] as $tautan)
                            <a href="{{ route($tautan['route']) }}" class="flex min-h-12 items-center gap-3 py-2.5 first:pt-0 last:pb-0 group">
                                <span class="grid size-8 place-items-center rounded-lg bg-canvas text-muted group-hover:text-olive">
                                    <x-icon :name="$tautan['ikon']" class="size-4" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold">{{ $tautan['label'] }}</span>
                                    <span class="block text-[11px] text-muted">{{ $tautan['ket'] }}</span>
                                </span>
                                <x-icon name="chevron-right" class="size-4 text-line-strong" />
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </section>
    </div>
@endsection
