@props(['action', 'confirm' => 'Delete this record? This cannot be undone.'])
<form method="POST" action="{{ $action }}" class="inline-flex" onsubmit="return confirm('{{ $confirm }}')">
    @csrf
    @method('DELETE')
    <button type="submit" title="Delete" aria-label="Delete"
            class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-rose-500 border border-[var(--border)] hover:border-rose-400 hover:bg-rose-500/10 transition">
        <svg class="w-[17px] h-[17px]" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
    </button>
</form>
