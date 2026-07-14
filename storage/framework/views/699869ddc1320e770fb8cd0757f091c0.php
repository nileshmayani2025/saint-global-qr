<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $map = [
        'active' => 'emerald', 'verified' => 'emerald', 'approved' => 'emerald', 'valid' => 'emerald',
        'inactive' => 'slate', 'draft' => 'slate', 'closed' => 'slate',
        'pending' => 'amber', 'generating' => 'amber', 'generated' => 'amber', 'duplicate' => 'amber',
        'blocked' => 'rose', 'rejected' => 'rose', 'suspended' => 'rose', 'invalid' => 'rose', 'expired' => 'orange',
        'printed' => 'sky', 'frozen' => 'sky',
    ];
    $color = $map[strtolower((string) $status)] ?? 'slate';
?>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
    bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 dark:bg-<?php echo e($color); ?>-900/40 dark:text-<?php echo e($color); ?>-300"><?php echo e($status); ?></span>
<?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/components/badge.blade.php ENDPATH**/ ?>