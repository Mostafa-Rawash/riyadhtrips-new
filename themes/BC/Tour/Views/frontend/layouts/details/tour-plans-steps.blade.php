@php
 if (!empty($translation->plans)) {
    $title = __("Sample Itinerary");
 }
 use Modules\Media\Helpers\FileHelper;

@endphp
@if (!empty($title))
<div class="g-include-exclude">
    <h3 class="section-title">{{ $title }}</h3>
    <div class="row">
        @if ($translation->plans)
            <div class="col-lg-12 col-md-12">
                {{-- Tab Navigation --}}
                <ul class="nav nav-tabs" id="plansTab" role="tablist">
                    @foreach ($translation->plans as $planKey => $plan)
                        @if (is_array($plan) && isset($plan['name']) && $planKey !== '__plan_number__')
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="plan-{{ $planKey }}-tab"
                                    data-bs-toggle="tab" 
                                    data-bs-target="#plan-{{ $planKey }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="plan-{{ $planKey }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ $plan['name'] }}
                                </button>
                            </li>
                        @endif
                    @endforeach
                </ul>
                
                {{-- Tab Content --}}
                <div class="tab-content" id="plansTabContent">
                    @foreach ($translation->plans as $planKey => $plan)
                        @if (is_array($plan) && isset($plan['steps']) && $planKey !== '__plan_number__')
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="plan-{{ $planKey }}"
                                role="tabpanel"
                                aria-labelledby="plan-{{ $planKey }}-tab">
                                <div class="accordion plan-steps" id="accordion-plan-{{ $planKey }}">
                                    @foreach ($plan['steps'] as $stepKey => $step)
                                        @if (is_array($step) && isset($step['title']))
                                        <div class="row accordion-parent">
                                        <span class="step-day">{{ $step['day'] }}</span>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading-{{ $planKey }}-{{ $stepKey }}">
                                                    <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#collapse-{{ $planKey }}-{{ $stepKey }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="collapse-{{ $planKey }}-{{ $stepKey }}">
                                                    <i class="fa fa-circle step-icon"></i>
                                                            <span class="step-title">{{ $step['title'] }}</span>
                                                    </button>
                                                </h2>
                                                <div id="collapse-{{ $planKey }}-{{ $stepKey }}" 
                                                     class="accordion-collapse collapse" 
                                                     aria-labelledby="heading-{{ $planKey }}-{{ $stepKey }}" 
                                                     data-bs-parent="#accordion-plan-{{ $planKey }}">
                                                    <div class="accordion-body">
                                                        {{ $step['description'] }}
                                                        @if (!empty($step['image_url']))
                                                        <div class="step-images">
                                                            <img src="{{ FileHelper::url($step['image_url']) }}" alt="{{ $step['title'] }}" class="place-image">
                                                        </div>
                                                       
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endif