@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View Promotion
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
                        <th> Title </th>
                        <td> {{ ucfirst($promotion->title) }} </td>
                    </tr>
                    <tr>
                        <th> Body </th>
                        <td> {!! html_entity_decode($promotion->body) !!} </td>
                    </tr>
                    <tr>
                        <th> Voucher Code </th>
                        <td> {{ $promotion->voucher_code }} </td>
                    </tr>
                    <tr>
                        <th> Expiry Date </th>
                        <td> {{ Carbon\Carbon::parse($promotion->expiry_date)->format('jS M Y') }} </td>
                    </tr>
                    @if(!empty($promotion->photo))
                    <tr>
                        <th> Photo </th>
                        <td> 
                            <img width="250" src="{{ $promotion->photo }}">
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection