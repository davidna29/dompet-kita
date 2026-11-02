@extends('layouts.app')

@section('title', $transaksi ? 'Ubah transaksi' : 'Tambah transaksi')

@section('content')
    @php
        $riwayatTerakhir = $transaksi?->riwayat->first();
    @endphp

    <div class="mx-auto max-w-3xl space-y-5" x-data="{ hapus: false }">
        <header class="page-head">
            <div>
                <a href="{{ route('transaksi.index') }}" class="mb-2 inline-flex items-center gap-1.5 text-xs font-bold text-muted hover:text-ink">
                    <x-icon name="arrow-left" class="size-3.5" />
                    Kembali ke transaksi
                </a>
                <h1 class="page-title">{{ $transaksi ? 'Ubah transaksi' : 'Tambah transaksi' }}</h1>
                <p class="page-sub">{{ $transaksi ? 'Koreksi baris yang sudah ada. Riwayat perubahan tetap tersimpan.' : 'Tempel catatan baru ke papan. Pencatat mengikuti akun yang sedang masuk.' }}</p>
            </div>
            @if($transaksi)
                <button type="button" x-on:click="hapus = true" class="btn btn-danger btn-sm self-start">
                    <x-icon name="trash" class="size-4" />
                    Hapus
                </button>
            @endif
        </header>

        @if($transaksi)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-paper px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="tag {{ $transaksi->is_mutasi ? 'tag--mutasi' : 'tag--netral' }}">{{ $transaksi->is_mutasi ? 'Mutasi' : 'Transaksi' }}</span>
                    <span class="text-xs text-muted">Sumber {{ $transaksi->sumber }}</span>
                    @if($transaksi->client_key)
                        <span class="font-mono text-[10px] text-muted">{{ $transaksi->client_key }}</span>
                    @endif
                </div>
                @if($riwayatTerakhir)
                    <p class="text-[11px] text-muted">
                        {{ $riwayatTerakhir->aksi === 'diubah' ? 'Terakhir diubah' : 'Dibuat' }} oleh
                        <span class="font-semibold text-ink">{{ $riwayatTerakhir->oleh ?? 'Hermes' }}</span>
                        pada {{ tanggal_id($riwayatTerakhir->created_at) }}, {{ $riwayatTerakhir->created_at->format('H:i') }} WIB
                    </p>
                @endif
            </div>

            <livewire:transaksi-form :transaksi-id="$transaksi->id" />
        @else
            <livewire:transaksi-form />
        @endif

        <p class="text-center text-[11px] leading-relaxed text-muted">
            Waktu perubahan ditetapkan server di Asia/Jakarta. Dua orang yang mengubah baris yang sama mengikuti aturan last-write-wins.
        </p>

        @if($transaksi)
            <div x-cloak x-show="hapus" x-transition.opacity.duration.200ms class="fixed inset-0 z-[80] flex items-end justify-center bg-black/45 p-3 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="judul-hapus">
                <div x-on:click.outside="hapus = false" class="w-full max-w-md rounded-2xl border border-line bg-paper-raised p-5 shadow-2xl">
                    <div class="flex items-start gap-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-tomato-soft text-tomato">
                            <x-icon name="alert" class="size-5" />
                        </span>
                        <div>
                            <h2 id="judul-hapus" class="font-display text-lg font-semibold">Hapus transaksi ini?</h2>
                            <p class="mt-1 text-sm leading-relaxed text-muted">Baris tidak dihapus permanen. STB tetap menerima tanda terhapus pada sinkronisasi berikutnya.</p>
                        </div>
                    </div>
                    <p class="mt-4 rounded-xl bg-canvas px-3 py-2 text-sm">
                        <span class="font-semibold">{{ $transaksi->kategori }}</span>
                        <span class="amount ml-2">{{ rupiah($transaksi->jumlah) }}</span>
                        <span class="block text-xs text-muted">{{ tanggal_id($transaksi->tanggal) }} · {{ $transaksi->keterangan ?: 'Tanpa keterangan' }}</span>
                    </p>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" x-on:click="hapus = false" class="btn btn-ghost btn-sm">Batal</button>
                        <form method="POST" action="{{ route('transaksi.destroy', $transaksi) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <x-icon name="trash" class="size-4" />
                                Ya, hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
