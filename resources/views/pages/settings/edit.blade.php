@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Edit Settings
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Edit Settings</h5>
        <form id="editSettingForm" class="col-md-10 mx-auto" method="POST" action="{{ route('settings.update', $constant->encrypted_constant_id) }}" enctype="multipart/form-data">
        @csrf
        {{ method_field('PUT') }}
            <div class="form-group">
                <label for="title">Key</label>
                <div>
                    <input type="text" class="form-control @error('key') is-invalid @enderror" id="key" name="key" placeholder="Key" value="{{ $constant->key }}" />
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
                    @if ($constant->key == 'TERMS_AND_CONDITIONS' || $constant->key == 'ABOUT_US' || $constant->key == 'APP_PRIVACY_POLICY')
                        <textarea class="form-control @error('value') is-invalid @enderror" id="editor-value" name="value" placeholder="Value">{{ old('value', $constant->value) }}</textarea>
                    @else
                        <input type="text" class="form-control @error('value') is-invalid @enderror" id="value" name="value" placeholder="Value" value="{{ old('value', $constant->value) }}" />
                    @endif

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
        $(document).ready(function() {
            CKEDITOR.replace('editor-value');
        });
    </script>
@endpush