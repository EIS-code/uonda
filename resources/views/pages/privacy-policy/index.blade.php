@extends('layouts.app')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="lnr-database icon-gradient bg-night-fade"></i>
                </div>
                <div>
                    {{ __('Privacy Policy') }}
                </div>
            </div>
        </div>
    </div>

    <div class="main-card mb-3 card">
        <div class="card-body">
            {!! defined('APP_PRIVACY_POLICY') ? APP_PRIVACY_POLICY : '' !!}
        </div>
    </div>
@endsection
