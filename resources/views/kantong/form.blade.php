@extends('layouts.app')

@section('title', $kantong ? 'Ubah kantong' : 'Tambah kantong')

@section('content')
    <div class="mx-auto max-w-2xl space-y-5">
        <header class="page-head">
            <div>
                <a href="{{ route('kantong') }}" class="mb-2 inline-flex min-h-11 items-center gap-1.5 text-xs font-bold text-muted hover:text-ink">
                    <x-icon name="arrow-left" class="size-3.5" />
                    Kembali ke kantong
                </a>
                <h1 class="page-title">{{ $kantong ? 'Ubah kantong' : 'Tambah kantong' }}</h1>
                <p class="page-sub">
                    {{ $kantong
                        ? 'Nama baru diterapkan juga pada transaksi dan jadwal terkait. Riwayat perubahan tetap tercatat.'
                        : 'Saldo awal boleh nol. Masukkan saldo nyata dan tanggal hitungnya agar saldo kantong akurat.' }}
                </p>
            </div>
        </header>

        <form method="POST" action="{{ $kantong ? route('kantong.update', $kantong) : route('kantong.store') }}" class="space-y-4">
            @csrf
            @if($kantong)
                @method('PUT')
            @endif

            <section class="paper p-5 sm:p-6">
                <div>
                    <label for="nama" class="label">Nama kantong</label>
                    <input id="nama" name="nama" type="text" value="{{ old('nama', $kantong?->nama) }}" maxlength="30" required autofocus class="input mt-1.5" placeholder="Contoh: Tunai, BSI, Bank utama">
                    @error('nama') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label for="tipe" class="label">Tipe kantong</label>
                    <select id="tipe" name="tipe" class="input mt-1.5">
                        @foreach(['harian' => 'Harian', 'tabungan' => 'Tabungan', 'investasi' => 'Investasi'] as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('tipe', $kantong?->tipe ?? 'harian') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('tipe') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                @unless($kantong)
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="saldo_awal" class="label">Saldo awal</label>
                            <input id="saldo_awal" name="saldo_awal" type="text" inputmode="numeric" value="{{ old('saldo_awal', '0') }}" onblur="this.value = window.formatRupiahValue(this.value)" required class="input mt-1.5">
                            @error('saldo_awal') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tanggal_saldo_awal" class="label">Dihitung sejak</label>
                            <input id="tanggal_saldo_awal" name="tanggal_saldo_awal" type="date" value="{{ old('tanggal_saldo_awal', today()->toDateString()) }}" required class="input mt-1.5">
                            @error('tanggal_saldo_awal') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @else
                    <p class="mt-4 rounded-xl bg-canvas px-3 py-2.5 text-xs leading-relaxed text-muted">
                        Saldo awal tidak berubah. Untuk menyesuaikannya, gunakan <strong class="text-ink">Atur saldo awal</strong> di halaman Kantong.
                    </p>
                @endunless
            </section>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ route('kantong') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">
                    {{ $kantong ? 'Simpan kantong' : 'Tambah kantong' }}
                </button>
            </div>
        </form>
    </div>
@endsection
