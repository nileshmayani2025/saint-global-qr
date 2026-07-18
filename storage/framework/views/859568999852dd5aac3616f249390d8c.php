<?php $__env->startSection('title', 'Sign in'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="font-display text-2xl font-bold">Welcome back</h1>
    <p class="text-[var(--muted)] mt-1">Enter your mobile number to sign in.</p>

    <form method="POST" action="<?php echo e(route('app.login.otp')); ?>" class="mt-8 space-y-5">
        <?php echo csrf_field(); ?>
        <div>
            <label for="phone" class="block text-sm font-medium mb-1.5">Mobile number</label>
            
            <div class="flex items-center lux-field focus-within:border-brand-500 focus-within:shadow-[0_0_0_4px_var(--ring)]">
                <span class="pl-3.5 pr-2 text-sm text-[var(--muted)] select-none shrink-0">+91</span>
                <input id="phone" type="tel" name="phone" value="<?php echo e(old('phone')); ?>" required autofocus
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
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Keep me signed in
        </label>
        <button class="w-full rounded-lg lux-btn text-white font-medium py-2.5 transition">Send OTP</button>
    </form>

    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400">
        No account? <a href="<?php echo e(route('register')); ?>" class="text-brand-600 font-medium hover:underline">Create one</a>
    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/auth/app-login.blade.php ENDPATH**/ ?>