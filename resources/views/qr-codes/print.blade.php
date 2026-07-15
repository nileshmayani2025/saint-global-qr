<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>QR Sheet · {{ $batch->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@media print { .no-print { display: none !important; } @page { margin: 12mm; } }</style>
</head>
<body class="bg-white text-slate-900">
    <div class="max-w-5xl mx-auto p-6">
        <div class="flex items-center justify-between mb-6 no-print">
            <div>
                <h1 class="text-xl font-bold">Batch {{ $batch->code }}</h1>
                <p class="text-slate-500 text-sm">{{ $batch->product?->name }} · {{ $qrCodes->count() }} codes</p>
            </div>
            <button onclick="window.print()" class="rounded-lg bg-blue-600 text-white font-medium px-5 py-2.5">Print</button>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
            @forelse ($qrCodes as $qr)
                <div class="border border-slate-300 rounded-lg p-2 text-center break-inside-avoid">
                    @if ($qr->image_path)
                        <img src="{{ asset('media/'.$qr->image_path) }}" class="w-full aspect-square object-contain">
                    @endif
                    <div class="mt-1 text-[9px] font-mono truncate">{{ $qr->code }}</div>
                    <div class="text-[9px] text-slate-500">#{{ $qr->serial }}</div>
                </div>
            @empty
                <p class="col-span-full text-center text-slate-400 py-10">No QR codes generated for this batch yet.</p>
            @endforelse
        </div>
    </div>
</body>
</html>
