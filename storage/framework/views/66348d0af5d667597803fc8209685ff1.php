<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $cards = [
            ['label' => 'Products',        'value' => $stats['products'],      'color' => 'brand'],
            ['label' => 'Batches',         'value' => $stats['batches'],       'color' => 'indigo'],
            ['label' => 'QR Codes',        'value' => $stats['qr_codes'],      'color' => 'violet'],
            ['label' => 'Verified',        'value' => $stats['verified'],      'color' => 'emerald'],
            ['label' => 'Verifications',   'value' => $stats['verifications'], 'color' => 'sky'],
            ['label' => 'Scans',           'value' => $stats['scans'],         'color' => 'cyan'],
            ['label' => 'Fraud Suspected', 'value' => $stats['fraud_scans'],   'color' => 'rose'],
            ['label' => 'Reward Points',   'value' => $stats['reward_points'], 'color' => 'amber'],
        ];
        $maxSeries = max(1, $series->max('count'));
    ?>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
                <div class="text-sm text-slate-500 dark:text-slate-400"><?php echo e($c['label']); ?></div>
                <div class="mt-1 text-2xl font-bold text-<?php echo e($c['color']); ?>-600 dark:text-<?php echo e($c['color']); ?>-400"><?php echo e(number_format($c['value'])); ?></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="grid lg:grid-cols-3 gap-4 mt-6">
        <div class="lg:col-span-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
            <h2 class="font-semibold mb-4">Verifications — last 14 days</h2>
            <div class="flex items-end gap-1.5 h-48">
                <?php $__currentLoopData = $series; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex-1 flex flex-col items-center gap-1 group">
                        <div class="w-full rounded-t bg-brand-500/80 hover:bg-brand-500 transition-all relative"
                             style="height: <?php echo e(max(4, (int) round($point['count'] / $maxSeries * 160))); ?>px" title="<?php echo e($point['count']); ?>">
                            <span class="absolute -top-5 left-1/2 -translate-x-1/2 text-xs opacity-0 group-hover:opacity-100"><?php echo e($point['count']); ?></span>
                        </div>
                        <span class="text-[10px] text-slate-400 rotate-0 whitespace-nowrap"><?php echo e(\Illuminate\Support\Str::before($point['label'], ' ')); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
            <h2 class="font-semibold mb-4">Scan results</h2>
            <?php $totalScans = max(1, $resultBreakdown->sum()); ?>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = ['valid'=>'emerald','duplicate'=>'amber','invalid'=>'rose','blocked'=>'slate','expired'=>'orange']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $val = (int) ($resultBreakdown[$res] ?? 0); ?>
                    <div>
                        <div class="flex justify-between text-sm mb-1"><span class="capitalize"><?php echo e($res); ?></span><span class="font-medium"><?php echo e($val); ?></span></div>
                        <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-<?php echo e($color); ?>-500" style="width: <?php echo e(round($val / $totalScans * 100)); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-400">No scans yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4 mt-6">
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
            <h2 class="font-semibold mb-4">Top products</h2>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <div class="font-medium"><?php echo e($tp->product?->name ?? 'Unknown'); ?></div>
                            <div class="text-xs text-slate-400"><?php echo e($tp->product?->sku); ?></div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 font-medium"><?php echo e($tp->verifications); ?> verified</span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-400">No verifications yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
            <h2 class="font-semibold mb-4">Recent activity</h2>
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex gap-3 text-sm">
                        <div class="w-2 h-2 mt-1.5 rounded-full bg-brand-500 shrink-0"></div>
                        <div class="min-w-0">
                            <div class="truncate"><?php echo e($log->description ?? $log->event); ?></div>
                            <div class="text-xs text-slate-400"><?php echo e($log->causer?->name ?? 'System'); ?> · <?php echo e($log->created_at?->diffForHumans()); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-400">No activity recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/dashboard.blade.php ENDPATH**/ ?>