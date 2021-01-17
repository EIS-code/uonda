@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-graduation-hat icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>Users
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
                                placeholder="Search for..." id="search" value="{{ request('search') }}">
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
                    <a href="{{Helper::generateURLWithFilter(route('users.index'),1,'id',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}"># {!! Helper::sortingDesign('id',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index'),1,'name',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Name {!! Helper::sortingDesign('name',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index'),1,'email',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Email {!! Helper::sortingDesign('email',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index'),1,'gender',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Gender {!! Helper::sortingDesign('gender',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index'),1,'current_status',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Status {!! Helper::sortingDesign('current_status',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index'),1,'created_at',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Registered On {!! Helper::sortingDesign('created_at',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>User Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($users as $key => $user)
                    <tr>
                        <td>{{ Helper::listIndex($users->currentPage(), $users->perPage(), $key) }}</td>
                        <td>{{ ucfirst($user->name) }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ !empty($user->gender) ? $user->gender : '-' }}</td>
                        <td>{{ Config::get('globalConstant.status')[$user->current_status] }}</td>
                        <td>{{ Carbon\Carbon::parse($user->created_at)->format('jS M Y') }}</td>
                        <td>
                            <input type="checkbox" {{ $user->is_enable == 1 ? 'checked' : ''}} data-id="{{ $user->encrypted_user_id }}" class="user_status" value="{{ $user->is_enable }}" data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                        </td>
                        <td class="icons_list">
                            @if($user->is_accepted)
                                <a href="javascript:void(0)" class="rejectModal" data-id="{{ $user->encrypted_user_id }}" title="Reject User"><span class="material-icons">close</span></a> 
                            @else 
                                <a href="javascript:void(0)" class="acceptUser" data-id="{{ $user->encrypted_user_id }}" title="Accept User"><span class="material-icons">done</span></a> 
                            @endif
                            <a href="javascript:void(0)" class="remove-button" data-id="{{ $user->id }}" title="Delete User"><span class="material-icons delete-button">delete</span></a>
                            <a href="{{ route('users.show', $user->encrypted_user_id) }}" title="Show User Details"><span class="material-icons">visibility</span></a>
                            <form id="remove-form-{{ $user->id }}" action="{{ route('users.destroy', $user->encrypted_user_id) }}" method="POST" class="d-none">
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
                <div class="dataTables_info pagination-info">{{ Helper::paginationSummary($users->currentPage(), $users->perPage(), $users->total()) }}</div>
            </div>
            <div class="col-sm-7">
            {{ $users->appends(['sortBy' => request('sortBy'), 'sortOrder' => request('sortOrder'), 'search' => request('search')])->links() }}
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
        $('.rejectModal').on('click', function() {
    		$('#rejectionModal').attr('data-id', $(this).attr('data-id'));
    		$('#rejectionModal').show();
        });
        $('.toggle').on('click', function() {
            var attr = $(this).children('.user_status').attr('checked');
            if(typeof attr !== typeof undefined && attr !== false) {
                $(this).children('.user_status').attr('checked', false); ;
                $(this).children('.user_status').val('0');
                $(this).children('.user_status').trigger('change');
            } else {
                $(this).children('.user_status').attr('checked', true); ;
                $(this).children('.user_status').val('1');
                $(this).children('.user_status').trigger('change');
            }
        });
        $('.user_status').on('change', function() {
            var user_id = $(this).attr('data-id');
            if(user_id) {
                var url = " {{ url('users') }}/" + user_id;
                $.ajax({
                    url: url,
                    type: "POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: { 'user_status' : $(this).val(), '_method' : "PUT"}, 
                    success: function(data) {
                        if(data.status == 200) {
                            location.reload();
                        }
                    },
                    error: function(error) {
                        if(error.status == 400) {
                            alert(error.responseJSON.error);
                        }
                    }
                });
            }
        })
        $('.acceptUser').on('click', function() {
            var user_id = $(this).attr('data-id');
            if(user_id) {
                var url = " {{ url('users') }}/" + user_id;
                $.ajax({
                    url: url,
                    type: "POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: { 'ís_accepted' : 1, '_method' : "PUT"}, 
                    success: function(data) {
                        if(data.status == 200) {
                            location.reload();
                        }
                    },
                    error: function(error) {
                        if(error.status == 400) {
                            alert(error.responseJSON.error);
                        }
                    }
                });
            }
        })
    });
</script>
@endpush