@props(['compact' => false])

<a href="{{ route('beranda') }}" class="flex items-center gap-2.5" aria-label="Dompet Kita — Beranda">
    <svg class="size-9 shrink-0" viewBox="0 0 36 36" fill="none" aria-hidden="true">
        <rect x="2" y="2" width="32" height="32" rx="10" fill="currentColor" class="text-action"/>
        <rect x="6" y="6" width="24" height="24" rx="7" fill="var(--olive-deep)"/>
        <path d="M11 13h14M11 18h9M11 23h11" stroke="var(--paper)" stroke-width="1.8" stroke-linecap="round"/>
        <circle cx="25.5" cy="22.5" r="3.2" fill="var(--butter)"/>
    </svg>
    <span class="leading-none">
        <span class="block font-display text-[19px] font-semibold tracking-[-0.03em]">Dompet Kita</span>
        @unless($compact)
            <span class="mt-1 block font-mono text-[9px] uppercase tracking-[0.16em] opacity-70">Keuangan keluarga</span>
        @endunless
    </span>
</a>
