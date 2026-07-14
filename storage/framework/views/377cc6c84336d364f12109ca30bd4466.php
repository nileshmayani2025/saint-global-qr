<?php $__env->startSection('title', 'Roles'); ?>

<?php $__env->startSection('content'); ?>
    <div class="flex items-center justify-between gap-3 mb-5">
        <p class="text-slate-500 dark:text-slate-400 text-sm"><?php echo e($roles->total()); ?> role(s)</p>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('roles.create')): ?>
            <a href="<?php echo e(route('roles.create')); ?>" class="rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium px-4 py-2">+ New role</a>
        <?php endif; ?>
    </div>

    <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-left text-slate-500 dark:text-slate-400">
                    <tr><th class="px-4 py-3 font-medium">Role</th><th class="px-4 py-3 font-medium">Permissions</th><th class="px-4 py-3 font-medium">Users</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3 font-medium"><?php echo e(ucwords(str_replace('-', ' ', $role->name))); ?></td>
                            <td class="px-4 py-3 text-slate-500"><?php echo e($role->permissions_count); ?></td>
                            <td class="px-4 py-3 text-slate-500"><?php echo e($role->users_count); ?></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('roles.update')): ?><a href="<?php echo e(route('roles.edit', $role)); ?>" class="text-brand-600 hover:underline">Edit</a><?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('roles.delete')): ?>
                                    <form method="POST" action="<?php echo e(route('roles.destroy', $role)); ?>" class="inline" onsubmit="return confirm('Delete this role?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="ml-3 text-rose-600 hover:underline">Delete</button></form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No roles found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4"><?php echo e($roles->withQueryString()->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/roles/index.blade.php ENDPATH**/ ?>