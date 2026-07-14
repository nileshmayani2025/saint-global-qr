@props(['href'])
<a href="{{ $href }}" title="View" aria-label="View"
   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-[var(--muted)] border border-[var(--border)] hover:border-brand-400 hover:text-brand-500 hover:bg-brand-500/10 transition">
    <svg class="w-[17px] h-[17px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
</a>
