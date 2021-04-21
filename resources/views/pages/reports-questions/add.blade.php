@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-picture text-danger"></i>
            </div>
            <div>Add Question
            </div>
        </div>
    </div>
</div>
<div class="main-card mb-3 card">
    <div class="card-body">
        <h5 class="card-title">Add Question</h5>
        <form id="addQuestionForm" class="col-md-10 mx-auto" method="post" action="{{ route('reports-questions.store') }}" enctype="multipart/form-data">
        @csrf
            <div class="form-group">
                <label for="question">Question</label>
                <div>
                    <input type="text" class="form-control @error('question') is-invalid @enderror" id="question" name="question" placeholder="Question" value="{{ old('question') }}" />
                    @error('question')
                        <em class="error invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </em>
                    @enderror
                </div>
            </div>
            <div class="position-relative form-group">
                <label for="exampleSelect" class="">Type</label>
                <select name="question_type" id="question_type" class="form-control @error('question_type') is-invalid @enderror option_type">
                    @foreach($options as $key => $type)
                        <option value="{{ $key }}" {{ old('key') == $key ? "Selected='selected'" : ''}}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('type')
                    <em class="error invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </em>
                @enderror
            </div>
            <div class="form-group select-options">
                <label for="sub_title">Options</label>
                <div>
                    <input type="text" class="form-control @error('options') is-invalid @enderror" id="options" name="options" placeholder="Comma seperated options for radio, checkbox and dropdown" value="{{ old('options') }}"/>
                    @error('options')
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
    
    // $(document).ready(function() {
    //     var myarray = [0, 1, 2, 5];
    //     $('.option_type').on('change', function() {
    //         if(jQuery.inArray(parseInt($(this).val()), myarray) !== -1) {
    //             $('.select-options').removeClass('d-none');
    //         } else {
    //             $('.select-options').addClass('d-none');
    //         }
            
    //         alert($(this).val());
    //     })
	// });
</script>
@endpush