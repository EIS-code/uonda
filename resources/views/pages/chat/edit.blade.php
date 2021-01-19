@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit Chat Room
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
        <h5 class="card-title">Edit Chat Room</h5>
        <form id="addChatRoomForm" class="col-md-10 mx-auto" method="POST" action="{{ route('chats.update', $chat_room->encrypted_chat_id) }}" enctype="multipart/form-data">
        @csrf
        {{ method_field('PUT') }}
            <input type="hidden" name="uuid" value="{{ $chat_room->uuid }}">
            <div class="form-group">
                <label for="title">Title</label>
                <div>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" placeholder="Title" value="{{ $chat_room->title }}" />
                    @error('title')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            @if(!empty($chat_room->group_icon_actual))
                <div class="">
                    <img width="250" src="{{ $chat_room->group_icon_actual }}">
                </div>
            @endif
            <div class="form-group">
                <label for="group_icon">Group Icon</label>
                <div>
                    <input type="file" name="group_icon" class="form-control @error('group_icon') is-invalid @enderror" accept="image/*" />
                    @error('group_icon')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="sub_title">Chat Room Users</label>
                <div class="row">
                    <div class="col-sm-12">
                        <select class="users-listing" name="users[]" multiple="multiple">
                            <option value="">Please select</option>
                            @foreach($users as $key => $user)
                                <option value="{{ $user->id }}" {{ in_array($user->id, $chat_room_data) ? 'selected="selected"' : ''}}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="sub_title">Is Group</label>
                <div>
                    <input type="checkbox" name="is_group" {{ $chat_room->is_group == 1 ? 'checked' : ''}} data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="save" value="Save">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('custom-scripts')
<script type="text/javascript">
    $(document).ready(function() {
        //to load the states on selection of country for shiiping address
    	$('.users-listing').select2();
    });
</script>
@endpush