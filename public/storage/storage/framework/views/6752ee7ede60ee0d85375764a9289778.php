<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH /home/suwilanji/dev/supply-catena/vendor/filament/forms/resources/views/components/group.blade.php ENDPATH**/ ?>