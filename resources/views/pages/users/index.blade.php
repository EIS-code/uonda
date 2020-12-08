@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
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
        <table style="width: 100%;" id="example" class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Status</th>
                <th>Registered On</th>
                <th>User Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($users as $key => $user)
                    <tr>
                        <td>{{ ucfirst($user->name) }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->gender == 'f' ? Female : ($user->gender == 'm') ? Male : '-'  }}</td>
                        <td>{{ Config::get('globalConstant.status')[$user->current_status] }}</td>
                        <td>{{ Carbon\Carbon::parse($user->created_at)->format('jS M Y') }}</td>
                        <td>
                            <input type="checkbox" {{ $user->is_enable == 1 ? 'checked' : ''}} data-id="{{ $user->encrypted_user_id }}" class="user_status" value="{{ $user->is_enable }}" data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                        </td>
                        <td class="icons_list">
                            @if($user->is_accepted)
                                <a href="javascript:void(0)" class="rejectModal" data-id="{{ $user->encrypted_user_id }}" title="Reject User"><i class="fa fa-times" aria-hidden="true"></i></a> 
                            @else 
                                <a href="javascript:void(0)" class="acceptUser" data-id="{{ $user->encrypted_user_id }}" title="Accept User"><i class="fa fa-check" aria-hidden="true"></i></a> 
                            @endif
                            <a href="javascript:void(0)" class="remove-button" title="Delete User"><i class="faicons mdi mdi-delete delete-button"></i></a>
                            <a href="{{ route('users.show', $user->encrypted_user_id) }}" title="Show User Details"><i class="faicons mdi mdi-eye"></i></a>
                            <form id="remove-form" action="{{ route('users.destroy', $user->encrypted_user_id) }}" method="POST" class="d-none">
                            @csrf
                            {{ method_field('DELETE') }}
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('custom-scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.remove-button').on('click', function() {
    		if(confirm('Are you sure you want to delete this?')) {
                $('#remove-form').submit();
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