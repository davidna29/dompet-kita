@extends('layouts.app')

@section('title', 'Catat hutang atau piutang')

@section('content')
    <div class="mx-auto max-w-2xl space-y-5">
        <header class="page-head">
            <div>
                <a href="{{ route('hutang.index') }}" class="mb-2 inline-flex min-h-11 items-center gap-1.5 text-xs font-bold text-muted hover:text-ink">
                    <x-icon name="arrow-left" class="size-3.5" />
                    Kembali ke daftar
                </a>
                <h1 class="page-title">Catat hutang atau piutang</h1>
                <p class="page-sub">Catatan ini tidak langsung menjadi transaksi. Transaksi dibuat saat pembayaran dikonfirmasi.</p>
            </div>
        </header>

        <form method="POST" action="{{ route('hutang.store') }}" class="space-y-4">
            @csrf

            <section class="paper p-5 sm:p-6">
                <div>
                    <span class="label">Jenis</span>
                    <div class="mt-1.5 grid grid-cols-2 gap-2">
                        @foreach(['hutang' => 'Hutang (saya pinjam)', 'piutang' => 'Piutang (dipinjam orang)'] as $nilai => $label)
                            <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-xl border border-line bg-paper-raised px-3 text-sm font-semibold has-[:checked]:border-olive has-[:checked]:bg-olive-soft">
                                <input type="radio" name="jenis" value="{{ $nilai }}" class="accent-olive" @checked(old('jenis', 'hutang') === $nilai) required>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('jenis') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label for="pihak" class="label">Nama pihak</label>
                    <input id="pihak" name="pihak" type="text" value="{{ old('pihak') }}" maxlength="50" required class="input mt-1.5" placeholder="Contoh: Pak Andi, Koperasi, Mbak Sari">
                    @error('pihak') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label for="nominal" class="label">Nominal</label>
                    <input id="nominal" name="nominal" type="text" inputmode="numeric" value="{{ old('nominal') }}" onblur="this.value = window.formatRupiahValue(this.value)" required class="input mt-1.5" placeholder="Contoh: 500.000">
                    @error('nominal') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="tanggal" class="label">Tanggal catat</label>
                        <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', today()->toDateString()) }}" class="input mt-1.5">
                        @error('tanggal') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jatuh_tempo" class="label">Jatuh tempo <span class="normal-case">(opsional)</span></label>
                        <input id="jatuh_tempo" name="jatuh_tempo" type="date" value="{{ old('jatuh_tempo') }}" class="input mt-1.5">
                        @error('jatuh_tempo') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="keterangan" class="label">Keterangan <span class="normal-case">(opsional)</span></label>
                    <textarea id="keterangan" name="keterangan" rows="3" maxlength="5000" class="input mt-1.5" placeholder="Contoh: Pinjam untuk servis motor">{{ old('keterangan') }}</textarea>
                    @error('keterangan') <p class="mt-1.5 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>
            </section>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ route('hutang.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan catatan</button>
            </div>
        </form>
    </div>
@endsection
