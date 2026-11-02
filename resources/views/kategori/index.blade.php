@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    @php
        $ikonPilihan = ['utensils','cart','car','fuel','bolt','home','heart','book','gift','wifi','droplet','shirt','pill','plane','paw','baby','coffee','wrench','bank','cash','shopping-bag','graduation-cap','music','dumbbell','leaf','stethoscope','bus','ticket','piggy-bank','repeat','tag'];
    @endphp

    <div class="space-y-5">
        <header class="page-head">
            <div>
                <h1 class="page-title">Kategori</h1>
                <p class="page-sub">Kategori tetap teks bebas. Ikon, warna, dan urutan di sini hanya mengubah tampilannya; transaksi dari chat tetap masuk.</p>
            </div>
            <a href="{{ route('transaksi.create') }}" class="btn btn-ghost btn-sm self-start">
                <x-icon name="plus" class="size-4" /> Tambah transaksi
            </a>
        </header>

        <section class="paper overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-line px-4 py-3.5 sm:px-5">
                <div>
                    <h2 class="font-display text-base font-semibold">Daftar kategori</h2>
                    <p class="mt-0.5 text-[11px] text-muted">{{ $kategori->count() }} nama dari transaksi dan pengaturan.</p>
                </div>
                <span class="tag tag--netral">Teks bebas</span>
            </div>

            @if($kategori->isEmpty())
                <div class="empty">
                    <span class="empty__icon"><x-icon name="tag" class="size-6" /></span>
                    <h3 class="mt-4 font-display text-lg font-semibold">Belum ada kategori</h3>
                    <p class="mt-1 max-w-[36ch] text-sm text-muted">Kategori muncul otomatis saat transaksi pertama dicatat.</p>
                </div>
            @else
                <div class="divide-y divide-line" x-data>
                    @foreach($kategori as $item)
                        <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:px-5">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span class="grid size-9 shrink-0 place-items-center rounded-lg" style="background: {{ $item['warna'] }}1f; color: {{ $item['warna'] }}">
                                    <x-icon :name="$item['ikon']" class="size-4" />
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold">{{ $item['kategori'] }}</h3>
                                        @if($item['kategori'] === 'Mutasi')
                                            <span class="tag tag--mutasi">Pindah kantong</span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-[11px] text-muted">{{ $item['jumlah_transaksi'] }} transaksi · {{ rupiah($item['total']) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 sm:justify-end">
                                <span class="font-mono text-[10px] text-muted">urutan {{ $item['urutan'] }}</span>
                                <button type="button" class="chip" x-on:click="$dispatch('isi-kategori', {{ \Illuminate\Support\Js::from($item) }})">Ubah</button>
                                @if($item['meta_id'])
                                    <x-hapus-form
                                        :action="route('kategori.destroy', $item['meta_id'])"
                                        title="Hapus tampilan {{ $item['kategori'] }}?"
                                        description="Ikon dan warna kembali ke bawaan. Kategori dan transaksinya tetap ada."
                                        label="Hapus"
                                        confirm="Ya, hapus tampilan"
                                        buttonClass="btn btn-ghost btn-sm"
                                    />
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section id="form-kategori" class="paper p-4 sm:p-5"
                 data-kategori="{{ old('kategori') }}"
                 data-ikon="{{ old('ikon', 'tag') }}"
                 data-warna="{{ old('warna', '#496976') }}"
                 data-urutan="{{ (int) old('urutan', 0) }}"
                 x-data="{ kategori: '', ikon: 'tag', warna: '#496976', urutan: 0 }"
                 x-init="kategori = $el.dataset.kategori; ikon = $el.dataset.ikon; warna = $el.dataset.warna; urutan = $el.dataset.urutan"
                 x-on:isi-kategori.window="kategori = $event.detail.kategori; ikon = $event.detail.ikon; warna = $event.detail.warna; urutan = $event.detail.urutan; $el.scrollIntoView({ behavior: 'smooth', block: 'start' })">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-lg font-semibold">Tambah atau ubah tampilan</h2>
                    <p class="mt-1 text-sm text-muted">Pilih kategori yang sudah ada atau tulis nama baru. Menyimpan akan memperbarui meta yang cocok.</p>
                </div>
                <span class="tag bg-butter-soft text-ochre">Opsional</span>
            </div>

            <form method="POST" action="{{ route('kategori.store') }}" class="mt-4 grid gap-4 lg:grid-cols-[1fr_1fr]">
                @csrf
                <div>
                    <label for="kategori" class="label">Nama kategori</label>
                    <input id="kategori" name="kategori" type="text" list="daftar-kategori" x-model="kategori" maxlength="50" required class="input mt-1.5" placeholder="Makanan, Tagihan, Mutasi…">
                    <datalist id="daftar-kategori">
                        @foreach($kategori as $item)
                            <option value="{{ $item['kategori'] }}"></option>
                        @endforeach
                    </datalist>
                    @error('kategori') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="urutan" class="label">Urutan tampil</label>
                    <input id="urutan" name="urutan" type="number" min="0" max="999" x-model="urutan" class="input mt-1.5">
                    <p class="mt-1 text-[11px] text-muted">Angka kecil tampil lebih dulu di pilihan cepat.</p>
                </div>

                <div class="lg:col-span-2">
                    <span class="label">Ikon</span>
                    <div class="mt-2 grid grid-cols-6 gap-1.5 sm:grid-cols-8 lg:grid-cols-10">
                        @foreach($ikonPilihan as $ikon)
                            <label class="group grid aspect-square cursor-pointer place-items-center rounded-lg border border-line bg-paper-raised text-muted transition-colors has-[:checked]:border-olive has-[:checked]:bg-olive-soft has-[:checked]:text-olive-deep hover:border-line-strong">
                                <input type="radio" name="ikon" value="{{ $ikon }}" x-model="ikon" class="sr-only" @checked(old('ikon', 'tag') === $ikon)>
                                <x-icon :name="$ikon" class="size-5" />
                                <span class="sr-only">{{ $ikon }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('ikon') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="lg:col-span-2">
                    <span class="label">Warna</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach($palet as $warna)
                            <label class="grid size-11 cursor-pointer place-items-center rounded-full border-2 border-transparent has-[:checked]:border-ink" style="background: {{ $warna }}">
                                <input type="radio" name="warna" value="{{ $warna }}" x-model="warna" class="sr-only" @checked(old('warna', '#496976') === $warna)>
                                <span class="sr-only">{{ $warna }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('warna') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-end gap-2 lg:col-span-2">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan tampilan kategori</button>
                    <span class="text-[11px] text-muted">Kategori baru tanpa transaksi tetap disimpan sebagai meta.</span>
                </div>
            </form>
        </section>
    </div>
@endsection
