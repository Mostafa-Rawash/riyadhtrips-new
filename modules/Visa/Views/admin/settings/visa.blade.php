<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{ __('General Options') }}</h3>
        <p class="form-group-desc">{{ __('General settings for visa applications') }}</p>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-body">
                <div class="form-group">
                    <label class="">{{ __('Disable visa module?') }}</label>
                    <div class="form-controls">
                        <label><input type="checkbox" name="visa_disable" value="1" @if(!empty($settings['visa_disable'])) checked @endif /> {{ __('Yes') }} </label>
                        <br>
                        <small class="form-text text-muted">{{ __('Check to disable visa module throughout the site') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{ __('Visa Page Options') }}</h3>
        <p class="form-group-desc">{{ __('Options for visa listing and search pages') }}</p>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-body">
                <div class="form-group">
                    <label>{{ __('Visa Search Page Title') }}</label>
                    <div class="form-controls">
                        <input type="text" class="form-control" name="visa_page_search_title" value="{{ $settings['visa_page_search_title'] ?? 'Visa Applications' }}" placeholder="{{ __('Visa Applications') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __('Visa Search Banner Image') }}</label>
                    <div class="form-group-input">
                        {!! \Modules\Media\Helpers\FileHelper::fieldUpload('visa_page_search_banner',$settings['visa_page_search_banner'] ?? '') !!}
                    </div>
                </div>
                <div class="form-group">
                    <label>{{ __('Number of visa items per page') }}</label>
                    <div class="form-controls">
                        <input type="number" min="1" step="1" class="form-control" name="visa_page_limit_item" value="{{ $settings['visa_page_limit_item'] ?? '9' }}" placeholder="{{ __('Items per page') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <h3 class="form-group-title">{{ __('Visa Single Page') }}</h3>
        <p class="form-group-desc">{{ __('Change settings for single visa application page') }}</p>
    </div>
    <div class="col-sm-8">
        <div class="panel">
            <div class="panel-body">
                <div class="form-group">
                    <label class="">{{ __('Enable review for visa?') }}</label>
                    <div class="form-controls">
                        <label><input type="checkbox" name="visa_enable_review" value="1" @if(!empty($settings['visa_enable_review'])) checked @endif /> {{ __('Yes') }} </label>
                        <br>
                        <small class="form-text text-muted">{{ __('Check to enable review in single visa application page') }}</small>
                    </div>
                </div>
                <div class="form-group">
                    <label class="">{{ __('Enable extra price?') }}</label>
                    <div class="form-controls">
                        <label><input type="checkbox" name="visa_enable_extra_price" value="1" @if(!empty($settings['visa_enable_extra_price'])) checked @endif /> {{ __('Yes') }} </label>
                        <br>
                        <small class="form-text text-muted">{{ __('Check to enable extra price selections on frontend') }}</small>
                    </div>
                </div>
                <div class="form-group">
                    <label class="">{{ __('Enable service fee?') }}</label>
                    <div class="form-controls">
                        <label><input type="checkbox" name="visa_enable_service_fee" value="1" @if(!empty($settings['visa_enable_service_fee'])) checked @endif /> {{ __('Yes') }} </label>
                        <br>
                        <small class="form-text text-muted">{{ __('Check to enable service fee calculations') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
