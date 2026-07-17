
<!DOCTYPE html>
<html lang="en" x-data="{ dark: (localStorage.getItem('theme') ?? 'dark') === 'dark' }"
      :class="{ 'dark': dark }" x-init="$watch('dark', v => localStorage.setItem('theme', v ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Home'); ?> · <?php echo e(config('app.name')); ?></title>
    <link rel="icon" href="<?php echo e(asset('images/logo.png')); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php echo $__env->make('partials.theme', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Sit clear of the Android gesture bar / iOS home indicator. */
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }
    </style>
</head>
<body>
<?php
    $user = auth()->user();
    $walletBalance = (float) ($user->rewardWallet?->balance ?? 0);

    $tabs = [
        ['route' => 'dashboard', 'label' => 'Home',    'icon' => 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['route' => 'scan',      'label' => 'Scan',    'icon' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3'],
        ['route' => 'my.scans',  'label' => 'History', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['route' => 'my.rewards','label' => 'Rewards', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
    ];
?>

<div class="min-h-screen flex flex-col">
    
    <header class="lux-topbar sticky top-0 z-20 h-16 flex items-center gap-3 px-4">
        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-2.5 min-w-0">
            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Saint Globle" class="w-9 h-9 rounded-xl ring-1 ring-black/5 dark:ring-white/10 shrink-0">
            <span class="font-display font-bold text-lg truncate">Saint Globle</span>
        </a>

        <div class="ml-auto flex items-center gap-2 shrink-0">
            <a href="<?php echo e(route('my.rewards')); ?>"
               class="inline-flex items-center gap-1.5 rounded-full bg-brand-600 text-white pl-2.5 pr-3 py-1.5 text-sm font-semibold shadow-sm hover:bg-brand-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h.01M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                ₹<?php echo e(number_format($walletBalance, 2)); ?>

            </a>

            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" aria-label="Account"
                        class="w-9 h-9 rounded-full grid place-items-center text-sm font-bold text-white ring-1 ring-black/5 dark:ring-white/10"
                        style="background:linear-gradient(135deg,#2ca0d4,#1b5a7c)">
                    <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                </button>
                <div x-show="open" x-cloak x-transition @click.outside="open = false"
                     class="lux-card absolute right-0 mt-2 w-56 py-2 text-sm z-30">
                    <div class="px-4 py-2 border-b border-[var(--border)]">
                        <div class="font-semibold truncate"><?php echo e($user->name); ?></div>
                        <div class="text-xs text-[var(--muted)]">+91 <?php echo e(\App\Support\Phone::format($user->phone)); ?></div>
                    </div>
                    <button @click="dark = !dark" class="w-full text-left px-4 py-2.5 hover:bg-black/5 dark:hover:bg-white/5">
                        <span x-show="!dark">Dark theme</span><span x-show="dark" x-cloak>Light theme</span>
                    </button>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="w-full text-left px-4 py-2.5 text-rose-500 hover:bg-rose-500/10">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    
    <main class="flex-1 px-4 pt-4 pb-28">
        <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <nav class="lux-topbar fixed bottom-0 inset-x-0 z-20 safe-bottom border-t border-[var(--border)]">
        <div class="grid grid-cols-4">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $active = request()->routeIs($tab['route']); ?>
                <a href="<?php echo e(route($tab['route'])); ?>"
                   class="flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium transition <?php echo e($active ? 'text-brand-600 dark:text-brand-400' : 'text-[var(--muted)]'); ?>">
                    <span class="grid place-items-center w-9 h-8 rounded-lg <?php echo e($active ? 'bg-brand-500/10' : ''); ?>">
                        <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($tab['icon']); ?>"/>
                        </svg>
                    </span>
                    <?php echo e($tab['label']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </nav>
</div>
</body>
</html>
<?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/layouts/consumer.blade.php ENDPATH**/ ?>