@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-user icon-gradient bg-ripe-malin"></i>
            </div>
            <div>Emails
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
                <th>Subject</th>
                <th>From</th>
                <th>To</th>
                <th>Send On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($emails as $key => $email)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ ucfirst($email->subject) }}</td>
                        <td>{{ $email->from }}</td>
                        <td>{{ $email->to }}</td>
                        <td>{{ Carbon\Carbon::parse($email->created_at)->format('jS M Y') }}</td>
                        <td class="icons_list">
                            <a href="{{ route('emails.show', $email->encrypted_email_id)}}" title="Show Email Details"><span class="material-icons">visibility</span></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection