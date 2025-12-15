    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo h($flash['type']); ?>" style="max-width: 1400px; margin: 0 auto 20px;">
            <?php echo h($flash['message']); ?>
        </div>
    <?php endif; ?>
