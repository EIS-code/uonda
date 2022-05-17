@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Add Setting
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Add Setting</h5>
        <form id="addSettingsForm" class="col-md-10 mx-auto" method="post" action="{{ route('settings.store') }}" enctype="multipart/form-data">
        @csrf
            <div class="form-group">
                <label for="title">Key</label>
                <div>
                    <input type="text" class="form-control @error('key') is-invalid @enderror" id="key" name="key" placeholder="Title" value="{{ old('key') }}" />
                    @error('key')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="form-group">
                <label for="title">Value</label>
                <div>
                    <input type="text" class="form-control @error('value') is-invalid @enderror" id="value" name="value" placeholder="Title" value="{{ old('value') }}" />
                    @error('value')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <!-- <div class="form-group">
                <label for="sub_title">Is Removed</label>
                <div>
                    <input type="checkbox" name="status" data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                </div>
            </div> -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary" name="save" value="Save">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('custom-scripts')
<script type="text/javascript">
</script>
@endpush