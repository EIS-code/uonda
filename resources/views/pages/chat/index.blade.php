@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-apartment icon-gradient bg-sunny-morning"></i>
            </div>
            <div>Chat Rooms
            </div>
        </div>
        
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <a href="{{ route('chats.create') }}">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add Chat Room
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
        <table style="width: 100%;" id="example" class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>No</th>
                <th>UUID</th>
                <th>Title</th>
                <th>Assigned Users</th>
                <th>Created by</th>
                <th>Created On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($chat_rooms as $key => $chat)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ $chat->uuid }}</td>
                        <td>{{ $chat->title }}</td>
                        <td>{{ $chat->chat_room_users_count }}</td>
                        <td>{{ !empty($chat->createdBy) ? $chat->createdBy->name : '' }} ({{ $chat->created_by_admin == 1 ? 'ADMIN' : 'USER' }})</td>
                        <td>{{ Carbon\Carbon::parse($chat->created_at)->format('jS M Y') }}</td>
                        <td class="icons_list">
                            <a href="{{ route('chats.edit', $chat->encrypted_chat_id) }}" title="Edit chat"><span class="material-icons">edit</span></a> 
                            <a href="javascript:void(0)" class="remove-button" data-id="{{ $chat->id }}" title="Delete chat"><span class="material-icons delete-button">delete</span></a>
                            <a href="{{ route('chats.show', $chat->encrypted_chat_id)}}" title="Show chat Details"><span class="material-icons">visibility</span></a>
                            <form id="remove-form-{{ $chat->id }}" action="{{ route('chats.destroy', $chat->encrypted_chat_id) }}" method="POST" class="d-none">
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
            var delete_id = $(this).attr('data-id');
    		if(confirm('Are you sure you want to delete this?')) {
                $('#remove-form-'+delete_id).submit();
            }
    	});
    });
</script>
@endpush