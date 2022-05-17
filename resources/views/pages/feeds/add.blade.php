@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Add Feeds
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Add Feeds</h5>
        <form id="addFeedsForm" class="col-md-10 mx-auto" method="post" enctype="multipart/form-data">
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
                <label for="sub_title">Sub Title</label>
                <div>
                    <input type="text" class="form-control @error('sub_title') is-invalid @enderror" id="sub_title" name="sub_title" placeholder="Sub Title" value="{{ old('sub_title') }}"/>
                    @error('sub_title')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <div>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Description" value="{{ old('description') }}">{{ old('description') }}</textarea>
                    @error('description')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="description">Short Description</label>
                <div>
                    <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" placeholder="Short Description" value="{{ old('short_description') }}">{{ old('short_description') }}</textarea>

                    @error('short_description')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="position-relative form-group">
                <label for="exampleSelect" class="">Type</label>
                <select name="type" id="type" class="form-control @error('type') is-invalid @enderror">
                    <option value="0">Please select type</option>
                    @foreach(Config::get('globalConstant.types') as $key => $type)
                        <option value="{{ $key }}" {{ old('type') == $key ? "Selected='selected'" : ''}}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('type')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                @enderror
            </div>
            <div class="form-group">
                <label for="description">Attachment</label>
                <div>
                        <input type="file" id="file-input" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept="image/*, video/*" />
                    @error('attachment')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div><br>
                <div class="result-div" style="display:none">
                    <div class="result">
                    </div><br>
                    <div>
                        <button type="button" data-ratio="1.333333" class="btn btn-success ratio" value="Save">4:3</button>
                        <button type="button" data-ratio="1.777777" class="btn btn-success ratio" value="Save">16:9</button>
                        <button type="button" data-ratio="1.5" class="btn btn-success ratio" value="Save">3:2</button>
                        <button type="button" data-ratio="NaN" class="btn btn-success ratio" value="Save">Free</button>
                    </div>
                </div><br>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary submit-btn" name="save" value="Save">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('custom-scripts')
<script type="text/javascript">
    
    $(document).ready(function() {
        CKEDITOR.replace( 'description', {contentsCss: "body {font-size: 20px;}"}  );

        CKEDITOR.replace( 'short_description', {contentsCss: "body {font-size: 20px;}"}  );
        
        const image = document.getElementById('image');
        var cropper = '';
        var result = document.querySelector(".result");
        var upload = document.querySelector("#file-input");
        var options = {
            aspectRatio: 16/9, // (1.7777)
            viewMode: 0,
            crop(event) {
            },
        };
        // on change show image with crop options
        upload.addEventListener("change", function(e) {
            if ($('#type').val() == 1 && e.target.files.length) {
                // start file reader
                var reader = new FileReader();
                reader.onload = function(e) {
                if (e.target.result) {
                    // $(".modal").modal("show");
                    // create new image
                    var img = document.createElement("img");
                    img.id = "image";
                    img.src = e.target.result;
                    img.style = "display: block;max-width: 100%;"
                    // clean result before
                    result.innerHTML = "";
                    $('.result-div').show();
                    // append new image
                    result.appendChild(img);
                    // show save btn and options
                    // save.classList.remove("hide");
                    // options.classList.remove("hide");
                    // init cropper
                    cropper = new Cropper(img, options);
                }
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        $(document).on('click', '.ratio', function () {
            options.aspectRatio = Number($(this).attr('data-ratio'));
            console.log(options);
            var image = document.getElementById('image');
            cropper.destroy();
            cropper = new Cropper(image, options);;
        });

        $('#addFeedsForm').submit(function(e) {
            e.preventDefault();
            var filename = $('input[type=file]').val().replace(/C:\\fakepath\\/i, '')
            var ckValue = CKEDITOR.instances["description"].getData();
            $('#description').val(ckValue);

            var shortDescriptionValue = CKEDITOR.instances["short_description"].getData();
            $('#short_description').val(shortDescriptionValue);

            // var formData = new FormData(document.querySelector('form'))
            if($('#type').val() == 1) {
                cropper.getCroppedCanvas().toBlob((blob) => {
                    var formData = new FormData($(this)[0]);
                    formData.append('attachment', blob, filename);
                    $.ajax({
                        type:'POST',
                        url: "{{ route('feeds.store') }}",
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        data: formData,
                        async:false,
                        contentType: false,
                        processData: false,
                        success: (data) => {
                            // this.reset();
                            window.location.href = "{{ route('feeds.index') }}";
    ;                   },
                        error: function(data) {
                            console.log(data);
                            if(data.responseJSON.code == 500) {
                                alert(data.responseJSON.msg);
                            }
                        }
                    });
                });
            } else {
                var formData = new FormData($(this)[0]);
                $.ajax({
                    type:'POST',
                    url: "{{ route('feeds.store') }}",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: formData,
                    async:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        // this.reset();
                        window.location.href = "{{ route('feeds.index') }}";
;                   },
                    error: function(data) {
                        console.log(data);
                        if(data.responseJSON.code == 500) {
                            alert(data.responseJSON.msg);
                        }
                    }
                });
            }
        });
	});
</script>
@endpush
