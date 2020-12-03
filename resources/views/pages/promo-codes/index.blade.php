@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>Promo Codes
            </div>
        </div>
        
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <a href="{{ route('promo-codes.create') }}">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add Promo Code
                </button>
                </a>
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
                <th>Promo Code</th>
                <th>Amount</th>
                <th>Percentage</th>
                <th>Status</th>
                <th>Created On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($promo_codes as $key => $code)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ ucfirst($code->promo_code) }}</td>
                        <td>{{ !empty($code->amount) ? $code->amount : '-' }}</td>
                        <td>{{ !empty($code->percentage) ? $code->percentage : '-' }}</td>
                        <td>{{ $code->status == 1 ? 'Enable' : 'Disable' }}</td>
                        <td>{{ Carbon\Carbon::parse($code->created_at)->format('jS M Y') }}</td>
                        <td class="icons_list">
                            <a href="{{ route('promo-codes.edit', $code->encrypted_code_id) }}" title="Edit Promo Code"><i class="faicons mdi mdi-lead-pencil"></i></a> 
                            <a data-type="user" data-id="" class="remove-button" title="Delete Promo Code"><i class="faicons mdi mdi-delete delete-button"></i></a>
                            <a href="{{ route('promo-codes.show', $code->encrypted_code_id)}}" title="Show Promo Code Details"><i class="faicons mdi mdi-eye"></i></a>
                            
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection