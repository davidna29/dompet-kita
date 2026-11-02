@props([
    'action',
    'method' => 'DELETE',
    'title' => 'Hapus data ini?',
    'description' => 'Data tidak dihapus permanen; riwayat dan sinkronisasi tetap aman.',
    'label' => 'Hapus',
    'confirm' => 'Ya, hapus',
    'buttonClass' => 'btn btn-danger btn-sm',
])

@php
    $judulId = 'hapus-'.str_replace('.', '', uniqid('', true));
@endphp

<div x-data="{ buka: false }">
    <button type="button" x-on:click="buka = true" class="{{ $buttonClass }}">
        <x-icon name="trash" class="size-4" />
        {{ $label }}
    </button>

    <div x-cloak x-show="buka" x-transition.opacity.duration.200ms class="fixed inset-0 z-[80] flex items-end justify-center bg-black/45 p-3 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="{{ $judulId }}">
        <div x-on:click.outside="buka = false" x-on:keydown.escape.window="buka = false" class="w-full max-w-md rounded-2xl border border-line bg-paper-raised p-5 shadow-2xl">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-tomato-soft text-tomato">
                    <x-icon name="alert" class="size-5" />
                </span>
                <div>
                    <h2 id="{{ $judulId }}" class="font-display text-lg font-semibold">{{ $title }}</h2>
                    <p class="mt-1 text-sm leading-relaxed text-muted">{{ $description }}</p>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-on:click="buka = false" class="btn btn-ghost btn-sm">Batal</button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method($method)
                    <button type="submit" class="btn btn-danger btn-sm">{{ $confirm }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
