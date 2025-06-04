<?php
// Initialize default places structure
$defaultPlaces = [
];

// Use existing places or default
$places = $translation->places;
if (!is_array($translation->places)) {
    $places = json_decode(old('places', default: $translation->places), true);
}
if (empty($places)) {
    $places = $defaultPlaces;
    $translation->places = $defaultPlaces;
}
if(isset($places['__number__'])){
    unset($places['__number__']);
}
?>

<div class="form-group-item">
    <label class="control-label"><?php echo e(__('Places to Visit')); ?></label>
    <div class="g-items-header">
        <div class="row">
            <div class="col-md-5 text-left"><?php echo e(__("Title")); ?></div>
            <div class="col-md-6 text-left"><?php echo e(__("Image")); ?></div>
            <div class="col-md-1"></div>
        </div>
    </div>
    <div class="g-items">
        <?php if(!empty($places)): ?>
            <?php $__currentLoopData = $places; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$place): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="item" data-number="<?php echo e($key); ?>">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" name="places[<?php echo e($key); ?>][title]" class="form-control" value="<?php echo e($place['title'] ?? ""); ?>" placeholder="<?php echo e(__('Eg: Famous Landmark')); ?>">
                        </div>
                        <div class="col-md-6">
                            <?php echo \Modules\Media\Helpers\FileHelper::fieldUpload("places[$key][image]", $place['image'] ?? ''); ?>

                        </div>
                        <div class="col-md-1">
                                <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </div>
    <div class="text-right">
            <span class="btn btn-info btn-sm btn-add-item"><i class="icon ion-ios-add-circle-outline"></i> <?php echo e(__('Add item')); ?></span>
    </div>
    <div class="g-more hide">
        <div class="item" data-number="__number__">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" __name__="places[__number__][title]" class="form-control" placeholder="<?php echo e(__('Eg: Additional Place')); ?>">
                </div>
                <div class="col-md-6">
                    <?php echo \Modules\Media\Helpers\FileHelper::fieldUpload('places[__number__][image]', ''); ?>

                </div>
                <div class="col-md-1">
                    <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/riyaoeiu/public_html/modules/Tour/Views/admin/tour/places-to-visit.blade.php ENDPATH**/ ?>