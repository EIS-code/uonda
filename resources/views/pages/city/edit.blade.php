@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit City
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Edit City</h5>
        <form id="addCityForm" class="col-md-10 mx-auto" method="POST" action="{{ route('city.update', $city->encrypted_city_id) }}" enctype="multipart/form-data">
        @csrf
        {{ method_field('PUT') }}
            <div class="form-group">
                <label for="title">Title</label>
                <div>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Name" value="{{ $city->name }}" />
                    @error('name')
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
                        <option value="{{ $country->id }}" {{ $country->id == $city->state->country_id ? "selected='selected'" : ''}}>{{ $country->name }}</option>
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
                        <option value="{{ $state->id }}" {{ $state->id == $city->state_id ? "selected='selected'" : ''}}>{{ $state->name}}</option>
                    @endforeach
                </select>
                @error('state_id')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                @enderror
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
            } else {
                $('#state_id').html('<option value="">Select country first</option>');
            }
            
        });
    });
</script>
@endpush