<?php $__env->startSection('title', 'Verification result'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $genuine = $result->genuine && $result->isValid();
        $duplicate = $result->isDuplicate();
        [$tone, $icon, $heading] = $genuine
            ? ['emerald', 'M9 12l2 2 4-4', 'Genuine product']
            : ($duplicate
                ? ['amber', 'M12 9v2m0 4h.01M12 3l9 16H3z', 'Already verified']
                : ['rose', 'M6 18L18 6M6 6l12 12', 'Verification failed']);
        $product = $result->qrCode?->product;
    ?>

    <div class="text-center">
        <div class="w-16 h-16 mx-auto rounded-full grid place-items-center bg-<?php echo e($tone); ?>-100 text-<?php echo e($tone); ?>-600 dark:bg-<?php echo e($tone); ?>-900/40 dark:text-<?php echo e($tone); ?>-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icon); ?>"/></svg>
        </div>
        <h1 class="mt-4 text-2xl font-bold text-<?php echo e($tone); ?>-600 dark:text-<?php echo e($tone); ?>-400"><?php echo e($heading); ?></h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1"><?php echo e($result->message); ?></p>
    </div>

    <?php if($product): ?>
        <div class="mt-8 lux-card p-6">
            <h2 class="font-semibold text-lg"><?php echo e($product->name); ?></h2>
            <p class="text-sm text-slate-500"><?php echo e($product->brand?->name); ?></p>
            <dl class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-400">SKU</dt><dd class="font-medium"><?php echo e($product->sku); ?></dd></div>
                <?php if($result->qrCode?->batch): ?>
                    <div class="flex justify-between"><dt class="text-slate-400">Batch</dt><dd class="font-medium"><?php echo e($result->qrCode->batch->code); ?></dd></div>
                    <?php if($result->qrCode->batch->expiry_date): ?>
                        <div class="flex justify-between"><dt class="text-slate-400">Expiry</dt><dd class="font-medium"><?php echo e($result->qrCode->batch->expiry_date->format('d M Y')); ?></dd></div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($result->verification?->reward_points): ?>
                    <div class="flex justify-between"><dt class="text-slate-400">Reward points</dt><dd class="font-medium text-amber-600">+<?php echo e($result->verification->reward_points); ?></dd></div>
                <?php endif; ?>
            </dl>
        </div>
    <?php endif; ?>

    <?php if(! empty($result->reasons)): ?>
        <div class="mt-4 rounded-xl border border-<?php echo e($tone); ?>-200 bg-<?php echo e($tone); ?>-50 dark:border-<?php echo e($tone); ?>-900/50 dark:bg-<?php echo e($tone); ?>-900/20 p-4 text-sm text-<?php echo e($tone); ?>-800 dark:text-<?php echo e($tone); ?>-200">
            <ul class="list-disc list-inside space-y-0.5">
                <?php $__currentLoopData = $result->reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($reason); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <a href="<?php echo e(route('verify.form')); ?>" class="mt-6 block text-center rounded-lg border border-slate-300 dark:border-slate-700 px-4 py-3 font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Verify another product</a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/verify/result.blade.php ENDPATH**/ ?>