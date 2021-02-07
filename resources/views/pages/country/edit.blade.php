@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit Country
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Edit School</h5>
        <form id="editCountryForm" class="col-md-10 mx-auto" method="POST" action="{{ route('country.update', $country->encrypted_country_id) }}" enctype="multipart/form-data">
        @csrf
        {{ method_field('PUT') }}
        <div class="form-group">
                <label for="title">Name</label>
                <div>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Name" value="{{ $country->name }}" />
                    @error('name')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="title">Sort Name</label>
                <div>
                    <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" placeholder="Sort Name" value="{{ $country->sort_name }}" />
                    @error('short_name')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="title">Phone Code</label>
                <div>
                    <input type="text" class="form-control @error('phone_code') is-invalid @enderror" id="phone_code" name="phone_code" placeholder="Phone Code" value="{{ $country->phone_code }}" />
                    @error('phone_code')
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