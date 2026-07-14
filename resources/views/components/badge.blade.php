@props(['status'])
@php
    $map = [
        'active' => 'emerald', 'verified' => 'emerald', 'approved' => 'emerald', 'valid' => 'emerald',
        'inactive' => 'slate', 'draft' => 'slate', 'closed' => 'slate',
        'pending' => 'amber', 'generating' => 'amber', 'generated' => 'amber', 'duplicate' => 'amber',
        'blocked' => 'rose', 'rejected' => 'rose', 'suspended' => 'rose', 'invalid' => 'rose', 'expired' => 'orange',
        'printed' => 'sky', 'frozen' => 'sky',
    ];
    $color = $map[strtolower((string) $status)] ?? 'slate';
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
    bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/40 dark:text-{{ $color }}-300">{{ $status }}</span>
