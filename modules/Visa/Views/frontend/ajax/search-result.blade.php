<div class="row">
    @if($rows->total() > 0)
        @foreach($rows as $row)
            <div class="col-lg-6 col-md-6">
                @include('Visa::frontend.layouts.search.loop-grid')
            </div>
        @endforeach
    @else
        <div class="col-lg-12">
            <div class="alert alert-warning">
                {{__("No visa application found")}}
            </div>
        </div>
    @endif
</div>
<div class="bravo-pagination">
    {{$rows->appends(request()->query())->links()}}
    @if($rows->total() > 0)
        <span class="count-string">{{ __("Showing :from - :to of :total visa applications",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
    @endif
</div>