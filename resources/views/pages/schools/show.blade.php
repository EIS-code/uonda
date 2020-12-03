@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View School
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
                        <th> School Name </th>
                        <td> {{ ucfirst($feed->name) }} </td>
                    </tr>
                    
                    <tr>
                        <th> City </th>
                        <td> {{ ucfirst($feed->city->name) }} </td>
                    </tr>

                    <tr>
                        <th> Country </th>
                        <td> {{ ucfirst($feed->country->name) }} </td>
                    </tr>
                    
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection