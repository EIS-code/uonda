@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Add Chat rooms
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Add Chat Room</h5>
        <form id="addChatRoomForm" class="col-md-10 mx-auto" method="post" action="{{ route('chats.store') }}" enctype="multipart/form-data">
        @csrf
            <div class="form-group">
                <label for="title">Title</label>
                <div>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" placeholder="Title" value="{{ old('title') }}" />
                    @error('title')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
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
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="sub_title">Is Group</label>
                <div>
                    <input type="checkbox" name="is_group" checked data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
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