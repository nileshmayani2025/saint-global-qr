<?php $__env->startSection('title', 'Verify OTP'); ?>

<?php
    use App\Services\Auth\OtpService;
    use App\Support\Phone;

    $length = OtpService::LENGTH;
    $registering = $intent === OtpService::INTENT_REGISTER;
?>

<?php $__env->startSection('content'); ?>
    <h1 class="font-display text-2xl font-bold">Verify your number</h1>
    <p class="text-[var(--muted)] mt-1 text-sm">
        Enter the <?php echo e($length); ?>-digit code for
        <span class="font-semibold text-[var(--fg)] whitespace-nowrap">+91 <?php echo e(Phone::format($phone)); ?></span>
    </p>

    <form method="POST" action="<?php echo e(route('otp.verify')); ?>" class="mt-7"
          x-data="{
              digits: <?php echo \Illuminate\Support\Js::from(str_split(str_pad($prefill, $length, '0', STR_PAD_LEFT)))->toHtml() ?>,
              set(i, e) {
                  const v = (e.target.value || '').replace(/\D/g, '').slice(-1);
                  this.digits[i] = v;
                  e.target.value = v;
                  if (v && i < <?php echo e($length - 1); ?>) this.$refs['d' + (i + 1)].focus();
              },
              back(i, e) {
                  if (e.target.value || i === 0) return;
                  this.digits[i - 1] = '';
                  this.$refs['d' + (i - 1)].value = '';
                  this.$refs['d' + (i - 1)].focus();
              },
          }">
        <?php echo csrf_field(); ?>
        
        <input type="hidden" name="code" value="<?php echo e($prefill); ?>" :value="digits.join('')">

        <div class="flex justify-center gap-3">
            <?php for($i = 0; $i < $length; $i++): ?>
                <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code"
                       aria-label="Digit <?php echo e($i + 1); ?>"
                       value="<?php echo e(substr($prefill, $i, 1)); ?>"
                       x-ref="d<?php echo e($i); ?>"
                       @input="set(<?php echo e($i); ?>, $event)"
                       @keydown.backspace="back(<?php echo e($i); ?>, $event)"
                       @focus="$event.target.select()"
                       class="w-14 h-16 text-center text-2xl font-bold lux-field focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            <?php endfor; ?>
        </div>

        <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-3 text-sm text-rose-500 text-center"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <p class="mt-3 text-xs text-center text-[var(--muted)]">
            Code auto-filled — just tap <?php echo e($registering ? 'Create account' : 'Verify & sign in'); ?>.
        </p>

        <button class="mt-6 w-full rounded-lg lux-btn text-white font-medium py-2.5 transition">
            <?php echo e($registering ? 'Create account' : 'Verify & sign in'); ?>

        </button>
    </form>

    <div class="mt-6 flex items-center justify-center gap-4 text-sm">
        <a href="<?php echo e(route($registering ? 'register' : 'login')); ?>" class="text-slate-500 dark:text-slate-400 hover:text-brand-600">
            Change number
        </a>
        <span class="text-slate-300 dark:text-slate-700">·</span>
        <form method="POST" action="<?php echo e(route('otp.resend')); ?>">
            <?php echo csrf_field(); ?>
            <button class="text-brand-600 font-medium hover:underline">Resend code</button>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp\htdocs\Saint Global\resources\views/auth/otp.blade.php ENDPATH**/ ?>