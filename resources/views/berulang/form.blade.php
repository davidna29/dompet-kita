@extends('layouts.app')

@section('title', $jadwal ? 'Ubah jadwal' : 'Tambah jadwal')

@section('content')
    @php
        $kategoriAwal = old('kategori', $jadwal?->kategori ?? 'Tagihan');
        $mutasiAwal = strtolower(trim($kategoriAwal)) === 'mutasi';
        $aktifAwal = old('aktif', $jadwal?->aktif ?? true);
    @endphp

    <div class="mx-auto max-w-2xl space-y-5" x-data="{ mutasi: {{ $mutasiAwal ? 'true' : 'false' }} }">
        <header class="page-head">
            <div>
                <a href="{{ route('berulang.index') }}" class="mb-2 inline-flex items-center gap-1.5 text-xs font-bold text-muted hover:text-ink">
                    <x-icon name="arrow-left" class="size-3.5" />
                    Kembali ke jadwal
                </a>
                <h1 class="page-title">{{ $jadwal ? 'Ubah jadwal' : 'Tambah jadwal' }}</h1>
                <p class="page-sub">Jadwal hanya menjadi pengingat. Transaksi dibuat setelah Anda mengonfirmasi di papan atau lewat chat.</p>
            </div>
        </header>

        <form method="POST" action="{{ $jadwal ? route('berulang.update', $jadwal) : route('berulang.store') }}" class="space-y-4">
            @csrf
            @if($jadwal)
                @method('PUT')
            @endif

            <section class="paper p-5 sm:p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="nama" class="label">Nama tagihan</label>
                        <input id="nama" name="nama" type="text" value="{{ old('nama', $jadwal?->nama) }}" maxlength="80" required class="input mt-1.5" placeholder="Listrik PLN, SPP sekolah…">
                        @error('nama') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="kategori" class="label">Kategori</label>
                        <input id="kategori" name="kategori" list="kategori-jadwal" value="{{ $kategoriAwal }}" maxlength="50" required
                               x-on:input="mutasi = $el.value.trim().toLowerCase() === 'mutasi'" class="input mt-1.5" placeholder="Tagihan">
                        <datalist id="kategori-jadwal">
                            @foreach($kategori as $item)
                                <option value="{{ $item }}"></option>
                            @endforeach
                        </datalist>
                        @error('kategori') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="jumlah" class="label">Jumlah</label>
                        <input id="jumlah" name="jumlah" type="text" inputmode="numeric" value="{{ old('jumlah', $jadwal ? angka_input($jadwal->jumlah) : '') }}" onblur="this.value = window.formatRupiahValue(this.value)" required class="input mt-1.5" placeholder="480.000">
                        @error('jumlah') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="aturan" class="label">Aturan ulang</label>
                        <select id="aturan" name="aturan" class="input mt-1.5">
                            @foreach(['bulanan' => 'Bulanan', 'mingguan' => 'Mingguan', 'tahunan' => 'Tahunan'] as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('aturan', $jadwal?->aturan ?? 'bulanan') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="jatuh_tempo" class="label">Jatuh tempo berikutnya</label>
                        <input id="jatuh_tempo" name="jatuh_tempo" type="date" value="{{ old('jatuh_tempo', $jadwal?->jatuh_tempo?->format('Y-m-d') ?? today()->addDays(7)->toDateString()) }}" required class="input mt-1.5">
                        @error('jatuh_tempo') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="metode" class="label">Kantong <span class="normal-case tracking-normal text-muted">(opsional)</span></label>
                        <select id="metode" name="metode" class="input mt-1.5">
                            <option value="">Belum dipilih</option>
                            @foreach($kantong as $item)
                                <option value="{{ $item->nama }}" @selected(old('metode', $jadwal?->metode) === $item->nama)>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="mutasi" x-cloak>
                        <label for="kantong_tujuan" class="label">Kantong tujuan</label>
                        <select id="kantong_tujuan" name="kantong_tujuan" class="input mt-1.5">
                            <option value="">Pilih tujuan</option>
                            @foreach($kantong as $item)
                                <option value="{{ $item->nama }}" @selected(old('kantong_tujuan', $jadwal?->kantong_tujuan) === $item->nama)>{{ $item->nama }}</option>
                            @endforeach
                        </select>
                        @error('kantong_tujuan') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="pencatat" class="label">Pencatat default</label>
                        <select id="pencatat" name="pencatat" class="input mt-1.5">
                            @foreach($pencatat as $item)
                                <option value="{{ $item }}" @selected(old('pencatat', $jadwal?->pencatat ?? auth()->user()->pencatat) === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                        @error('pencatat') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="flex min-h-11 cursor-pointer items-center gap-2.5 text-sm font-semibold">
                            <input type="checkbox" name="aktif" value="1" @checked($aktifAwal) class="size-4 rounded border-line text-olive focus:ring-olive">
                            Jadwal aktif — tampilkan pengingat di papan
                        </label>
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">
                <div></div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row">
                    <a href="{{ route('berulang.index') }}" class="btn btn-ghost">Batal</a>
                    <button type="submit" class="btn btn-primary">{{ $jadwal ? 'Simpan perubahan' : 'Simpan jadwal' }}</button>
                </div>
            </div>
        </form>

        @if($jadwal)
            <div class="flex justify-start">
                <x-hapus-form
                    :action="route('berulang.destroy', $jadwal)"
                    title="Hapus jadwal {{ $jadwal->nama }}?"
                    description="Jadwal dihapus lunak dan berhenti muncul. Transaksi yang sudah pernah dikonfirmasi tetap ada."
                    label="Hapus jadwal"
                    confirm="Ya, hapus jadwal"
                />
            </div>
        @endif
    </div>
@endsection
