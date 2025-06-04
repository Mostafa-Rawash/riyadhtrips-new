@php
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
@endphp

{{-- Main Container --}}
<div class="form-group-item plans-container">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <label class="control-label h5 mb-0">{{__('Plans and Steps')}}</label>
        <button type="button" class="btn btn-success btn-sm btn-add-plan">
            <i class="icon ion-ios-add-circle-outline"></i>
            {{__('Add New Plan')}}
        </button>
    </div>

    {{-- Plans List --}}
    <div class="plans-wrapper">
        @foreach($plans as $plan_key => $plan)
        <div class="plan-item card mb-3">
            {{-- Plan Header --}}
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-11">
                        <input type="text"
                            name="plans[{{$plan_key}}][name]"
                            class="form-control"
                            value="{{$plan['name'] ?? ''}}"
                            placeholder="{{ $plan['name'] ?? __('Enter Plan Name')}}">
                    </div>
                    <div class="col-md-1 text-right">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-plan">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            {{-- Plan Steps --}}
            <div class="card-body">
                <div class="steps-wrapper">
                    {{-- Steps Header --}}
                    <div class="g-items-header bg-light rounded p-2 mb-3">
                        <div class="row">
                            <div class="col-md-1">{{__("Day")}}</div>
                            <div class="col-md-3">{{__("Title")}}</div>
                            <div class="col-md-4">{{__("Description")}}</div>
                            <div class="col-md-3">{{__("Image URL")}}</div>
                            <div class="col-md-1">{{__("Actions")}}</div>
                        </div>
                    </div>

                    {{-- Steps List --}}
                    <div class="g-items">
                        @if(isset($plan['steps']) && !empty($plan['steps']) && is_array($plan['steps']))
                        @foreach($plan['steps'] as $step_key => $step)
                        <div class="item border-bottom py-2" data-number="{{$step_key}}">
                            <div class="row align-items-center">
                            <div class="col-md-1">
                                    <input type="text"
                                        name="plans[{{$plan_key}}][steps][{{$step_key}}][day]"
                                        class="form-control"
                                        value="{{$step['day'] ?? ''}}"
                                        placeholder="{{__('Enter day')}}">
                                </div>
                                <div class="col-md-3">
                                    <input type="text"
                                        name="plans[{{$plan_key}}][steps][{{$step_key}}][title]"
                                        class="form-control"
                                        value="{{$step['title'] ?? ''}}"
                                        placeholder="{{__('Enter title')}}">
                                </div>
                                <div class="col-md-4">
                                    <textarea name="plans[{{$plan_key}}][steps][{{$step_key}}][description]"
                                        class="form-control"
                                        rows="2"
                                        placeholder="{{__('Enter description')}}">{{$step['description'] ?? ''}}</textarea>
                                </div>
                                <div class="col-md-3">
                                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('plans['.$plan_key.'][steps]['.$step_key.'][image_url]', old('plans['.$plan_key.'][steps]['.$step_key.'][image_url]', $step['image_url'] ?? '')) !!}
                                </div>
                                <div class="col-md-1 text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-step">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>

                    {{-- Add Step Button --}}
                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-info btn-sm btn-add-step">
                            <i class="icon ion-ios-add-circle-outline"></i>
                            {{__('Add Step')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Templates --}}
<div class="d-none">
    {{-- Plan Template --}}
    <div class="plan-template">
        <div class="plan-item card mb-3">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-11">
                        <input type="text" name="plans[__plan_number__][name]" class="form-control" placeholder="{{__('Enter Plan Name')}}">
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
                            <div class="col-md-1">{{__("Day")}}</div>
                            <div class="col-md-3">{{__("Title")}}</div>
                            <div class="col-md-4">{{__("Description")}}</div>
                            <div class="col-md-3">{{__("Image URL")}}</div>
                            <div class="col-md-1">{{__("Actions")}}</div>
                        </div>
                    </div>
                    <div class="g-items"></div>
                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-info btn-sm btn-add-step">
                            <i class="icon ion-ios-add-circle-outline"></i>
                            {{__('Add Step')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Step Template --}}
    <div class="step-template">
        <div class="item border-bottom py-2" data-number="__step_number__">
            <div class="row align-items-center">
                <div class="col-md-1">
                    <input type="text" name="plans[__plan_number__][steps][__step_number__][day]" class="form-control" placeholder="{{__('Enter day')}}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="plans[__plan_number__][steps][__step_number__][title]" class="form-control" placeholder="{{__('Enter title')}}">
                </div>
                <div class="col-md-4">
                    <textarea name="plans[__plan_number__][steps][__step_number__][description]" class="form-control" rows="2" placeholder="{{__('Enter description')}}"></textarea>
                </div>
                <div class="col-md-3">
                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('plans[__plan_number__][steps][__step_number__][image_url]', old('plans[__plan_number__][steps][__step_number__][image_url]')) !!}
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

@push('js')
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
@endpush