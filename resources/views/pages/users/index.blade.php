@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-graduation-hat icon-gradient bg-happy-itmeo"></i>
            </div>
            <div>{{ ucfirst($type)}} Users
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
                    <a href="{{Helper::generateURLWithFilter(route('users.index', $type),1,'id',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}"># {!! Helper::sortingDesign('id',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index', $type),1,'name',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Name {!! Helper::sortingDesign('name',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index', $type),1,'email',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Email {!! Helper::sortingDesign('email',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index', $type),1,'gender',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Gender {!! Helper::sortingDesign('gender',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index', $type),1,'current_status',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Status {!! Helper::sortingDesign('current_status',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>
                    <a href="{{Helper::generateURLWithFilter(route('users.index', $type),1,'created_at',(request('sortOrder','asc')=='asc'?'desc':'asc'),request('search'))}}">Registered On {!! Helper::sortingDesign('created_at',request('sortBy'),request('sortOrder')) !!}</a>
                </th>
                <th>User Status</th>
                @if ($type == 'accepted')
                    <th>
                        Free For Use
                    </th>
                @endif
                <th>
                    Email Verified
                </th>
                <th>
                    In App Purchase
                </th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($users as $key => $user)
                    <tr id="tr-user-list-{{ $user->id }}">
                        <td>{{ Helper::listIndex($users->currentPage(), $users->perPage(), $key) }}</td>
                        <td>{{ ucfirst($user->name) }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ !empty($user->gender) ? $user->gender : '-' }}</td>
                        <td>{{ Config::get('globalConstant.status')[$user->current_status] }}</td>
                        <td>{{ Carbon\Carbon::parse($user->created_at)->format('jS M Y') }}</td>
                        <td>
                            <input type="checkbox" {{ $user->is_enable == $user::IS_ENABLED ? 'checked' : ''}} data-id="{{ $user->encrypted_user_id }}" class="user_status" value="{{ $user->is_enable }}" data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                        </td>
                        @if ($type == 'accepted')
                            <td>
                                <input type="checkbox" {{ $user->free_for_use_flag == $user::FREE_FOR_USE_FLAG_YES ? 'checked' : ''}} data-id="{{ $user->encrypted_user_id }}" class="free_for_use_flag" value="{{ $user->free_for_use_flag }}" data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                            </td>
                        @endif
                        <td>
                            {{ !empty($user->email_verified_at) ? 'Yes' : 'No' }}
                        </td>
                        <td style="display: flex; border: none;">
                            <div style="width: 90%;">
                                &nbsp;
                            </div>
                            <div style="width: 10%;">
                                <a href="{{ route('user.check.iap.ios', $user->id) }}" data-user-id="{ $user->id }}" class="show_in_app_purchase">
                                    <i class="fa fa-eye" style="font-size:20px; padding-top: 5px;"></i>
                                    <i class="fa fa-eye-slash" style="font-size:20px; display: none; color:red; padding-top: 5px;"></i>
                                </a>
                            </div>
                        </td>
                        <td class="icons_list">
                            @if($user->is_accepted == 0)
                                <a href="javascript:void(0)" class="acceptUser" data-id="{{ $user->encrypted_user_id }}" title="Accept User"><span class="material-icons">done</span></a> 
                                <a href="javascript:void(0)" class="rejectModal" data-id="{{ $user->encrypted_user_id }}" title="Reject User"><span class="material-icons">close</span></a> 
                            @elseif($user->is_accepted == 1) 
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
        let isEnabled  = "{{ auth()->user()::IS_ENABLED }}",
            isDisabled = "{{ auth()->user()::IS_DISABLED }}",
            ffuFlagYes = "{{ auth()->user()::FREE_FOR_USE_FLAG_YES }}",
            ffuFlagNo  = "{{ auth()->user()::FREE_FOR_USE_FLAG_NO }}";

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
            let attr  = $(this).children('.user_status').prop('checked'),
                attr1 = $(this).children('.free_for_use_flag').prop('checked');

            if (typeof attr !== typeof undefined) {
                $(this).children('.user_status').trigger('change')
            }

            if (typeof attr1 !== typeof undefined) {
                $(this).children('.free_for_use_flag').trigger('change')
            }

            if (attr === false) {
                /* $(this).children('.user_status').prop('checked', true);
                $(this).children('.user_status').val('0'); */
                // $(this).children('.user_status').trigger('change');
            } else {
                /* $(this).children('.user_status').prop('checked', false);
                $(this).children('.user_status').val('1'); */
                // $(this).children('.user_status').trigger('change');
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
                    data: { 'user_status' : ($(this).prop('checked') === true ? isDisabled : isEnabled), '_method' : "PUT"}, 
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
        });

        $('.free_for_use_flag').on('change', function() {
            let userId = $(this).attr('data-id');

            if (userId) {
                var url = " {{ url('users') }}/" + userId;
                $.ajax({
                    url: url,
                    type: "POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: { 'free_for_use_flag' : ($(this).prop('checked') === true ? ffuFlagNo : ffuFlagYes), '_method' : "PUT"}, 
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
        });

        $('.acceptUser').on('click', function() {
            var user_id = $(this).attr('data-id');
            if(user_id) {
                var url = " {{ url('users') }}/" + user_id;
                $.ajax({
                    url: url,
                    type: "POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: { 'is_accepted' : 1, '_method' : "PUT"}, 
                    success: function(data) {
                        if (data.status == 200) {
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
        });

        // In app purchase show.
        $(document).find(".show_in_app_purchase").on("click", showInAppPurchase);

        function showInAppPurchase(e) {
            e.preventDefault();

            let self   = $(this),
                target = $(e.target),
                userId = self.data("user-id"),
                route  = self.attr("href");

            if (userId && target.hasClass('fa-eye')) {
                $.ajax({
                    url: route,
                    type: "GET",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(data) {
                        if (data.code == 401) {
                            self.parent('div').prev('div').empty();
                            self.parent('div').prev('div').html(data.msg);

                            self.find('.fa.fa-eye').fadeOut(200);
                            self.find('.fa.fa-eye-slash').fadeIn(100);
                        } else if (data.code == 200) {
                            self.parent('div').prev('div').empty();
                            self.parent('div').prev('div').html("Purchase Date : " + data.data.purchase_date + "<br />" + "Expires Date : " + data.data.expires_date);

                            self.find('.fa.fa-eye').fadeOut(200);
                            self.find('.fa.fa-eye-slash').fadeIn(100);
                        }
                    },
                    error: function(error) {
                        alert(error.responseJSON.message);
                    }
                });
            } else {
                self.parent('div').prev('div').empty();

                self.find('.fa.fa-eye-slash').fadeOut(100);
                self.find('.fa.fa-eye').fadeIn(200);
            }
        }
    });
</script>
@endpush