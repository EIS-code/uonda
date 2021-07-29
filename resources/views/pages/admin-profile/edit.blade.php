@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit Profile
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
        <h5 class="card-title">Edit Profile</h5>
        <form id="addProfileForm" class="col-md-10 mx-auto" method="POST" action="{{ route('profile-update') }}" enctype="multipart/form-data">
        @csrf
            <div class="form-group">
                <label for="title">Name</label>
                <div>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Name" value="{{ $user->name }}" />
                    @error('name')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="title">Email</label>
                <div>
                    <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Email" value="{{ $user->email }}" autocomplete="off" />
                    @error('email')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="title">Designation</label>
                <div>
                    <input type="text" class="form-control @error('job_position') is-invalid @enderror" id="job_position" name="job_position" placeholder="Designation" value="{{ $user->job_position }}" />
                    @error('job_position')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="title">Password</label>
                <div>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password" value="" autocomplete="off" />
                    @error('password')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <div>
                    <input type="password" class="form-control @error('confirm-password') is-invalid @enderror" id="confirm-password" name="password_confirmation" placeholder="{{ __('Confirm Password') }}" value="" autocomplete="off" />
                    @error('confirm-password')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            @if(!empty($user->new_profile))
                <div class="">
                    <img width="250" src="{{ URL::asset('storage/admin-profile/'. $user->new_profile) }}">
                </div>
            @endif
            <div class="form-group">
                <label for="attachment">Profile Picture</label>
                <div>
                    <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept="image/*, video/*" />
                    @error('attachment')
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