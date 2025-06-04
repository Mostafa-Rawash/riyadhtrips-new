@if($row->getGallery())
<div class="g-gallery">
    <div class="fotorama" data-width="100%" data-thumbwidth="135" data-thumbheight="135" data-thumbmargin="15" data-nav="thumbs" data-allowfullscreen="true">
        <a href="{{$row->getBannerImageUrlAttribute('full')}}" data-thumb="{{$row->getBannerImageUrlAttribute('full')}}" data-alt="{{ __("Gallery") }}">
            <img src="{{$row->getBannerImageUrlAttribute('full')}}" alt="{{ __("Gallery") }}">
        </a>

        @foreach($row->getGallery() as $key=>$item)
        <a href="{{$item['large']}}" data-thumb="{{$item['thumb']}}" data-alt="{{ __("Gallery") }}">
            <img src="{{$item['thumb']}}" alt="{{ __("Gallery") }}">
        </a>
        @endforeach
    </div>

</div>

@endif
