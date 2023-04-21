@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Add City
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Add City</h5>
        <form id="addCityForm" class="col-md-10 mx-auto" method="post" action="{{ route('city.store') }}" >
        @csrf
            <div class="form-group">
                <label for="title">Name</label>
                <div>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Name" value="{{ old('name') }}" />
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
                        <option value="{{ $country->id }}">{{ $country->name}}</option>
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
                </select>
                @error('state_id')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                @enderror
            </div>

            <div class="position-relative form-group">
                <label for="latitude">Latitude</label>
                <div>
                    <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" placeholder="Latitude" value="{{ old('latitude')}}" />
                    @error('latitude')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                    @enderror
                </div>
            </div>

            <div class="position-relative form-group">
                <label for="longitude">Longitude</label>
                <div>
                    <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" placeholder="Longitude" value="{{ old('longitude') }}" />
                    @error('longitude')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                    @enderror
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
            } else {
                $('#state_id').html('<option value="">Select country first</option>');
            }
            
        });
    });
</script>
@endpush