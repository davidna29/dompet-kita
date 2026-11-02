@extends('layouts.app')

@section('title', 'Ringkasan')

@section('content')
    @php
        $labelPeriode = match ($preset) {
            'hari' => tanggal_id($mulai),
            'minggu' => tanggal_id($mulai).' – '.tanggal_id($selesai),
            'rentang' => tanggal_id($mulai).' – '.tanggal_id($selesai),
            default => bulan_id($bulan),
        };
        $totalKategori = max(1, $ringkasan['keluar']);
        $labelsDonat = array_column($donat, 'label');
        $valuesDonat = array_column($donat, 'total');
        $colorsDonat = array_column($donat, 'warna');
        $labelsTren = array_column($tren, 'label');
        $valuesTren = array_column($tren, 'total');
        $labelsKumulatif = array_column($kumulatif, 'label');
        $valuesKumulatif = array_column($kumulatif, 'kumulatif');
    @endphp

    <div class="space-y-5">
        <header class="page-head">
            <div>
                <h1 class="page-title">Ringkasan</h1>
                <p class="page-sub">{{ $labelPeriode }} · total belanja tanpa Mutasi, angka yang sama dengan laporan Hermes.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('transaksi.ekspor', $filterExport) }}" class="btn btn-ghost btn-sm">
                    <x-icon name="download" class="size-4" />
                    Ekspor periode
                </a>
                <a href="{{ route('transaksi.index', ['dari' => $mulai->toDateString(), 'sampai' => $selesai->toDateString()]) }}" class="btn btn-primary btn-sm">
                    <x-icon name="list" class="size-4" />
                    Lihat baris
                </a>
            </div>
        </header>

        <section class="paper p-3 sm:p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-1.5">
                    @foreach(['hari' => 'Hari ini', 'minggu' => 'Minggu ini', 'bulan' => 'Bulan', 'rentang' => 'Rentang'] as $kunci => $label)
                        <a href="{{ route('ringkasan', array_filter(['periode' => $kunci, 'bulan' => $kunci === 'bulan' ? $bulan->format('Y-m') : null, 'dari' => $kunci === 'rentang' ? $mulai->toDateString() : null, 'sampai' => $kunci === 'rentang' ? $selesai->toDateString() : null])) }}"
                           class="chip {{ $preset === $kunci ? 'chip-aktif' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>

                @if($preset === 'bulan')
                    <div class="flex items-center rounded-xl border border-line bg-canvas p-0.5">
                        <a href="{{ route('ringkasan', ['periode' => 'bulan', 'bulan' => $bulanSebelum]) }}" class="grid size-11 place-items-center rounded-lg text-muted hover:bg-paper hover:text-ink" aria-label="Bulan sebelumnya">
                            <x-icon name="chevron-left" class="size-4" />
                        </a>
                        <span class="px-2 font-mono text-[10px] uppercase tracking-[0.1em]">{{ bulan_id($bulan) }}</span>
                        @if($bulanBerjalan)
                            <span class="grid size-9 place-items-center text-line"><x-icon name="pin" class="size-4" /></span>
                        @else
                            <a href="{{ route('ringkasan', ['periode' => 'bulan', 'bulan' => $bulanBerikut]) }}" class="grid size-11 place-items-center rounded-lg text-muted hover:bg-paper hover:text-ink" aria-label="Bulan berikutnya">
                                <x-icon name="chevron-right" class="size-4" />
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            @if($preset === 'rentang')
                <form method="GET" action="{{ route('ringkasan') }}" class="mt-3 grid gap-2 border-t border-line pt-3 sm:grid-cols-[1fr_1fr_auto]">
                    <input type="hidden" name="periode" value="rentang">
                    <div>
                        <label for="dari" class="label">Dari</label>
                        <input id="dari" name="dari" type="date" value="{{ $mulai->toDateString() }}" class="input mt-1.5">
                    </div>
                    <div>
                        <label for="sampai" class="label">Sampai</label>
                        <input id="sampai" name="sampai" type="date" value="{{ $selesai->toDateString() }}" class="input mt-1.5">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-primary btn-sm h-11 w-full sm:w-auto">Terapkan</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="grid grid-cols-2 divide-line rounded-[14px] border border-line bg-paper sm:grid-cols-4 sm:divide-x">
            <div class="border-b border-line px-4 py-3 sm:border-b-0">
                <p class="label">Masuk</p>
                <p class="amount mt-1 text-sm text-sage sm:text-base">{{ rupiah($ringkasan['masuk']) }}</p>
            </div>
            <div class="border-b border-line px-4 py-3 sm:border-b-0">
                <p class="label">Belanja</p>
                <p class="amount mt-1 text-sm sm:text-base">{{ rupiah($ringkasan['keluar']) }}</p>
            </div>
            <div class="border-b border-line px-4 py-3 sm:border-b-0">
                <p class="label">Mutasi</p>
                <p class="amount mt-1 text-sm text-ochre sm:text-base">{{ rupiah($ringkasan['mutasi']) }}</p>
            </div>
            <div class="px-4 py-3">
                <p class="label">Selisih</p>
                <p class="amount mt-1 text-sm sm:text-base">{{ rupiah($ringkasan['selisih']) }}</p>
            </div>
        </section>

        @if($perbandingan)
            <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-line bg-paper px-4 py-3">
                <p class="text-xs text-muted">Dibanding {{ bulan_id($perbandingan['bulan_sebelumnya']) }}</p>
                <p class="flex items-center gap-1.5 text-sm font-semibold {{ ($perbandingan['persen'] ?? 0) > 0 ? 'text-tomato' : 'text-sage' }}">
                    @if($perbandingan['persen'] === null)
                        Belum ada pembanding
                    @elseif($perbandingan['persen'] === 0)
                        Sama dengan bulan lalu
                    @else
                        <x-icon :name="$perbandingan['persen'] > 0 ? 'arrow-up-right' : 'arrow-down-left'" class="size-4" />
                        {{ abs($perbandingan['persen']) }}% {{ $perbandingan['persen'] > 0 ? 'lebih tinggi' : 'lebih rendah' }}
                    @endif
                </p>
            </div>
        @endif

        <section class="grid gap-4 lg:grid-cols-2">
            <article class="paper p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold">Ke mana uang pergi</h2>
                        <p class="mt-0.5 text-xs text-muted">Kategori belanja · Mutasi tidak dihitung.</p>
                    </div>
                    <div class="text-right">
                        <p class="label">Total belanja</p>
                        <p class="amount mt-1 text-sm">{{ rupiah($ringkasan['keluar']) }}</p>
                    </div>
                </div>
                @if(empty($donat))
                    <div class="py-12 text-center text-sm text-muted">Belum ada belanja di periode ini.</div>
                @else
                    <div class="mt-4 grid gap-3 sm:grid-cols-[160px_1fr] sm:items-center">
                        <div class="relative mx-auto h-40 w-40">
                            <canvas data-chart="donut" data-labels="{{ json_encode($labelsDonat) }}" data-values="{{ json_encode($valuesDonat) }}" data-colors="{{ json_encode($colorsDonat) }}" aria-label="Grafik donat pengeluaran per kategori" role="img"></canvas>
                        </div>
                        <div class="space-y-2">
                            @foreach($donat as $item)
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="size-2 shrink-0 rounded-full" style="background: {{ $item['warna'] }}"></span>
                                    <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                                    <span class="amount shrink-0">{{ rupiah($item['total']) }}</span>
                                    <span class="w-9 shrink-0 text-right font-mono text-[10px] text-muted">{{ round(($item['total'] / $totalKategori) * 100) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>

            <article class="paper p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold">Enam bulan</h2>
                        <p class="mt-0.5 text-xs text-muted">Belanja per bulan · {{ bulan_id($bulan) }} disorot.</p>
                    </div>
                    <span class="tag tag--netral">Tren</span>
                </div>
                <div class="mt-4 h-52">
                    <canvas data-chart="bars" data-labels="{{ json_encode($labelsTren) }}" data-values="{{ json_encode($valuesTren) }}" aria-label="Grafik batang pengeluaran enam bulan" role="img"></canvas>
                </div>
            </article>
        </section>

        <section class="paper p-4 sm:p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-lg font-semibold">Kumulatif harian</h2>
                    <p class="mt-0.5 text-xs text-muted">Belanja yang menumpuk dari {{ tanggal_id($mulai) }} sampai {{ tanggal_id($selesai) }}.</p>
                </div>
                <span class="tag tag--netral">{{ count($kumulatif) }} hari</span>
            </div>
            <div class="mt-4 h-52">
                <canvas data-chart="line" data-labels="{{ json_encode($labelsKumulatif) }}" data-values="{{ json_encode($valuesKumulatif) }}" aria-label="Grafik garis kumulatif belanja harian" role="img"></canvas>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-[1.2fr_.8fr]">
            <article class="paper overflow-hidden">
                <div class="border-b border-line px-4 py-3.5">
                    <h2 class="font-display text-base font-semibold">Rincian kategori</h2>
                    <p class="mt-0.5 text-[11px] text-muted">Semua kategori belanja di periode ini.</p>
                </div>
                @if(empty($ringkasan['per_kategori']))
                    <div class="px-4 py-10 text-center text-sm text-muted">Belum ada kategori yang bisa dirangkum.</div>
                @else
                    <div class="divide-y divide-line">
                        @foreach($ringkasan['per_kategori'] as $item)
                            @php
                                $meta = $kategoriMeta[$item['kategori']] ?? null;
                                $warna = $meta->warna ?? '#5F7F8E';
                                $ikon = $meta->ikon ?? \App\Support\Icon::untukKategori($item['kategori']);
                                $persen = round(($item['total'] / $totalKategori) * 100);
                            @endphp
                            <div class="flex items-center gap-3 px-4 py-3">
                                <span class="grid size-8 shrink-0 place-items-center rounded-lg" style="background: {{ $warna }}1f; color: {{ $warna }}">
                                    <x-icon :name="$ikon" class="size-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="truncate text-sm font-semibold">{{ $item['kategori'] }}</span>
                                        <span class="amount shrink-0 text-xs">{{ rupiah($item['total']) }}</span>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="progress flex-1"><div class="progress__bar" style="width: {{ min(100, $persen) }}%; background: {{ $warna }}"></div></div>
                                        <span class="w-8 text-right font-mono text-[10px] text-muted">{{ $persen }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>

            <div class="space-y-4">
                <article class="paper p-4 sm:p-5">
                    <h2 class="font-display text-base font-semibold">Per pencatat</h2>
                    <p class="mt-0.5 text-[11px] text-muted">Belanja non-Mutasi.</p>
                    <div class="mt-3 divide-y divide-line">
                        @forelse($ringkasan['per_pencatat'] as $item)
                            <div class="flex items-center justify-between gap-3 py-2.5">
                                <span class="text-sm">{{ $item['pencatat'] }}</span>
                                <span class="amount text-xs">{{ rupiah($item['total']) }}</span>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-muted">Belum ada data.</p>
                        @endforelse
                    </div>
                </article>

                <article class="paper p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display text-base font-semibold">Anggaran</h2>
                            <p class="mt-0.5 text-[11px] text-muted">{{ $anggaran['dipinjam'] ? 'Meneruskan bulan lalu' : 'Bulan ini' }}</p>
                        </div>
                        <a href="{{ route('anggaran.index', ['periode' => $bulan->format('Y-m')]) }}" class="link-aksi">Atur</a>
                    </div>
                    @if($anggaran['total_anggaran'] > 0)
                        <p class="amount mt-3 text-lg">{{ rupiah($anggaran['total_terpakai']) }} <span class="font-sans text-xs font-normal text-muted">dari {{ rupiah($anggaran['total_anggaran']) }}</span></p>
                        <div class="progress mt-2"><div class="progress__bar {{ $anggaran['total']['status'] === 'lewat' ? 'progress__bar--lewat' : ($anggaran['total']['status'] === 'hampir' ? 'progress__bar--hampir' : '') }}" style="width: {{ min(100, $anggaran['total']['persen']) }}%"></div></div>
                        <p class="mt-1.5 text-[11px] text-muted">{{ $anggaran['total']['persen'] }}% terpakai · {{ $anggaran['total']['sisa'] >= 0 ? 'sisa '.rupiah($anggaran['total']['sisa']) : 'lewat '.rupiah(abs($anggaran['total']['sisa'])) }}</p>
                    @else
                        <p class="mt-3 text-sm text-muted">Belum ada anggaran untuk bulan ini.</p>
                    @endif
                </article>
            </div>
        </section>
    </div>
@endsection
