@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View Promo code
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
                        <th> Promo Code </th>
                        <td> {{ ucfirst($code->promo_code) }} </td>
                    </tr>
                    <tr>
                        <th> Amount </th>
                        <td> {{ !empty($code->amount) ? $code->amount : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Percentage </th>
                        <td> {{ !empty($code->percentage) ? $code->percentage . ' %' : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Status </th>
                        <td> {{ $code->status == 1 ? 'Enable' : 'Disable' }} </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection