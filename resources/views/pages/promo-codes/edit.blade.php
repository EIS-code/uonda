@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit Promo Code
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Edit Promo Code</h5>
        <form id="editPromoCodeForm" class="col-md-10 mx-auto" method="POST" action="{{ route('promo-codes.update', $code->encrypted_code_id) }}" >
        @csrf
        {{ method_field('PUT') }}
            <div class="form-group">
                <label for="promo_code">Promo Code</label>
                <div>
                    <input type="text" class="form-control @error('promo_code') is-invalid @enderror" id="promo_code" name="promo_code" placeholder="Promo Code" value="{{ $code->promo_code }}" />
                    @error('promo_code')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="amount">Amount</label>
                <div>
                    <input type="text" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" placeholder="Amount" value="{{ !empty($code->amount) ? $code->amount : '' }}"/>
                    @error('amount')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="percentage">Percentage</label>
                <div>
                    <input type="text" class="form-control @error('percentage') is-invalid @enderror" id="percentage" name="percentage" placeholder="Percentage" value="{{ !empty($code->percentage) ? $code->percentage : '' }}"/>
                    @error('percentage')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="sub_title">Status</label>
                <div>
                    <input type="checkbox" name="status" {{ $code->status == 1 ? 'checked' : ''}} data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="save" value="Save">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection