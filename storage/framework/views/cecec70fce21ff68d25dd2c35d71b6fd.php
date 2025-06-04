<?php if($row->getGallery()): ?>
<div class="g-gallery">
    <div class="fotorama" data-width="100%" data-thumbwidth="135" data-thumbheight="135" data-thumbmargin="15" data-nav="thumbs" data-allowfullscreen="true">
        <a href="<?php echo e($row->getBannerImageUrlAttribute('full')); ?>" data-thumb="<?php echo e($row->getBannerImageUrlAttribute('full')); ?>" data-alt="<?php echo e(__("Gallery")); ?>">
            <img src="<?php echo e($row->getBannerImageUrlAttribute('full')); ?>" alt="<?php echo e(__("Gallery")); ?>">
        </a>

        <?php $__currentLoopData = $row->getGallery(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e($item['large']); ?>" data-thumb="<?php echo e($item['thumb']); ?>" data-alt="<?php echo e(__("Gallery")); ?>">
            <img src="<?php echo e($item['thumb']); ?>" alt="<?php echo e(__("Gallery")); ?>">
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

</div>

<?php endif; ?>
<?php /**PATH /home/riyaoeiu/public_html/themes/BC/Layout/global/details/gallery.blade.php ENDPATH**/ ?>