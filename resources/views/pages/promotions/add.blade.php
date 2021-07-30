@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Add Promotions
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Add Promotions</h5>
        <form id="addPromotionForm" class="col-md-10 mx-auto" method="post" action="{{ route('promotions.store') }}" enctype="multipart/form-data">
        @csrf
            <div class="form-group">
                <label for="title">Title</label>
                <div>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" placeholder="Title" value="{{ old('title') }}" />
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
                    <textarea type="text" class="form-control @error('body') is-invalid @enderror" id="body" name="body" placeholder="Body" value="{{ old('body') }}">{{ old('body') }}</textarea>
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
                    <input type="text" class="form-control @error('voucher_code') is-invalid @enderror" id="voucher_code" name="voucher_code" placeholder="Voucher Code" value="{{ old('voucher_code') }}"/>
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
                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" placeholder="Expiry Date" value="{{ old('expiry_date') }}"/>
                    @error('expiry_date')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="body">Photo</label>
                <div>
                    <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*" id="file-input" />
                    <input type="hidden" name="photo-base64" id="file-input-base64" />
                    @error('photo')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <br />

            <div class="result-div" style="display:none">
                <div class="result">
                </div><br>
                <div>
                    <button type="button" data-ratio="1.333333" class="btn btn-success ratio" value="Save">4:3</button>
                    <button type="button" data-ratio="1.777777" class="btn btn-success ratio" value="Save">16:9</button>
                    <button type="button" data-ratio="1.5" class="btn btn-success ratio" value="Save">3:2</button>
                    <button type="button" data-ratio="NaN" class="btn btn-success ratio" value="Save">Free</button>
                </div>
            </div>
            <br />

            <div class="form-group">
                <button type="button" class="btn btn-primary" name="save" id="save" value="Save">Save</button>
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

        const image = document.getElementById('image');

        let cropper = '',
            result  = document.querySelector(".result"),
            upload  = document.querySelector("#file-input"),
            options = {
                aspectRatio: 16/9, // (1.7777)
                viewMode: 0,
                crop(event) {},
            };

        // On change show image with crop options.
        upload.addEventListener("change", function(e) {
            // Start file reader.
            let reader = new FileReader();

            reader.onload = function(e) {
                if (e.target.result) {
                    // Create new image.
                    var img = document.createElement("img");
                    img.id = "image";
                    img.src = e.target.result;
                    img.style = "display: block;max-width: 100%;"
                    // Clean result before
                    result.innerHTML = "";
                    $('.result-div').show();
                    // Append new image
                    result.appendChild(img);
                    // Show save btn and options
                    // Save.classList.remove("hide");
                    // Options.classList.remove("hide");
                    // Init cropper
                    cropper = new Cropper(img, options);
                }
            };

            reader.readAsDataURL(e.target.files[0]);
        });

        $(document).on('click', '.ratio', function () {
            options.aspectRatio = Number($(this).attr('data-ratio'));

            let image = document.getElementById('image');

            cropper.destroy();

            cropper = new Cropper(image, options);
        });

        $('#save').on("click", function(e) {
            e.preventDefault();

            let self = $(this);

            if (cropper) {
                let croppedPhoto = cropper.getCroppedCanvas();

                $("#file-input-base64").val(croppedPhoto.toDataURL("image/png"));
            }

            $('#addPromotionForm').submit();
        });
    });

</script>
@endpush