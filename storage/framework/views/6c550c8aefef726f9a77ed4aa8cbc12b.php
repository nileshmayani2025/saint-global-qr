<?php $__env->startSection('title', 'Create account'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="font-display text-2xl font-bold">Create your account</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-1">Verify your mobile number and start scanning right away.</p>

    <form method="POST" action="<?php echo e(route('register')); ?>" class="mt-8 space-y-5">
        <?php echo csrf_field(); ?>
        <div>
            <label for="name" class="block text-sm font-medium mb-1.5">Full name</label>
            <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" required autofocus
                   autocomplete="name"
                   class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-sm text-rose-500"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium mb-1.5">Mobile number</label>
            
            <div class="flex items-center lux-field focus-within:border-brand-500 focus-within:shadow-[0_0_0_4px_var(--ring)]">
                <span class="pl-3.5 pr-2 text-sm text-[var(--muted)] select-none shrink-0">+91</span>
                <input id="phone" type="tel" name="phone" value="<?php echo e(old('phone')); ?>" required
                       inputmode="numeric" autocomplete="tel" maxlength="10" placeholder="98765 43210"
                       class="flex-1 min-w-0 bg-transparent border-0 outline-none py-2.5 pr-3.5 tracking-wider text-[var(--text)] placeholder:text-[var(--muted)]">
            </div>
            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-sm text-rose-500"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            I agree to the terms of service
        </label>
        <?php $__errorArgs = ['terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-sm text-rose-500"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <button class="w-full rounded-lg lux-btn text-white font-medium py-2.5 transition">Send OTP</button>
    </form>

    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400">
        Already registered? <a href="<?php echo e(route('login')); ?>" class="text-brand-600 font-medium hover:underline">Sign in</a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/auth/register.blade.php ENDPATH**/ ?>