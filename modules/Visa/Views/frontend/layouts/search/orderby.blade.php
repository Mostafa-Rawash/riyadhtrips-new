<div class="item-search-box dropdown">
    <span class="sort-text">{{__("Sort by:")}}</span>
    <div class="dropdown-toggle" role="button" data-toggle="dropdown" aria-expanded="false">
        @switch(Request::input('orderby'))
            @case("newest")
                {{__("Newest")}}
                @break
            @case("oldest")
                {{__("Oldest")}}
                @break
            @default
                {{__("Newest")}}
        @endswitch
    </div>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
        <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['orderby' => 'newest']) }}">{{__("Newest")}}</a>
        <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['orderby' => 'oldest']) }}">{{__("Oldest")}}</a>
    </div>
</div>