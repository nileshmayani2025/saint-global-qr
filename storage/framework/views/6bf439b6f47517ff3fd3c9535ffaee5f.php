<?php $__env->startSection('title', $batch->exists ? 'Edit batch' : 'New batch'); ?>

<?php $__env->startSection('content'); ?>
    <a href="<?php echo e(route('batches.index')); ?>" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to batches</a>

    <form method="POST" action="<?php echo e($batch->exists ? route('batches.update', $batch) : route('batches.store')); ?>" class="mt-4 max-w-xl">
        <?php echo csrf_field(); ?>
        <?php if($batch->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
        <div class="lux-card p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium mb-1.5">Product</label>
                <select name="product_id" required class="w-full lux-field px-3.5 py-2.5">
                    <option value="">— Select product —</option>
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>" <?php if(old('product_id', $batch->product_id) == $p->id): echo 'selected'; endif; ?>><?php echo e($p->name); ?> (<?php echo e($p->sku); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Batch code</label>
                <input name="code" value="<?php echo e(old('code', $batch->code)); ?>" required class="w-full lux-field px-3.5 py-2.5">
            </div>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Manufacture date</label>
                    <input type="date" name="manufacture_date" value="<?php echo e(old('manufacture_date', optional($batch->manufacture_date)->toDateString())); ?>" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Expiry date</label>
                    <input type="date" name="expiry_date" value="<?php echo e(old('expiry_date', optional($batch->expiry_date)->toDateString())); ?>" class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Quantity</label>
                    <input type="number" min="1" name="quantity" value="<?php echo e(old('quantity', $batch->quantity ?? 100)); ?>" required class="w-full lux-field px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Reward points <span class="text-slate-400">(optional)</span></label>
                    <input type="number" min="0" name="reward_points" value="<?php echo e(old('reward_points', $batch->reward_points)); ?>" class="w-full lux-field px-3.5 py-2.5">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Status</label>
                <select name="status" class="w-full lux-field px-3.5 py-2.5">
                    <?php $__currentLoopData = ['draft','generating','active','closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s); ?>" <?php if(old('status', $batch->status ?? 'draft') === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg lux-btn text-white font-medium px-5 py-2.5"><?php echo e($batch->exists ? 'Update' : 'Create'); ?> batch</button>
            <a href="<?php echo e(route('batches.index')); ?>" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/batches/form.blade.php ENDPATH**/ ?>