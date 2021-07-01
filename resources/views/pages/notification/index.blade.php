@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-apartment icon-gradient bg-sunny-morning"></i>
            </div>
            <div>Notifications
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
                                placeholder="Search for notification" id="search" value="{{ request('search') }}">
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
                    <a href="{{Helper::generateURLWithFilter(route('notification.index'),1,'id',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}"># {!! Helper::sortingDesign('id',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('notification.index'),1,'name',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Title {!! Helper::sortingDesign('title',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('notification.index'),1,'state_id',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Message {!! Helper::sortingDesign('message',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('notification.index'),1,'country_id',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Is Read {!! Helper::sortingDesign('is_read',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('notification.index'),1,'created_at',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Created At {!! Helper::sortingDesign('created_at',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
            </tr>
            </thead>
            <tbody>
                @foreach($notifications as $key => $notification)
                    <tr>
                        <td>{{ Helper::listIndex($notifications->currentPage(), $notifications->perPage(), $key) }}</td>
                        <td>{{ ucfirst($notification->title) }}</td>
                        <td>{{ ucfirst($notification->message) }}</td>
                        @if($notification->is_read == "0")         
                        <td>
                            <form id="formName-{{ $notification->id }}" action="{{ route('notification.read', $notification->id) }}" method="GET">
                                <input type ="checkbox" name="cBox[]" value = "{{$notification->id}}" onchange="this.form.submit()"></input>
                            </form>
                        </td>
                        @else
                            <td>{{ 'Yes' }}</td>        
                        @endif
                        <td>{{ !empty($notification->created_at) ? Carbon\Carbon::parse($notification->created_at)->format('jS M Y') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="row">
            <div class="col-sm-5">
                <div class="dataTables_info pagination-info">{{ Helper::paginationSummary($notifications->currentPage(), $notifications->perPage(), $notifications->total()) }}</div>
            </div>
            <div class="col-sm-7">
            {{ $notifications->appends(['sortBy' => request('sortBy'), 'sortOrder' => request('sortOrder'), 'search' => request('search')])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
@push('custom-scripts')
@endpush