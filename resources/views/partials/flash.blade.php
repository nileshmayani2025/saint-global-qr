@foreach (['success' => 'emerald', 'error' => 'rose', 'info' => 'brand', 'warning' => 'amber'] as $key => $color)
    @if (session($key))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="mb-4 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm
             border-{{ $color }}-200 bg-{{ $color }}-50 text-{{ $color }}-800
             dark:border-{{ $color }}-900/50 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-200">
            <span class="flex-1">{{ session($key) }}</span>
            <button @click="show = false" class="opacity-60 hover:opacity-100">&times;</button>
        </div>
    @endif
@endforeach

@if ($errors->any())
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 dark:border-rose-900/50 dark:bg-rose-900/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
        <p class="font-semibold mb-1">Please fix the following:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
