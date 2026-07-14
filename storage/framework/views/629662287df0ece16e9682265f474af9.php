<?php $__env->startSection('title', $product->exists ? 'Edit product' : 'New product'); ?>

<?php $__env->startSection('content'); ?>
    <a href="<?php echo e(route('products.index')); ?>" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to products</a>

    <form method="POST" action="<?php echo e($product->exists ? route('products.update', $product) : route('products.store')); ?>" enctype="multipart/form-data" class="mt-4 max-w-3xl">
        <?php echo csrf_field(); ?>
        <?php if($product->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Name</label>
                    <input name="name" value="<?php echo e(old('name', $product->name)); ?>" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">SKU</label>
                    <input name="sku" value="<?php echo e(old('sku', $product->sku)); ?>" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">HSN code</label>
                    <input name="hsn_code" value="<?php echo e(old('hsn_code', $product->hsn_code)); ?>" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Brand</label>
                    <select name="brand_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                        <option value="">— None —</option>
                        <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b->id); ?>" <?php if(old('brand_id', $product->brand_id) == $b->id): echo 'selected'; endif; ?>><?php echo e($b->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Category</label>
                    <select name="category_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                        <option value="">— None —</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(old('category_id', $product->category_id) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Unit</label>
                    <input name="unit" value="<?php echo e(old('unit', $product->unit ?? 'piece')); ?>" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">MRP (₹)</label>
                    <input type="number" step="0.01" min="0" name="mrp" value="<?php echo e(old('mrp', $product->mrp ?? 0)); ?>" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Reward points</label>
                    <input type="number" min="0" name="reward_points" value="<?php echo e(old('reward_points', $product->reward_points ?? 0)); ?>" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5">
                        <option value="active" <?php if(old('status', $product->status) === 'active'): echo 'selected'; endif; ?>>Active</option>
                        <option value="inactive" <?php if(old('status', $product->status) === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5"><?php echo e(old('description', $product->description)); ?></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1.5">Image</label>
                    <?php if($product->image_path): ?>
                        <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($product->image_path)); ?>" class="w-24 h-24 object-cover rounded-lg mb-2 border border-slate-200 dark:border-slate-700">
                        <label class="flex items-center gap-2 text-sm mb-2"><input type="checkbox" name="remove_image" value="1" class="rounded"> Remove current image</label>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" class="w-full text-sm">
                </div>
            </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
            <button class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium px-5 py-2.5"><?php echo e($product->exists ? 'Update' : 'Create'); ?> product</button>
            <button name="after_save" value="continue" class="rounded-lg border border-slate-300 dark:border-slate-700 px-5 py-2.5 font-medium hover:bg-slate-50 dark:hover:bg-slate-800">Save &amp; continue</button>
            <a href="<?php echo e(route('products.index')); ?>" class="text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/products/form.blade.php ENDPATH**/ ?>