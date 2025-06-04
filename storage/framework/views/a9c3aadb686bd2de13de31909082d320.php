<?php
if(!empty($translation->include)){
$title = __("Included");
}
if(!empty($translation->exclude)){
$title = __("Excluded");
}
if(!empty($translation->exclude) and !empty($translation->include)){
$title = __("Included/Excluded");
}
?>
<?php if(!empty($title)): ?>
<div class="g-include-exclude">
    <div class="row">
        <?php if($translation->include): ?>
        <h3 class="section-title"><?php echo e(__("Package include")); ?></h3>
        <div class="col-lg-12 col-md-12">
            <div class="tab-content" id="includeTabContent">
                <div class="tab-pane fade show active" id="include" role="tabpanel" aria-labelledby="include-tab">
                    <div class="accordion include-items" id="accordion-include">
                        <?php $__currentLoopData = $translation->include; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="row accordion-parent">
                            <span class="step-day"></span>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-include-<?php echo e($loop->index); ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-include-<?php echo e($loop->index); ?>" aria-expanded="<?php echo e(!empty($item['description']) ? 'true' : 'false'); ?>" aria-controls="collapse-include-<?php echo e($loop->index); ?>">
                                        <i class="fa fa-circle step-icon"></i>
                                        <span class="step-title"><?php echo e($item['title']); ?></span>
                                    </button>
                                </h2>
                                <div id="collapse-include-<?php echo e($loop->index); ?>" class="accordion-collapse collapse <?php echo e(!empty($item['description']) ? 'show' : ''); ?>" aria-labelledby="heading-include-<?php echo e($loop->index); ?>">
                                    <div class="accordion-body">
                                        <?php echo e($item['description'] ?? ''); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if($translation->exclude): ?>
        <h3 class="section-title"><?php echo e(__("Package exclude")); ?></h3>
        <div class="col-lg-12 col-md-12">

            <div class="tab-content" id="excludeTabContent">
                <div class="tab-pane fade show active" id="exclude" role="tabpanel" aria-labelledby="exclude-tab">
                    <div class="accordion exclude-items" id="accordion-exclude">
                        <?php $__currentLoopData = $translation->exclude; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="row accordion-parent">
                            <span class="step-day"></span>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-exclude-<?php echo e($loop->index); ?>">
                                    <button class="accordion-button <?php echo e(!empty($item['description']) ? '' : 'collapsed'); ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-exclude-<?php echo e($loop->index); ?>" aria-expanded="<?php echo e(!empty($item['description']) ? 'true' : 'false'); ?>" aria-controls="collapse-exclude-<?php echo e($loop->index); ?>">
                                        <i class="fa fa-circle step-icon"></i>
                                        <span class="step-title"><?php echo e($item['title']); ?></span>
                                    </button>
                                </h2>
                                <div id="collapse-exclude-<?php echo e($loop->index); ?>" class="accordion-collapse collapse <?php echo e(!empty($item['description']) ? 'show' : ''); ?>" aria-labelledby="heading-exclude-<?php echo e($loop->index); ?>">
                                    <div class="accordion-body">
                                        <?php echo e($item['description'] ?? ''); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?><?php /**PATH /home/riyaoeiu/public_html/themes/BC/Tour/Views/frontend/layouts/details/tour-package-include-exclude.blade.php ENDPATH**/ ?>