<?php $__currentLoopData = ['success' => 'emerald', 'error' => 'rose', 'info' => 'brand', 'warning' => 'amber']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(session($key)): ?>
        <div x-data="{ show: true }" x-show="show" x-transition
             class="mb-4 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm
             border-<?php echo e($color); ?>-200 bg-<?php echo e($color); ?>-50 text-<?php echo e($color); ?>-800
             dark:border-<?php echo e($color); ?>-900/50 dark:bg-<?php echo e($color); ?>-900/30 dark:text-<?php echo e($color); ?>-200">
            <span class="flex-1"><?php echo e(session($key)); ?></span>
            <button @click="show = false" class="opacity-60 hover:opacity-100">&times;</button>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($errors->any()): ?>
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 dark:border-rose-900/50 dark:bg-rose-900/30 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
        <p class="font-semibold mb-1">Please fix the following:</p>
        <ul class="list-disc list-inside space-y-0.5">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/partials/flash.blade.php ENDPATH**/ ?>