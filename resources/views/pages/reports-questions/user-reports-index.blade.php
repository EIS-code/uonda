@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-database icon-gradient bg-night-fade"></i>
            </div>
            <div>User Reports 
            </div>
        </div>
    </div>
</div> 
@foreach (['danger', 'warning', 'success', 'info'] as $msg)
    @if(Session::has('alert-' . $msg))
        <div class="alert alert-{{ $msg }} alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {!! Session::get('alert-' . $msg) !!}
        </div>
    @endif
@endforeach    
<div class="main-card mb-3 card">
    <div class="card-body">
        <table style="width: 100%;" id="example" class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>No</th>
                <th>Question</th>
                <th>Answer</th>
                <th>Answered By</th>
                <th>Created On</th>
            </tr>
            </thead>
            <tbody>
                @foreach($reports as $key => $report)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td><a href="{{ route('reports-questions.show', $report->ReportQuestions->encrypted_question_id) }}">{{ ucfirst($report->ReportQuestions->question) }}</a></td>
                        <td>{{ $report->answer }}</td>
                        <td><a href="{{ route('users.show', $report->User->encrypted_user_id) }}"> {{ $report->User->name }}</a></td>
                        <td>{{ Carbon\Carbon::parse($report->created_at)->format('jS M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('custom-scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.remove-button').on('click', function() {
    		var delete_id = $(this).attr('data-id');
    		if(confirm('Are you sure you want to delete this?')) {
                $('#remove-form-'+delete_id).submit();
            }
    	});
    });
</script>
@endpush