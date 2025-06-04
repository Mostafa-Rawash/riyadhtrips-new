<?php
// Initialize default plans structure
$defaultPlans = [
];

// Use existing plans or default
$plans = $translation->plans;
if (!is_array($translation->plans)) {
    $plans = json_decode(old('plans', default: $translation->plans), true);
}
if (empty($plans)) {
    $plans = $defaultPlans;
    $translation->plans = $defaultPlans;
}
if(isset($plans['__plan_number__'])){
    unset($plans['__plan_number__']);
}
?>


<div class="form-group-item plans-container">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <label class="control-label h5 mb-0"><?php echo e(__('Plans and Steps')); ?></label>
        <button type="button" class="btn btn-success btn-sm btn-add-plan">
            <i class="icon ion-ios-add-circle-outline"></i>
            <?php echo e(__('Add New Plan')); ?>

        </button>
    </div>

    
    <div class="plans-wrapper">
        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan_key => $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="plan-item card mb-3">
            
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-11">
                        <input type="text"
                            name="plans[<?php echo e($plan_key); ?>][name]"
                            class="form-control"
                            value="<?php echo e($plan['name'] ?? ''); ?>"
                            placeholder="<?php echo e($plan['name'] ?? __('Enter Plan Name')); ?>">
                    </div>
                    <div class="col-md-1 text-right">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-plan">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="steps-wrapper">
                    
                    <div class="g-items-header bg-light rounded p-2 mb-3">
                        <div class="row">
                            <div class="col-md-1"><?php echo e(__("Day")); ?></div>
                            <div class="col-md-3"><?php echo e(__("Title")); ?></div>
                            <div class="col-md-4"><?php echo e(__("Description")); ?></div>
                            <div class="col-md-3"><?php echo e(__("Image URL")); ?></div>
                            <div class="col-md-1"><?php echo e(__("Actions")); ?></div>
                        </div>
                    </div>

                    
                    <div class="g-items">
                        <?php if(isset($plan['steps']) && !empty($plan['steps']) && is_array($plan['steps'])): ?>
                        <?php $__currentLoopData = $plan['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step_key => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="item border-bottom py-2" data-number="<?php echo e($step_key); ?>">
                            <div class="row align-items-center">
                            <div class="col-md-1">
                                    <input type="text"
                                        name="plans[<?php echo e($plan_key); ?>][steps][<?php echo e($step_key); ?>][day]"
                                        class="form-control"
                                        value="<?php echo e($step['day'] ?? ''); ?>"
                                        placeholder="<?php echo e(__('Enter day')); ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="text"
                                        name="plans[<?php echo e($plan_key); ?>][steps][<?php echo e($step_key); ?>][title]"
                                        class="form-control"
                                        value="<?php echo e($step['title'] ?? ''); ?>"
                                        placeholder="<?php echo e(__('Enter title')); ?>">
                                </div>
                                <div class="col-md-4">
                                    <textarea name="plans[<?php echo e($plan_key); ?>][steps][<?php echo e($step_key); ?>][description]"
                                        class="form-control"
                                        rows="2"
                                        placeholder="<?php echo e(__('Enter description')); ?>"><?php echo e($step['description'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-3">
                                    <?php echo \Modules\Media\Helpers\FileHelper::fieldUpload('plans['.$plan_key.'][steps]['.$step_key.'][image_url]', old('plans['.$plan_key.'][steps]['.$step_key.'][image_url]', $step['image_url'] ?? '')); ?>

                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-step">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>

                    
                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-info btn-sm btn-add-step">
                            <i class="icon ion-ios-add-circle-outline"></i>
                            <?php echo e(__('Add Step')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>


<div class="d-none">
    
    <div class="plan-template">
        <div class="plan-item card mb-3">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-11">
                        <input type="text" name="plans[__plan_number__][name]" class="form-control" placeholder="<?php echo e(__('Enter Plan Name')); ?>">
                    </div>
                    <div class="col-md-1 text-right">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-plan">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="steps-wrapper">
                    <div class="g-items-header bg-light rounded p-2 mb-3">
                        <div class="row">
                            <div class="col-md-1"><?php echo e(__("Day")); ?></div>
                            <div class="col-md-3"><?php echo e(__("Title")); ?></div>
                            <div class="col-md-4"><?php echo e(__("Description")); ?></div>
                            <div class="col-md-3"><?php echo e(__("Image URL")); ?></div>
                            <div class="col-md-1"><?php echo e(__("Actions")); ?></div>
                        </div>
                    </div>
                    <div class="g-items"></div>
                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-info btn-sm btn-add-step">
                            <i class="icon ion-ios-add-circle-outline"></i>
                            <?php echo e(__('Add Step')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="step-template">
        <div class="item border-bottom py-2" data-number="__step_number__">
            <div class="row align-items-center">
                <div class="col-md-1">
                    <input type="text" name="plans[__plan_number__][steps][__step_number__][day]" class="form-control" placeholder="<?php echo e(__('Enter day')); ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="plans[__plan_number__][steps][__step_number__][title]" class="form-control" placeholder="<?php echo e(__('Enter title')); ?>">
                </div>
                <div class="col-md-4">
                    <textarea name="plans[__plan_number__][steps][__step_number__][description]" class="form-control" rows="2" placeholder="<?php echo e(__('Enter description')); ?>"></textarea>
                </div>
                <div class="col-md-3">
                    <?php echo \Modules\Media\Helpers\FileHelper::fieldUpload('plans[__plan_number__][steps][__step_number__][image_url]', old('plans[__plan_number__][steps][__step_number__][image_url]')); ?>

                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-remove-step">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('js'); ?>
<script>
    (function($) {
        'use strict';

        class PlanStepManager {
            constructor() {
                this.plansWrapper = $('.plans-wrapper');
                this.planTemplate = $('.plan-template').html();
                this.stepTemplate = $('.step-template').html();
                this.initializeEventListeners();
            }

            initializeEventListeners() {
                $(document)
                    .on('click', '.btn-add-plan', this.handleAddPlan.bind(this))
                    .on('click', '.btn-add-step', this.handleAddStep.bind(this))
                    .on('click', '.btn-remove-plan', this.handleRemovePlan.bind(this))
                    .on('click', '.btn-remove-step', this.handleRemoveStep.bind(this));
            }

            handleAddPlan() {
                const planCount = this.plansWrapper.find('.plan-item').length;
                const template = this.planTemplate.replace(/__plan_number__/g, planCount);
                this.plansWrapper.append(template);
                return false; // Prevent default form submission
            }

            handleAddStep(e) {
                const planItem = $(e.currentTarget).closest('.plan-item');
                const planIndex = this.plansWrapper.find('.plan-item').index(planItem);
                const stepsContainer = planItem.find('.g-items');
                const stepCount = stepsContainer.find('.item').length;

                const template = this.stepTemplate
                    .replace(/__plan_number__/g, planIndex)
                    .replace(/__step_number__/g, stepCount);

                stepsContainer.append(template);
                return false; // Prevent default form submission
            }

            handleRemovePlan(e) {
                if (confirm('Are you sure you want to remove this plan?')) {
                    $(e.currentTarget).closest('.plan-item').remove();
                    this.reindexPlans();
                }
                return false; // Prevent default form submission
            }

            handleRemoveStep(e) {
                if (confirm('Are you sure you want to remove this step?')) {
                    const stepElement = $(e.currentTarget).closest('.item');
                    const planItem = stepElement.closest('.plan-item');
                    stepElement.remove();
                    this.reindexSteps(planItem);
                }
                return false; // Prevent default form submission
            }

            reindexPlans() {
                this.plansWrapper.find('.plan-item').each((planIndex, element) => {
                    $(element).find('[name^="plans["]').each((_, input) => {
                        const name = $(input).attr('name');
                        const newName = name.replace(/plans\[\d+\]/, `plans[${planIndex}]`);
                        $(input).attr('name', newName);
                    });
                    this.reindexSteps($(element));
                });
            }

            reindexSteps(planItem) {
                const planIndex = this.plansWrapper.find('.plan-item').index(planItem);
                planItem.find('.g-items .item').each((stepIndex, element) => {
                    $(element).attr('data-number', stepIndex);
                    $(element).find('[name^="plans["]').each((_, input) => {
                        const name = $(input).attr('name');
                        const newName = name.replace(
                            /plans\[\d+\]\[steps\]\[\d+\]/,
                            `plans[${planIndex}][steps][${stepIndex}]`
                        );
                        $(input).attr('name', newName);
                    });
                });
            }
        }

        // Initialize on document ready
        $(document).ready(function() {
            new PlanStepManager();
        });
    })(jQuery);
</script>
<?php $__env->stopPush(); ?><?php /**PATH /home/riyaoeiu/public_html/modules/Tour/Views/admin/tour/plans-steps.blade.php ENDPATH**/ ?>