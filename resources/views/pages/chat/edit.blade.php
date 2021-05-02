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
            <div class="position-relative form-group">
                <label for="exampleSelect" class="">Country</label>
                <select name="country_id" id="country_id" class="form-control @error('country_id') is-invalid @enderror">
                    <option value="">Please select Country</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ $country->id == $chat_room->country_id ? "selected='selected'" : ''}}>{{ $country->name}}</option>
                    @endforeach
                </select>
                @error('country_id')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                @enderror
            </div>
            <div class="position-relative form-group">
                <label for="exampleSelect" class="">City</label>
                <select name="city_id" id="city_id" class="form-control @error('city_id') is-invalid @enderror">
                    <option value="">Please select city</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ $city->id == $chat_room->city_id ? "selected='selected'" : ''}}>{{ $city->name}}</option>
                    @endforeach
                </select>
                @error('city_id')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                @enderror
            </div>
            <div class="form-group">
                <label for="sub_title">Is Group</label>
                <div>
                    <input type="checkbox" name="is_group" {{ $chat_room->is_group == 1 ? 'checked' : ''}} data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                </div>
            </div>
            <div class="form-group">
                <label for="sub_title">Group Type</label>
                <div class="position-relative form-check">
                    <label class="form-check-label">
                        <input name="group_type" type="radio" class="form-check-input" value="0" {{ $chat_room->group_type == 0 ? 'checked' : '' }}> 
                        Public
                    </label>
                </div>
                <div class="position-relative form-check">
                    <label class="form-check-label">
                        <input name="group_type" type="radio" class="form-check-input" value="1" {{ $chat_room->group_type == 1 ? 'checked' : '' }}> 
                        Private
                    </label>
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
        $('select[name=country_id]').change(function() {
            var countryID = $(this).val();
            if(countryID) {
                var url = '{{ url('get-cities-of-country') }}' + '/' + $(this).val();
                $.get(url, function(data) {
                    var select = $('form select[name=city_id]');
                    select.empty();
                    select.append('<option value="">Please select City</option>')
                    $.each(data.data,function(key, value) {
                        select.append('<option value=' + value.id + '>' + value.name + '</option>');
                    });
                });
            } else {
                $('#city_id').html('<option value="">Select country first</option>'); 
            }
        });
    });
</script>
@endpush