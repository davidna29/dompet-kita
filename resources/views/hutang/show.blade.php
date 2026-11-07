@extends('layouts.app')

@section('title', 'Detail hutang')

@section('content')
    @php
        $terbayar = max(0, (int) $hutang->nominal - (int) $hutang->sisa);
        $persen = $hutang->nominal > 0 ? (int) round(($terbayar / $hutang->nominal) * 100) : 0;
        $lunas = $hutang->status === 'lunas';
    @endphp

    <div class="mx-auto max-w-2xl space-y-5">
        <header class="page-head">
            <div>
                <a href="{{ route('hutang.index') }}" class="mb-2 inline-flex min-h-11 items-center gap-1.5 text-xs font-bold text-muted hover:text-ink">
                    <x-icon name="arrow-left" class="size-3.5" />
                    Kembali ke daftar
                </a>
                <h1 class="page-title">{{ $hutang->pihak }}</h1>
                <p class="page-sub">
                    {{ ucfirst($hutang->jenis) }} dicatat {{ tanggal_id($hutang->tanggal) }}
                    @if($hutang->jatuh_tempo) · jatuh tempo {{ tanggal_id($hutang->jatuh_tempo) }} @endif
                </p>
            </div>
            <x-hapus-form
                :action="route('hutang.destroy', $hutang)"
                title="Arsipkan catatan {{ $hutang->pihak }}?"
                description="Catatan disembunyikan dari daftar. Transaksi pembayaran yang sudah tercatat tetap tersimpan."
                label="Arsipkan"
                confirm="Ya, arsipkan"
                buttonClass="btn btn-ghost btn-sm self-start"
            />
        </header>

        <section class="paper-raised p-5 sm:p-6">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="label">Sisa {{ $hutang->jenis }}</p>
                    <p class="amount mt-1 text-2xl sm:text-3xl">{{ rupiah($hutang->sisa) }}</p>
                </div>
                <p class="text-right text-xs text-muted">dari {{ rupiah($hutang->nominal) }}</p>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <span class="h-2 flex-1 overflow-hidden rounded-full bg-canvas">
                    <span class="block h-full rounded-full {{ $hutang->jenis === 'hutang' ? 'bg-tomato' : 'bg-sage' }}" style="width: {{ min(100, $persen) }}%"></span>
                </span>
                <span class="font-mono text-[10px] text-muted">{{ $persen }}%</span>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="tag {{ $hutang->jenis === 'hutang' ? 'tag--keluar' : 'tag--masuk' }}">{{ ucfirst($hutang->jenis) }}</span>
                @if($lunas)
                    <span class="tag tag--netral">Lunas</span>
                @elseif($hutang->jatuh_tempo !== null && $hutang->jatuh_tempo->lt(today()))
                    <span class="tag bg-ochre-soft text-ochre">Terlambat</span>
                @endif
                <span class="text-[11px] text-muted">Terbayar {{ rupiah($terbayar) }}</span>
            </div>

            @if($hutang->keterangan)
                <p class="mt-4 rounded-xl bg-canvas px-3 py-2.5 text-sm leading-relaxed text-muted">{{ $hutang->keterangan }}</p>
            @endif
        </section>

        @unless($lunas)
            <section class="paper p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold">{{ $hutang->jenis === 'hutang' ? 'Catat pembayaran' : 'Catat penerimaan' }}</h2>
                <p class="mt-1 text-sm text-muted">Transaksi dibuat saat disimpan. Konfirmasi ulang dengan tanggal dan jumlah yang sama tidak membuat baris ganda.</p>

                <form method="POST" action="{{ route('hutang.bayar', $hutang) }}" class="mt-4 space-y-3">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="jumlah" class="label">Jumlah</label>
                            <input id="jumlah" name="jumlah" type="text" inputmode="numeric" value="{{ old('jumlah') }}" onblur="this.value = window.formatRupiahValue(this.value)" required class="input mt-1.5" placeholder="Contoh: 250.000">
                            @error('jumlah') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tanggal" class="label">Tanggal</label>
                            <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', today()->toDateString()) }}" class="input mt-1.5">
                            @error('tanggal') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="metode" class="label">Kantong <span class="normal-case">(opsional)</span></label>
                            <select id="metode" name="metode" class="input mt-1.5">
                                <option value="">Belum dipilih</option>
                                @foreach($kantong as $k)
                                    <option value="{{ $k->nama }}" @selected(old('metode') === $k->nama)>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                            @error('metode') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="keterangan" class="label">Keterangan <span class="normal-case">(opsional)</span></label>
                            <input id="keterangan" name="keterangan" type="text" value="{{ old('keterangan') }}" maxlength="5000" class="input mt-1.5">
                            @error('keterangan') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-full sm:w-auto">
                        <x-icon name="check" class="size-4" />
                        Simpan {{ $hutang->jenis === 'hutang' ? 'pembayaran' : 'penerimaan' }}
                    </button>
                </form>
            </section>
        @endunless

        <section class="paper overflow-hidden">
            <div class="border-b border-line px-4 py-3.5 sm:px-5">
                <h2 class="font-display text-base font-semibold">Riwayat transaksi</h2>
                <p class="mt-0.5 text-[11px] text-muted">Baris ini juga muncul di daftar transaksi dan laporan.</p>
            </div>

            @if($pembayaran->isEmpty())
                <div class="px-4 py-8 text-center">
                    <p class="text-sm font-semibold">Belum ada transaksi.</p>
                    <p class="mt-1 text-xs text-muted">Transaksi tercatat saat pembayaran atau penerimaan disimpan.</p>
                </div>
            @else
                <div class="divide-y divide-line">
                    @foreach($pembayaran as $item)
                        <a href="{{ route('transaksi.edit', $item) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-olive-soft/50 sm:px-5">
                            <span class="grid size-9 shrink-0 place-items-center rounded-lg {{ $hutang->jenis === 'hutang' ? 'bg-tomato-soft text-tomato' : 'bg-sage-soft text-sage' }}">
                                <x-icon name="receipt" class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold">{{ tanggal_id($item->tanggal) }}</span>
                                <span class="mt-0.5 block truncate text-[11px] text-muted">
                                    {{ $item->keterangan ?: ($hutang->jenis === 'hutang' ? 'Pembayaran' : 'Penerimaan') }}
                                    @if($item->metode) · {{ $item->metode }} @endif
                                    · {{ $item->pencatat }}
                                </span>
                            </span>
                            <span class="amount shrink-0 text-sm">{{ rupiah($item->jumlah) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <p class="text-center text-[11px] text-muted">Hutang dan piutang tidak masuk total belanja atau pemasukan; saldo kantong tetap mengikuti transaksi yang tercatat.</p>
    </div>
@endsection
