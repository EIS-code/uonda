@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit School
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Edit School</h5>
        <form id="addSchoolForm" class="col-md-10 mx-auto" method="POST" action="{{ route('schools.update', $school->encrypted_school_id) }}" enctype="multipart/form-data">
        @csrf
        {{ method_field('PUT') }}
            <div class="form-group">
                <label for="title">Title</label>
                <div>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Name" value="{{ $school->name }}" />
                    @error('name')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <div>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Description" value="{{ $school->description }}">{{ $school->description }}</textarea>
                    @error('description')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="position-relative form-group">
                <label for="exampleSelect" class="">Country</label>
                <select name="country_id" id="country_id" class="form-control @error('country_id') is-invalid @enderror">
                    <option value="">Please select Country</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ $country->id == $school->country_id ? "selected='selected'" : ''}}>{{ $country->name}}</option>
                    @endforeach
                </select>
                @error('country_id')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                @enderror
            </div>
            <div class="position-relative form-group">
                <label for="exampleSelect" class="">State</label>
                <select name="state_id" id="state_id" class="form-control @error('state_id') is-invalid @enderror">
                    <option value="">Please select state</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}" {{ $state->id == $school->state_id ? "selected='selected'" : ''}}>{{ $state->name}}</option>
                    @endforeach
                </select>
                @error('state_id')
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
                        <option value="{{ $city->id }}" {{ $city->id == $school->city_id ? "selected='selected'" : ''}}>{{ $city->name}}</option>
                    @endforeach
                </select>
                @error('city_id')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                @enderror
            </div>
            <div class="form-group">
                <label for="sub_title">Status</label>
                <div>
                    <input type="checkbox" name="is_active" {{ $school->is_active == 1 ? 'checked' : ''}} data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
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
    	$('select[name=country_id]').change(function() {
            var countryID = $(this).val();
            if(countryID) {
                var url = '{{ url('get-states') }}' + '/' + $(this).val();
                $.get(url, function(data) {
                    var select = $('form select[name=state_id]');
                    select.empty();
                    select.append('<option value="">Please select State</option>')
                    $.each(data.data,function(key, value) {
                        select.append('<option value=' + value.id + '>' + value.name + '</option>');
                    });
                });

                $('select[name=state_id]').change();
            } else {
                $('#state_id').html('<option value="">Select country first</option>');
                $('#city_id').html('<option value="">Select state first</option>'); 
            }
            
        });

        $('select[name=state_id]').change(function() {
            var stateId = $(this).val();

            if (stateId.length > 0) {
                var url = '{{ url('get-cities') }}' + '/' + $(this).val();
                $.get(url, function(data) {
                    var select = $('form select[name=city_id]');
                    select.empty();
                    select.append('<option value="">Please select City</option>')
                    $.each(data.data,function(key, value) {
                        select.append('<option value=' + value.id + '>' + value.name + '</option>');
                    });
                });
            } else {
                // $('#city_id').html('<option value="">Select state first</option>');

                var countryId = $('select[name=country_id]').val(),
                    url       = '{{ url('get-cities-of-country') }}' + '/' + countryId;

                $.get(url, function(data) {
                    var select = $('form select[name=city_id]');
                    select.empty();
                    select.append('<option value="">Please select City</option>')
                    $.each(data.data,function(key, value) {
                        select.append('<option value=' + value.id + '>' + value.name + '</option>');
                    });
                });
            }
            
        });
    });
</script>
@endpush