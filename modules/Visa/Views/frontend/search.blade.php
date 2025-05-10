@extends('layouts.app')
@section('head')
    <link href="{{ asset('module/visa/css/visa.css?_ver='.config('app.asset_version')) }}" rel="stylesheet">
@endsection
@section('content')
    <div class="bravo_search_visa">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-12">
                    @include('Visa::frontend.layouts.search.filter-search')
                </div>
                <div class="col-lg-9 col-md-12">
                    <div class="bravo-list-item">
                        <div class="topbar-search">
                            <h2 class="text">
                                @if($rows->total() > 1)
                                    {{ __(":count visa applications found",['count'=>$rows->total()]) }}
                                @else
                                    {{ __(":count visa application found",['count'=>$rows->total()]) }}
                                @endif
                            </h2>
                            <div class="control">
                                @include('Visa::frontend.layouts.search.orderby')
                            </div>
                        </div>
                        <div class="list-item">
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
                        </div>
                        <div class="bravo-pagination">
                            {{$rows->appends(request()->query())->links()}}
                            @if($rows->total() > 0)
                                <span class="count-string">{{ __("Showing :from - :to of :total visa applications",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()]) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script type="text/javascript" src="{{ asset('module/visa/js/visa.js?_ver='.config('app.asset_version')) }}"></script>
@endsection