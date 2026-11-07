<!DOCTYPE html>
<html lang="id" data-palette="olive">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f2edde" data-theme-color>
    <title>Masuk · Dompet Kita</title>
    <link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
    <link rel="manifest" href="/manifest.webmanifest">
    <script>
        (function () {
            try {
                var palet = localStorage.getItem('dompet-palet');
                document.documentElement.dataset.palette = palet === 'rose' ? 'rose' : 'olive';

                var tema = localStorage.getItem('dompet-tema');
                if (tema === 'dark' || (tema === null && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas text-ink">
    <main class="grid min-h-dvh lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative hidden overflow-hidden bg-olive-deep p-10 text-shell-ink lg:flex lg:flex-col lg:justify-between xl:p-14">
            <div class="flex items-center justify-between">
                <x-logo />
                <span class="tag border border-shell-line bg-shell-soft text-shell-ink/70">MOBILE-FIRST · WIB</span>
            </div>

            <div class="relative my-12 max-w-[520px]">
                <h1 class="mt-4 font-display text-[clamp(2.8rem,4.6vw,4.6rem)] font-semibold leading-[0.95] tracking-[-0.035em] text-shell-ink">
                    Uang rumah,<br>rapi di satu papan.
                </h1>
                <p class="mt-5 max-w-[40ch] text-sm leading-relaxed text-shell-ink/65">
                    Catat lewat Telegram, lihat dan koreksi di sini. Saldo kantong, anggaran, dan tagihan tetap satu angka.
                </p>

                <div class="relative mt-10 max-w-[410px] rotate-[-1deg] rounded-[14px] bg-paper p-4 text-ink shadow-[0_24px_60px_-34px_rgba(0,0,0,.65)]">
                    <span class="absolute -top-1.5 right-8 size-3 rounded-full bg-tomato shadow-[0_2px_3px_rgba(0,0,0,.35)]"></span>
                    <div class="flex items-center justify-between border-b border-line pb-3">
                        <span class="label">Papan bulan ini</span>
                        <span class="month-tab month-tab--aktif !h-7">NOVEMBER</span>
                    </div>
                    <div class="pt-4">
                        <p class="text-xs text-muted">Pengeluaran · Mutasi, Hutang, Piutang tidak dihitung</p>
                        <p class="amount mt-1 text-[clamp(2rem,3.2vw,2.8rem)] leading-none">Rp4.812.000</p>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-3 border-t border-line pt-3">
                        <div>
                            <p class="label">Masuk</p>
                            <p class="amount mt-1 text-sm">Rp13.500.000</p>
                        </div>
                        <div>
                            <p class="label">Selisih</p>
                            <p class="amount mt-1 text-sm text-sage">Rp8.688.000</p>
                        </div>
                        <div>
                            <p class="label">Mutasi</p>
                            <p class="amount mt-1 text-sm text-ochre">Rp1.200.000</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2 border-t border-line pt-3">
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="flex items-center gap-2"><span class="size-2 rounded-full bg-tomato"></span> Makanan · pasar</span>
                            <span class="amount">−Rp185.000</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="flex items-center gap-2"><span class="size-2 rounded-full bg-sky"></span> Tunai → BSI</span>
                            <span class="amount text-ochre">Rp700.000</span>
                        </div>
                    </div>
                    <p class="mt-3 text-[10px] text-muted">Contoh tampilan · bukan data keluarga asli</p>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-shell-line pt-4 text-[11px] text-shell-ink/50">
                <span>Tunai · BSI · Shopee</span>
                <span>Asia/Jakarta</span>
            </div>
        </section>

        <section class="flex min-h-dvh flex-col px-5 py-6 sm:px-10 lg:px-14">
            <div class="flex items-center justify-between lg:hidden">
                <x-logo />
                <button type="button" data-theme-toggle class="grid size-11 place-items-center rounded-xl border border-line bg-paper text-ink" aria-label="Ganti mode terang atau gelap">
                    <x-icon name="sun" class="theme-icon-sun size-5" />
                    <x-icon name="moon" class="theme-icon-moon size-5" />
                </button>
            </div>

            <div class="mx-auto flex w-full max-w-[420px] flex-1 flex-col justify-center py-10 lg:py-0">
                <div class="mb-8 hidden items-center justify-end lg:flex">
                    <button type="button" data-theme-toggle class="grid size-10 place-items-center rounded-xl border border-line bg-paper text-ink" aria-label="Ganti mode terang atau gelap">
                        <x-icon name="sun" class="theme-icon-sun size-4" />
                        <x-icon name="moon" class="theme-icon-moon size-4" />
                    </button>
                </div>

                <h2 class="font-display text-[clamp(2rem,5vw,2.6rem)] font-semibold leading-[1] tracking-[-0.04em]">Masuk ke papan.</h2>
                <p class="mt-3 text-sm leading-relaxed text-muted">Gunakan akun keluarga. Setiap catatan otomatis memakai nama pencatat dari akun yang masuk.</p>

                @if($errors->any())
                    <div class="mt-6 flex items-start gap-2.5 rounded-xl border border-tomato/40 bg-tomato-soft px-4 py-3 text-sm text-tomato" role="alert">
                        <x-icon name="alert" class="mt-0.5 size-5 shrink-0" />
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <div class="mt-7" x-data="{ email: '{{ old('email') }}', password: '', lihat: false }">
                    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="email" class="label">Email</label>
                            <input id="email" name="email" type="email" x-model="email" x-ref="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   class="input mt-2" placeholder="nama@keluarga.id">
                            @error('email') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <label for="password" class="label">Kata sandi</label>
                                <span class="text-[10px] text-muted">5 percobaan / menit / IP</span>
                            </div>
                            <div class="relative mt-2">
                                <input id="password" name="password" :type="lihat ? 'text' : 'password'" x-model="password" x-ref="password" required autocomplete="current-password"
                                       class="input pr-12" placeholder="Kata sandi">
                                <button type="button" x-on:click="lihat = !lihat" class="absolute inset-y-0 right-0 grid w-11 place-items-center text-muted hover:text-ink" :aria-label="lihat ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                                    <template x-if="!lihat"><x-icon name="eye" class="size-4" /></template>
                                    <template x-if="lihat"><x-icon name="eye-off" class="size-4" /></template>
                                </button>
                            </div>
                            @error('password') <p class="mt-1 text-sm font-semibold text-tomato">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center justify-between gap-3 pt-1">
                            <label class="flex min-h-11 cursor-pointer items-center gap-2 text-sm text-muted">
                                <input type="checkbox" name="remember" value="1" class="size-4 rounded border-line text-olive focus:ring-olive">
                                Ingat saya
                            </label>
                            <span class="text-[11px] text-muted">Akses lewat HTTPS</span>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            <x-icon name="arrow-right" class="size-4" />
                            Masuk
                        </button>
                    </form>

                    @if(config('app.demo_mode'))
                        <div class="mt-8 border-t border-line pt-5">
                            <div class="flex items-center gap-2">
                                <span class="tag bg-butter-soft text-ochre">Data contoh</span>
                                <p class="text-xs text-muted">Tersedia dua akun lokal untuk melihat aplikasi.</p>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <button type="button" class="chip h-11 justify-center" x-on:click="$refs.email.value = 'david@dompetkita.test'; $refs.password.value = 'password'; $refs.email.dispatchEvent(new Event('input')); $refs.password.dispatchEvent(new Event('input'))">
                                    Masuk sebagai David
                                </button>
                                <button type="button" class="chip h-11 justify-center" x-on:click="$refs.email.value = 'nurul@dompetkita.test'; $refs.password.value = 'password'; $refs.email.dispatchEvent(new Event('input')); $refs.password.dispatchEvent(new Event('input'))">
                                    Masuk sebagai Nurul
                                </button>
                            </div>
                            <p class="mt-2 text-[11px] text-muted">Kata sandi demo: <span class="font-mono">password</span>. Jangan gunakan akun ini di produksi.</p>
                        </div>
                    @endif
                </div>

                <p class="mt-auto pt-10 text-center text-[11px] leading-relaxed text-muted lg:pt-8">
                    Dompet Kita menyimpan data di server keluarga. Tidak ada data transaksi yang disimpan di perangkat ini.
                </p>
            </div>
        </section>
    </main>
    @livewireScripts
</body>
</html>
