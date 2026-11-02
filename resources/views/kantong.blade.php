@extends('layouts.app')

@section('title', 'Kantong')

@section('content')
    @php
        $warnaKantong = ['Tunai' => '#4F6B4A', 'BSI' => '#496976', 'Shopee' => '#87570F'];
        $ikonKantong = ['Tunai' => 'cash', 'BSI' => 'bank', 'Shopee' => 'shopping-bag'];
        $hasilRekonsiliasi = collect($rekonsiliasi ?? [])->keyBy('id');
    @endphp

    <div class="space-y-5">
        <header class="page-head">
            <div>
                <h1 class="page-title">Kantong</h1>
                <p class="page-sub">Saldo dihitung server dari transaksi tidak terhapus. Mutasi ikut dihitung di sini, tetapi tidak masuk total belanja.</p>
            </div>
            <div class="flex flex-wrap gap-2 self-start">
                <a href="#atur-saldo-awal" onclick="document.querySelector('#atur-saldo-awal details').open = true" class="btn btn-ghost btn-sm">
                    <x-icon name="settings" class="size-4" />
                    Atur saldo awal
                </a>
                <a href="{{ route('transaksi.create') }}" class="btn btn-primary btn-sm">
                    <x-icon name="plus" class="size-4" />
                    Tambah transaksi
                </a>
            </div>
        </header>

        <section class="paper-raised p-5 sm:p-6">
            <div class="flex flex-wrap items-end justify-between gap-4 border-b border-line pb-4">
                <div>
                    <h2 class="font-display text-lg font-semibold">Saldo semua kantong</h2>
                    <p class="mt-1 text-xs text-muted">Tunai · BSI · Shopee · sampai hari ini</p>
                </div>
                <p class="amount text-2xl sm:text-3xl">{{ rupiah($total) }}</p>
            </div>

            <div class="divide-y divide-line">
                @foreach($kantong as $k)
                    <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl" style="background: {{ $warnaKantong[$k['nama']] ?? '#496976' }}1f; color: {{ $warnaKantong[$k['nama']] ?? '#496976' }}">
                                <x-icon :name="$ikonKantong[$k['nama']] ?? 'wallet'" class="size-5" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold">{{ $k['nama'] }}</h3>
                                    <span class="label !text-[9px]">sejak {{ tanggal_id($k['tanggal_saldo_awal']) }}</span>
                                </div>
                                <p class="mt-1 text-[11px] leading-relaxed text-muted">
                                    Awal {{ rupiah($k['saldo_awal']) }} + masuk {{ rupiah($k['masuk']) }} − keluar {{ rupiah($k['keluar']) }} + mutasi {{ rupiah($k['terima_mutasi']) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end sm:gap-1">
                            <p class="amount text-lg">{{ rupiah($k['saldo']) }}</p>
                            <a href="{{ route('transaksi.index', ['metode' => $k['nama'], 'semua' => 1]) }}" class="link-aksi">Lihat transaksi</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 grid gap-2 border-t border-line pt-4 text-[11px] text-muted sm:grid-cols-2">
                <p class="flex items-start gap-2"><x-icon name="info" class="mt-0.5 size-3.5 shrink-0" /> Mutasi mengurangi kantong asal dan menambah kantong tujuan.</p>
                <p class="flex items-start gap-2"><x-icon name="info" class="mt-0.5 size-3.5 shrink-0" /> Transaksi tanpa kantong tidak masuk saldo mana pun.</p>
            </div>
        </section>

        <section id="atur-saldo-awal" class="paper overflow-hidden">
            <details class="group">
                <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 sm:px-5">
                    <span class="flex items-center gap-2">
                        <x-icon name="settings" class="size-4 text-muted" />
                        <span>
                            <span class="block text-sm font-bold">Atur saldo awal</span>
                            <span class="block text-[11px] text-muted">Dipakai sebelum migrasi CSV dan saat memulai pembukuan.</span>
                        </span>
                    </span>
                    <x-icon name="chevron-down" class="size-4 text-muted transition-transform group-open:rotate-180" />
                </summary>
                <form method="POST" action="{{ route('kantong.saldo-awal') }}" class="border-t border-line px-4 py-4 sm:px-5">
                    @csrf
                    <div class="space-y-4">
                        @foreach($kantong as $k)
                            <div class="grid gap-2 sm:grid-cols-[120px_1fr_180px] sm:items-end">
                                <p class="text-sm font-semibold sm:pb-2.5">{{ $k['nama'] }}</p>
                                <div>
                                    <label for="awal-{{ $k['id'] }}" class="label">Saldo awal</label>
                                    <input id="awal-{{ $k['id'] }}" name="saldo_awal[{{ $k['id'] }}]" type="text" inputmode="numeric" value="{{ angka_input($k['saldo_awal']) }}" onblur="this.value = window.formatRupiahValue(this.value)" class="input mt-1.5" required>
                                </div>
                                <div>
                                    <label for="tanggal-awal-{{ $k['id'] }}" class="label">Dihitung sejak</label>
                                    <input id="tanggal-awal-{{ $k['id'] }}" name="tanggal_saldo_awal[{{ $k['id'] }}]" type="date" value="{{ $k['tanggal_saldo_awal'] }}" class="input mt-1.5" required>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('saldo_awal') <p class="mt-2 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    @error('saldo_awal.*') <p class="mt-2 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    <p class="mt-3 text-[11px] leading-relaxed text-muted">Transaksi sebelum tanggal ini tidak dihitung ke saldo. Saldo awal tidak mengubah riwayat transaksi.</p>
                    <button type="submit" class="btn btn-primary btn-sm mt-3">Simpan saldo awal</button>
                </form>
            </details>
        </section>

        @if($tanpaKantong['jumlah'] > 0 || $metodeTidakTerdaftar->isNotEmpty())
            <section class="space-y-3">
                @if($tanpaKantong['jumlah'] > 0)
                    <div class="flex flex-col gap-2 rounded-xl border border-ochre/40 bg-ochre-soft px-4 py-3 text-sm text-ink sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-2">
                            <x-icon name="alert" class="mt-0.5 size-5 shrink-0 text-ochre" />
                            <p><span class="font-bold">{{ $tanpaKantong['jumlah'] }} transaksi</span> belum punya kantong (total {{ rupiah($tanpaKantong['total']) }}). Saldo kantong tidak menghitungnya.</p>
                        </div>
                        <a href="{{ route('transaksi.index', ['metode' => '__none__', 'semua' => 1]) }}" class="btn btn-ghost btn-sm shrink-0">Pilih kantong</a>
                    </div>
                @endif

                @if($metodeTidakTerdaftar->isNotEmpty())
                    <div class="rounded-xl border border-line bg-paper px-4 py-3 text-sm">
                        <p class="flex items-center gap-2 font-bold"><x-icon name="alert" class="size-4 text-ochre" /> Metode di luar kantong terdaftar</p>
                        <p class="mt-1 text-xs leading-relaxed text-muted">Baris ini tetap tersimpan, tetapi tidak masuk saldo karena tidak ada kantong dengan nama tersebut.</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($metodeTidakTerdaftar as $metode)
                                <a href="{{ route('transaksi.index', ['metode' => $metode->metode, 'semua' => 1]) }}" class="chip">
                                    {{ $metode->metode }} · {{ $metode->jumlah }} baris
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endif

        <section class="grid gap-4 lg:grid-cols-[1.1fr_.9fr]">
            <article class="paper overflow-hidden">
                <div class="flex items-start justify-between gap-3 border-b border-line px-4 py-3.5 sm:px-5">
                    <div>
                        <h2 class="font-display text-base font-semibold">Mutasi terakhir</h2>
                        <p class="mt-0.5 text-[11px] text-muted">Pindah antar kantong, bukan belanja.</p>
                    </div>
                    <span class="tag tag--mutasi">Bukan belanja</span>
                </div>

                @if($mutasi->isEmpty())
                    <div class="px-4 py-10 text-center">
                        <p class="text-sm font-semibold">Belum ada mutasi.</p>
                        <p class="mt-1 text-xs text-muted">Catat tarik tunai atau pindah saldo sebagai kategori Mutasi.</p>
                    </div>
                @else
                    <div class="divide-y divide-line">
                        @foreach($mutasi as $item)
                            <a href="{{ route('transaksi.edit', $item) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-olive-soft/50 sm:px-5">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-ochre-soft text-ochre">
                                    <x-icon name="repeat" class="size-4" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-1.5 text-sm font-semibold">
                                        {{ $item->metode ?: 'Tanpa asal' }}
                                        <x-icon name="arrow-right" class="size-3.5 text-ochre" />
                                        {{ $item->kantong_tujuan ?: 'Tanpa tujuan' }}
                                    </span>
                                    <span class="mt-0.5 block truncate text-[11px] text-muted">{{ tanggal_id($item->tanggal) }} · {{ $item->keterangan ?: 'Tanpa keterangan' }}</span>
                                </span>
                                <span class="amount shrink-0 text-sm text-ochre">{{ rupiah($item->jumlah) }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="paper p-4 sm:p-5">
                <div>
                    <h2 class="font-display text-lg font-semibold">Cocokkan saldo</h2>
                    <p class="mt-1 text-sm leading-relaxed text-muted">Masukkan saldo nyata di tiap kantong. Selisih hanya ditampilkan; tidak ada penyesuaian otomatis yang dicatat.</p>
                </div>

                <form method="POST" action="{{ route('kantong.rekonsiliasi') }}" class="mt-4 space-y-3">
                    @csrf
                    @foreach($kantong as $k)
                        @php $hasilBaris = $hasilRekonsiliasi->get($k['id']); @endphp
                        <div>
                            <label for="saldo-{{ $k['id'] }}" class="label">{{ $k['nama'] }} — saldo nyata</label>
                            <input id="saldo-{{ $k['id'] }}" name="saldo[{{ $k['id'] }}]" type="text" inputmode="numeric" autocomplete="off"
                                   value="{{ $hasilBaris['saldo_nyata'] ?? '' }}"
                                   onblur="this.value = window.formatRupiahValue(this.value)"
                                   class="input mt-1.5" placeholder="Contoh: 1.250.000">
                        </div>
                    @endforeach
                    @error('saldo.*') <p class="text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    <button type="submit" class="btn btn-primary btn-sm w-full">
                        <x-icon name="refresh" class="size-4" />
                        Hitung selisih
                    </button>
                </form>

                @if($rekonsiliasi)
                    <div class="mt-5 border-t border-line pt-4">
                        <p class="label">Hasil perbandingan</p>
                        <div class="mt-2 divide-y divide-line">
                            @foreach($rekonsiliasi as $row)
                                <div class="flex items-center justify-between gap-3 py-2.5">
                                    <div>
                                        <p class="text-sm font-semibold">{{ $row['nama'] }}</p>
                                        <p class="text-[10px] text-muted">Sistem {{ rupiah($row['saldo']) }}</p>
                                    </div>
                                    <div class="text-right">
                                        @if($row['saldo_nyata'] === null)
                                            <span class="text-xs text-muted">Belum diisi</span>
                                        @elseif($row['selisih'] === 0)
                                            <span class="tag tag--masuk">Cocok</span>
                                        @elseif($row['selisih'] > 0)
                                            <span class="text-xs font-bold text-ochre">Sistem lebih besar {{ rupiah($row['selisih']) }}</span>
                                        @else
                                            <span class="text-xs font-bold text-tomato">Sistem lebih kecil {{ rupiah(abs($row['selisih'])) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-3 rounded-lg bg-canvas px-3 py-2 text-[11px] leading-relaxed text-muted">Tambahkan transaksi yang terlewat ke kantong yang tepat, lalu cocokkan lagi. Selisih tidak disimpan sebagai koreksi.</p>
                    </div>
                @endif
            </article>
        </section>

        <p class="text-center text-[11px] text-muted">Saldo memakai tanggal saldo awal tiap kantong. Transaksi sebelum tanggal itu tidak dihitung.</p>
    </div>
@endsection
