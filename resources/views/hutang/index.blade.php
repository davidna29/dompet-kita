@extends('layouts.app')

@section('title', 'Hutang & Piutang')

@section('content')
    <div class="space-y-5">
        <header class="page-head">
            <div>
                <h1 class="page-title">Hutang &amp; Piutang</h1>
                <p class="page-sub">Catatan pinjaman keluarga. Transaksi baru tercatat saat pembayaran dikonfirmasi, bukan saat catatan dibuat.</p>
            </div>
            <a href="{{ route('hutang.create') }}" class="btn btn-primary btn-sm self-start">
                <x-icon name="plus" class="size-4" />
                Catat baru
            </a>
        </header>

        <section class="grid grid-cols-3 divide-x divide-line rounded-[14px] border border-line bg-paper">
            <div class="px-4 py-3">
                <p class="label">Sisa hutang</p>
                <p class="amount mt-1 text-lg text-tomato">{{ rupiah($totalHutang) }}</p>
            </div>
            <div class="px-4 py-3">
                <p class="label">Sisa piutang</p>
                <p class="amount mt-1 text-lg text-sage">{{ rupiah($totalPiutang) }}</p>
            </div>
            <div class="px-4 py-3">
                <p class="label">Jatuh tempo 14 hari</p>
                <p class="amount mt-1 text-lg text-ochre">{{ $jatuhTempoDekat }}</p>
            </div>
        </section>

        <div class="flex flex-wrap gap-1.5">
            @php $filterBase = ['status' => $status]; @endphp
            <a href="{{ route('hutang.index', array_filter($filterBase)) }}" class="chip {{ $jenis === null ? 'chip-aktif' : '' }}">Semua</a>
            @foreach(['hutang' => 'Hutang', 'piutang' => 'Piutang'] as $kunci => $label)
                <a href="{{ route('hutang.index', array_filter(['jenis' => $kunci, 'status' => $status])) }}" class="chip {{ $jenis === $kunci ? 'chip-aktif' : '' }}">{{ $label }}</a>
            @endforeach
            <span class="mx-1 w-px bg-line"></span>
            <a href="{{ route('hutang.index', array_filter(['jenis' => $jenis])) }}" class="chip {{ $status === null ? 'chip-aktif' : '' }}">Semua status</a>
            @foreach(['belum' => 'Belum lunas', 'lunas' => 'Lunas'] as $kunci => $label)
                <a href="{{ route('hutang.index', array_filter(['jenis' => $jenis, 'status' => $kunci])) }}" class="chip {{ $status === $kunci ? 'chip-aktif' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if($daftar->isEmpty())
            <div class="paper empty">
                <span class="empty__icon"><x-icon name="arrow-down-left" class="size-6" /></span>
                <h2 class="mt-4 font-display text-xl font-semibold">Belum ada catatan</h2>
                <p class="mt-1 max-w-[40ch] text-sm text-muted">Catat pinjaman atau piutang supaya sisa dan jatuh temponya terlihat di papan.</p>
                <a href="{{ route('hutang.create') }}" class="btn btn-primary btn-sm mt-4"><x-icon name="plus" class="size-4" /> Catat baru</a>
            </div>
        @else
            <section class="paper overflow-hidden">
                <div class="border-b border-line px-4 py-3.5 sm:px-5">
                    <h2 class="font-display text-base font-semibold">Daftar catatan</h2>
                    <p class="mt-0.5 text-[11px] text-muted">Bayar sebagian atau lunas dari halaman detail.</p>
                </div>

                <div class="divide-y divide-line">
                    @foreach($daftar as $item)
                        @php
                            $terbayar = max(0, (int) $item->nominal - (int) $item->sisa);
                            $persen = $item->nominal > 0 ? (int) round(($terbayar / $item->nominal) * 100) : 0;
                        @endphp
                        <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:px-5">
                            <a href="{{ route('hutang.show', $item) }}" class="flex min-w-0 flex-1 items-start gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $item->jenis === 'hutang' ? 'bg-tomato-soft text-tomato' : 'bg-sage-soft text-sage' }}">
                                    <x-icon :name="$item->jenis === 'hutang' ? 'arrow-down-left' : 'arrow-up-right'" class="size-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-sm font-bold">{{ $item->pihak }}</h3>
                                        <span class="tag {{ $item->jenis === 'hutang' ? 'tag--keluar' : 'tag--masuk' }}">{{ ucfirst($item->jenis) }}</span>
                                        @if($item->status === 'lunas')
                                            <span class="tag tag--netral">Lunas</span>
                                        @elseif($item->jatuh_tempo !== null && $item->jatuh_tempo->lt(today()))
                                            <span class="tag bg-ochre-soft text-ochre">Terlambat</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-[11px] text-muted">
                                        {{ tanggal_id($item->tanggal) }}
                                        @if($item->jatuh_tempo) · jatuh tempo {{ tanggal_id($item->jatuh_tempo) }} @endif
                                        @if($item->keterangan) · {{ $item->keterangan }} @endif
                                    </p>
                                    <div class="mt-2 flex max-w-64 items-center gap-2">
                                        <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-canvas">
                                            <span class="block h-full rounded-full {{ $item->jenis === 'hutang' ? 'bg-tomato' : 'bg-sage' }}" style="width: {{ min(100, $persen) }}%"></span>
                                        </span>
                                        <span class="font-mono text-[10px] text-muted">{{ $persen }}%</span>
                                    </div>
                                </div>
                            </a>

                            <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end sm:gap-1">
                                <div class="text-right">
                                    <p class="amount text-base">{{ rupiah($item->sisa) }}</p>
                                    <p class="text-[10px] text-muted">dari {{ rupiah($item->nominal) }}</p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('hutang.show', $item) }}" class="link-aksi">Detail</a>
                                    <x-hapus-form
                                        :action="route('hutang.destroy', $item)"
                                        title="Arsipkan catatan {{ $item->pihak }}?"
                                        description="Catatan disembunyikan dari daftar. Transaksi pembayaran yang sudah tercatat tetap tersimpan."
                                        label="Arsipkan"
                                        confirm="Ya, arsipkan"
                                        buttonClass="btn btn-ghost btn-sm"
                                    />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <p class="text-center text-[11px] text-muted">Hutang dan piutang tidak dihitung sebagai belanja atau pemasukan rumah tangga; hanya perpindahan uang yang tercatat sebagai transaksi.</p>
    </div>
@endsection
