@php
if(!empty($translation->include)){
$title = __("Included");
}
if(!empty($translation->exclude)){
$title = __("Excluded");
}
if(!empty($translation->exclude) and !empty($translation->include)){
$title = __("Included/Excluded");
}
@endphp
@if(!empty($title))
<div class="g-include-exclude">
    <div class="row">
        @if($translation->include)
        <h3 class="section-title">{{ __("Package include") }}</h3>
        <div class="col-lg-12 col-md-12">
            <div class="tab-content" id="includeTabContent">
                <div class="tab-pane fade show active" id="include" role="tabpanel" aria-labelledby="include-tab">
                    <div class="accordion include-items" id="accordion-include">
                        @foreach($translation->include as $item)
                        <div class="row accordion-parent">
                            <span class="step-day"></span>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-include-{{ $loop->index }}">
                                    <button class="accordion-button {{ !empty($item['description']) ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-include-{{ $loop->index }}" aria-expanded="{{ !empty($item['description']) ? 'true' : 'false' }}" aria-controls="collapse-include-{{ $loop->index }}">
                                        <i class="fa fa-circle step-icon"></i>
                                        <span class="step-title">{{ $item['title'] }}</span>
                                    </button>
                                </h2>
                                <div id="collapse-include-{{ $loop->index }}" class="accordion-collapse collapse {{ !empty($item['description']) ? 'show' : '' }}" aria-labelledby="heading-include-{{ $loop->index }}" data-bs-parent="#accordion-include">
                                    <div class="accordion-body">
                                        {{ $item['description'] ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($translation->exclude)
        <h3 class="section-title">{{ __("Package exclude") }}</h3>
        <div class="col-lg-12 col-md-12">

            <div class="tab-content" id="excludeTabContent">
                <div class="tab-pane fade show active" id="exclude" role="tabpanel" aria-labelledby="exclude-tab">
                    <div class="accordion exclude-items" id="accordion-exclude">
                        @foreach($translation->exclude as $item)
                        <div class="row accordion-parent">
                            <span class="step-day"></span>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-exclude-{{ $loop->index }}">
                                    <button class="accordion-button {{ !empty($item['description']) ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-exclude-{{ $loop->index }}" aria-expanded="{{ !empty($item['description']) ? 'true' : 'false' }}" aria-controls="collapse-exclude-{{ $loop->index }}">
                                        <i class="fa fa-circle step-icon"></i>
                                        <span class="step-title">{{ $item['title'] }}</span>
                                    </button>
                                </h2>
                                <div id="collapse-exclude-{{ $loop->index }}" class="accordion-collapse collapse {{ !empty($item['description']) ? 'show' : '' }}" aria-labelledby="heading-exclude-{{ $loop->index }}" data-bs-parent="#accordion-exclude">
                                    <div class="accordion-body">
                                        {{ $item['description'] ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif