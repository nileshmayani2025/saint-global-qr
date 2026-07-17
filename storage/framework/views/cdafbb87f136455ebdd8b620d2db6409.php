<!DOCTYPE html>
<html lang="en" x-data="{ dark: (localStorage.getItem('theme') ?? 'dark') === 'dark' }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Welcome'); ?> · <?php echo e(config('app.name')); ?></title>
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php echo $__env->make('partials.theme', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-5 sm:p-8"
         style="background:radial-gradient(760px 460px at 50% -10%, rgba(44,160,212,.18), transparent 60%), radial-gradient(600px 380px at 50% 120%, rgba(217,178,95,.08), transparent 60%);">
        <div class="w-full max-w-md lux-rise">
            <div class="text-center mb-7">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Saint Globle" class="w-20 h-20 mx-auto rounded-2xl ring-1 ring-black/5 dark:ring-white/10 shadow-xl">
                <div class="mt-3 font-display font-bold text-xl">Saint Globle</div>
                <div class="text-xs text-[var(--muted)]">A Construction Chemicals</div>
            </div>

            <div class="lux-card p-7 sm:p-8">
                <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->yieldContent('content'); ?>
            </div>

            <p class="mt-6 text-center text-xs text-[var(--muted)]">&copy; <?php echo e(date('Y')); ?> Saint Globle · A Construction Chemicals</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/layouts/guest.blade.php ENDPATH**/ ?>