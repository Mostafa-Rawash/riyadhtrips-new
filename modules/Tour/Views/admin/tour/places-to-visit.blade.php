@php
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
@endphp

<div class="form-group-item">
    <label class="control-label">{{__('Places to Visit')}}</label>
    <div class="g-items-header">
        <div class="row">
            <div class="col-md-5 text-left">{{__("Title")}}</div>
            <div class="col-md-6 text-left">{{__("Image")}}</div>
            <div class="col-md-1"></div>
        </div>
    </div>
    <div class="g-items">
        @if(!empty($places))
            @foreach($places as $key=>$place)
                <div class="item" data-number="{{$key}}">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" name="places[{{$key}}][title]" class="form-control" value="{{$place['title'] ?? ""}}" placeholder="{{__('Eg: Famous Landmark')}}">
                        </div>
                        <div class="col-md-6">
                            {!! \Modules\Media\Helpers\FileHelper::fieldUpload("places[$key][image]", $place['image'] ?? '') !!}
                        </div>
                        <div class="col-md-1">
                                <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div class="text-right">
            <span class="btn btn-info btn-sm btn-add-item"><i class="icon ion-ios-add-circle-outline"></i> {{__('Add item')}}</span>
    </div>
    <div class="g-more hide">
        <div class="item" data-number="__number__">
            <div class="row">
                <div class="col-md-5">
                    <input type="text" __name__="places[__number__][title]" class="form-control" placeholder="{{__('Eg: Additional Place')}}">
                </div>
                <div class="col-md-6">
                    {!! \Modules\Media\Helpers\FileHelper::fieldUpload('places[__number__][image]', '') !!}
                </div>
                <div class="col-md-1">
                    <span class="btn btn-danger btn-sm btn-remove-item"><i class="fa fa-trash"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>
