@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View Email Details
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
                        <th> Subject </th>
                        <td> {{ ucfirst($email->title) }} </td>
                    </tr>
                    <tr>
                        <th> From </th>
                        <td> {{ $email->from }} </td>
                    </tr>
                    <tr>
                        <th> To </th>
                        <td> {{ $email->to }} </td>
                    </tr>
                    <tr>
                        <th> CC </th>
                        <td> {{ !empty($email->cc) ? $email->cc : '-' }} </td>
                    </tr>
                    <tr>
                        <th> BCC </th>
                        <td> {{ !empty($email->bcc) ? $email->bcc : '-' }} </td>
                    </tr>
                    <tr>
                        <th> CC </th>
                        <td> {{ !empty($email->cc) ? $email->cc : '-' }} </td>
                    </tr>
                    <tr>
                        <th> body </th>
                        <td> {!! html_entity_decode($email->body) !!} </td>
                    </tr>
                    <tr>
                        <th> Attachments </th>
                        <td> {{ !empty($email->attachments) ? $email->attachments : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Is Sent  </th>
                        <td> {{ $email->is_send == '1' ? 'YES' : 'No' }} </td>
                    </tr>
                    <tr>
                        <th> Exception Info  </th>
                        <td> {{ !empty($email->exception_info) ? $email->exception_info : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Sent on</th>
                        <td> {{ Carbon\Carbon::parse($email->created_at)->format('jS M Y') }} </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection