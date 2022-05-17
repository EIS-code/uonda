@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View Question Details 
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
        <div class="table-responsive">
            <table class="table table-striped course-tables show-details-table">
                
                <tbody>
                    <tr>
                        <th> Question </th>
                        <td> {{ ucfirst($question->question) }} </td>
                    </tr>
                    <tr>
                        <th> Question Type </th>
                        <td> {{ $question->report_question_type }} </td>
                    </tr>
                    <tr>
                        <th> Options </th>
                        <td> {{ !empty($question->options) ? $question->options : '-'}} </td>
                    </tr>
                    <tr>
                        <th> Created On </th>
                        <td> {{ Carbon\Carbon::parse($question->created_at)->format('jS M Y') }} </td>
                    </tr>
                    <tr>
                        <th> Updated On </th>
                        <td> {{ Carbon\Carbon::parse($question->updated_at)->format('jS M Y') }} </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection