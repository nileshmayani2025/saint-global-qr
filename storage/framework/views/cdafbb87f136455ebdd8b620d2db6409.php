<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Welcome'); ?> · <?php echo e(config('app.name')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: { colors: { brand: {50:'#f0f9fd',100:'#ddf0f9',200:'#bce3f2',300:'#8ccfe8',400:'#54b3d8',500:'#2ca0d4',600:'#2185b8',700:'#1d6d97',800:'#1b5a7c',900:'#194c68'} } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased">
    <div class="min-h-screen grid lg:grid-cols-2">
        <div class="hidden lg:flex flex-col justify-between p-12 text-white" style="background: linear-gradient(160deg, #2ca0d4, #1b5a7c);">
            <div class="flex items-center gap-3">
                <img src="/images/logo.png" alt="Saint Globe" class="w-12 h-12 rounded-xl shadow-md">
                <div class="leading-tight">
                    <div class="font-semibold text-lg">Saint Globe</div>
                    <div class="text-xs text-white/70">A Construction Chemicals</div>
                </div>
            </div>
            <div>
                <h2 class="text-3xl font-bold leading-tight">Product authentication &amp; reward platform</h2>
                <p class="mt-4 text-white/80 max-w-md">QR-based anti-counterfeit verification, batch management and consumer rewards — in one secure ERP.</p>
            </div>
            <p class="text-white/60 text-sm">&copy; <?php echo e(date('Y')); ?> Saint Globe · A Construction Chemicals</p>
        </div>
        <div class="flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">
                <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/layouts/guest.blade.php ENDPATH**/ ?>