<!DOCTYPE html>
<html lang="en" x-data="{ dark: localStorage.getItem('theme') === 'dark', sidebar: window.innerWidth > 1024 }"
      :class="{ 'dark': dark }" x-init="$watch('dark', v => localStorage.setItem('theme', v ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> · <?php echo e(config('app.name')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: {
            brand: { 50:'#f0f9fd',100:'#ddf0f9',200:'#bce3f2',300:'#8ccfe8',400:'#54b3d8',500:'#2ca0d4',600:'#2185b8',700:'#1d6d97',800:'#1b5a7c',900:'#194c68' }
        } } } }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-200 antialiased">
<?php
    $nav = [
        ['route' => 'dashboard',        'label' => 'Dashboard',   'perm' => null,              'icon' => 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['route' => 'products.index',   'label' => 'Products',    'perm' => 'products.view',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4'],
        ['route' => 'brands.index',     'label' => 'Brands',      'perm' => 'brands.view',     'icon' => 'M7 7h.01M7 3h5a2 2 0 011.4.6l7 7a2 2 0 010 2.8l-5 5a2 2 0 01-2.8 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
        ['route' => 'categories.index', 'label' => 'Categories',  'perm' => 'categories.view', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
        ['route' => 'batches.index',    'label' => 'Batches',     'perm' => 'batches.view',    'icon' => 'M20 7L12 3 4 7m16 0l-8 4m8-4v10l-8 4M4 7l8 4m-8-4v10l8 4m0-14v14'],
        ['route' => 'qr-codes.index',   'label' => 'QR Codes',    'perm' => 'qr-codes.view',   'icon' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 3h3m3 0h.01M14 14h.01M17 20h3v-3'],
        ['route' => 'wallets.index',    'label' => 'Wallets',     'perm' => 'wallets.view',    'icon' => 'M3 10h18M7 15h.01M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z'],
        ['route' => 'redemptions.index','label' => 'Redemptions', 'perm' => 'redemptions.view','icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m0-8c1.11 0 2.08.402 2.599 1M12 8c-1.11 0-2.08.402-2.599 1'],
        ['route' => 'users.index',      'label' => 'Users',       'perm' => 'users.view',      'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z'],
        ['route' => 'roles.index',      'label' => 'Roles',       'perm' => 'roles.view',      'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    $isActive = fn ($route) => request()->routeIs($route) || request()->routeIs(\Illuminate\Support\Str::before($route, '.').'.*');
?>

<div class="min-h-screen lg:flex">
    <!-- Sidebar -->
    <aside x-show="sidebar" x-cloak
           class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col lg:static lg:translate-x-0">
        <div class="h-16 flex items-center gap-2.5 px-4 border-b border-slate-200 dark:border-slate-800">
            <img src="/images/logo.png" alt="Saint Globe" class="w-10 h-10 rounded-lg object-cover shadow-sm">
            <div class="font-semibold leading-tight">Saint&nbsp;Globe<br><span class="text-xs text-slate-400 font-normal">Verify &amp; Reward</span></div>
        </div>
        <nav class="flex-1 overflow-y-auto p-3 space-y-1">
            <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_null($item['perm']) || auth()->user()->can($item['perm'])): ?>
                    <a href="<?php echo e(route($item['route'])); ?>"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                       <?php echo e($isActive($item['route']) ? 'bg-brand-600 text-white shadow' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($item['icon']); ?>"/></svg>
                        <?php echo e($item['label']); ?>

                    </a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="pt-3 mt-3 border-t border-slate-200 dark:border-slate-800 space-y-1">
                <a href="<?php echo e(route('my.scans')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium <?php echo e($isActive('my.scans') ? 'bg-brand-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    My Scans
                </a>
                <a href="<?php echo e(route('my.rewards')); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium <?php echo e($isActive('my.rewards') ? 'bg-brand-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    My Rewards
                </a>
                <a href="<?php echo e(route('verify.form')); ?>" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Verify a Product
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3 px-4 sticky top-0 z-30">
            <button @click="sidebar = !sidebar" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="font-semibold text-lg"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
            <div class="ml-auto flex items-center gap-2">
                <?php if (! (auth()->user()->isApproved())): ?>
                    <span class="hidden sm:inline text-xs px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 font-medium">Pending approval</span>
                <?php endif; ?>
                <button @click="dark = !dark" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" title="Toggle theme">
                    <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                        <div class="w-8 h-8 rounded-full bg-brand-600 text-white grid place-items-center text-sm font-semibold"><?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?></div>
                        <span class="hidden sm:block text-sm font-medium"><?php echo e(auth()->user()->name); ?></span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg py-2 text-sm">
                        <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="font-medium truncate"><?php echo e(auth()->user()->email); ?></div>
                            <div class="text-xs text-slate-400"><?php echo e(auth()->user()->getRoleNames()->implode(', ') ?: 'No role'); ?></div>
                        </div>
                        <a href="<?php echo e(route('my.rewards')); ?>" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">My Rewards</a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?>
                            <button class="w-full text-left px-4 py-2 text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-800">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto">
            <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
</div>
</body>
</html>
<?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/layouts/app.blade.php ENDPATH**/ ?>