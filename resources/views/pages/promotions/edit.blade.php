@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit Promotions
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Edit Promotions</h5>
        <form id="editPromotionForm" class="col-md-10 mx-auto" method="post" action="{{ route('promotions.update', $promotion->encrypted_promotion_id) }}" enctype="multipart/form-data">
        @csrf
        {{ method_field('PUT') }}
            <div class="form-group">
                <label for="title">Title</label>
                <div>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" placeholder="Title" value="{{ $promotion->title }}" />
                    @error('title')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <div>
                    <textarea type="text" class="form-control @error('body') is-invalid @enderror" id="body" name="body" placeholder="Body" value="{{ old('body') }}">{{ $promotion->body }}</textarea>
                    @error('body')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="voucher_code">Voucher Code</label>
                <div>
                    <input type="text" class="form-control @error('voucher_code') is-invalid @enderror" id="voucher_code" name="voucher_code" placeholder="Voucher Code" value="{{ $promotion->voucher_code }}"/>
                    @error('voucher_code')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <div>
                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" placeholder="Expiry Date" value="{{ $promotion->formatted_expiry_date }}" />
                    @error('expiry_date')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="">
                    <img width="250" src="{{ $promotion->photo }}">
                </div>
            <div class="form-group">
                <label for="body">Photo</label>
                <div>
                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*" />
                    @error('photo')
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
        CKEDITOR.replace( 'body' );
        expiry_date.min = new Date().toISOString().split("T")[0];
    });
</script>
@endpush