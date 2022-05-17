@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-apartment icon-gradient bg-sunny-morning"></i>
            </div>
            <div>Cities
            </div>
        </div>
        
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <a href="{{ route('city.create') }}">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add City
                </button>
                </a>
            </div>
        </div>
    </div>
</div> 
@foreach (['danger', 'warning', 'success', 'info'] as $msg)
    @if(Session::has('alert-' . $msg))
        <div class="alert alert-{{ $msg }} alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {!! Session::get('alert-' . $msg) !!}
        </div>
    @endif
@endforeach   
<div class="main-card mb-3 card">
    <div class="card-body">
        <div class="title_right">
            <form action="" method="get">
                <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                    <div class="input-group">
                        <input type="hidden" value="0" name="page"/>
                        <input type="hidden" value="{{request('sortBy')}}" name="sortBy"/>
                        <input type="hidden" value="{{request('sortOrder')}}" name="sortOrder"/>
                        <input type="text" class="form-control round-border search-component" name="search"
                                placeholder="Search for City" id="search" value="{{ request('search') }}">
                        <span class="input-group-btn">
                            <button class="mb-2 mr-2 btn btn-primary" type="submit">Search</button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
        <table style="width: 100%;" class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('city.index'),1,'id',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}"># {!! Helper::sortingDesign('id',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('city.index'),1,'name',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Name {!! Helper::sortingDesign('name',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('city.index'),1,'state_id',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">State {!! Helper::sortingDesign('state_id',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('city.index'),1,'country_id',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Country {!! Helper::sortingDesign('country_id',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('city.index'),1,'created_at',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Registered On {!! Helper::sortingDesign('created_at',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($cities as $key => $city)
                    <tr>
                        <td>{{ Helper::listIndex($cities->currentPage(), $cities->perPage(), $key) }}</td>
                        <td>{{ ucfirst($city->name) }}</td>
                        <td>{{ ucfirst($city->state->name) }}</td>
                        <td>{{ ucfirst($city->state->country->name) }}</td>
                        <td>{{ !empty($city->created_at) ? Carbon\Carbon::parse($city->created_at)->format('jS M Y') : '-' }}</td>
                        <td class="icons_list">
                            <a href="{{ route('city.edit', $city->encrypted_city_id) }}" title="Edit City"><span class="material-icons">edit</span></a> 
                            <a href="javascript:void(0)" class="remove-button" data-id="{{ $city->id }}" title="Delete City"><span class="material-icons delete-button">delete</span></a>
                            <form id="remove-form-{{ $city->id }}" action="{{ route('city.destroy', $city->encrypted_city_id) }}" method="POST" class="d-none">
                            @csrf
                            {{ method_field('DELETE') }}
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="row">
            <div class="col-sm-5">
                <div class="dataTables_info pagination-info">{{ Helper::paginationSummary($cities->currentPage(), $cities->perPage(), $cities->total()) }}</div>
            </div>
            <div class="col-sm-7">
            {{ $cities->appends(['sortBy' => request('sortBy'), 'sortOrder' => request('sortOrder'), 'search' => request('search')])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
@push('custom-scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.remove-button').on('click', function() {
            var delete_id = $(this).attr('data-id');
    		if(confirm('Are you sure you want to delete this?')) {
                $('#remove-form-'+delete_id).submit();
            }
    	});
    });
</script>
@endpush