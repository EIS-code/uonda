@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit Subscription Plan
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Edit Subscription Plan</h5>
        <form id="editSubscriptionPlanForm" class="col-md-10 mx-auto" method="POST" action="{{ route('subscription_plan.update', $plan->encrypted_plan_id) }}">
        @csrf
        {{ method_field('PUT') }}
            <div class="form-group">
                <label for="title">Name</label>
                <div>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Name" value="{{ $plan->name }}" />
                    @error('name')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="sub_title">Price</label>
                <div class="col-sm-12 row">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fa fa-calendar-alt"></i>
                            </div>
                        </div>
                        <input name="price" value="{{ $plan->price }}" class="form-control input-mask-trigger @error('price') is-invalid @enderror"
                            data-inputmask="'alias': 'numeric', 'groupSeparator': ',', 'autoGroup': true, 'digits': 2, 'digitsOptional': false, 'prefix': '$ ', 'placeholder': '0'"
                            im-insert="true" style="text-align: right;">
                            @error('price')
                                <em class="error invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </em>
                            @enderror
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="save" value="Save">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection