@props(['href'])
<a href="{{ $href }}" title="Edit" aria-label="Edit"
   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-brand-500 border border-[var(--border)] hover:border-brand-400 hover:bg-brand-500/10 transition">
    <svg class="w-[17px] h-[17px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
    </svg>
</a>
