{{--
    Session flash messages now render as toasts (partials/toast.blade.php). Only
    validation errors stay inline: a long list of field problems belongs next to
    the form it refers to, not in a toast that times out.
--}}
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
