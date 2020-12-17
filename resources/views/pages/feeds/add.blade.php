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
        <form id="addFeedsForm" class="col-md-10 mx-auto" method="post" action="{{ route('feeds.store') }}" enctype="multipart/form-data">
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
                    <textarea type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Description" value="{{ old('description') }}">{{ old('description') }}</textarea>
                    @error('description')
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
@push('custom-scripts')
<script type="text/javascript">
    
    $(document).ready(function() {
        CKEDITOR.replace( 'description' );
	});
</script>
@endpush