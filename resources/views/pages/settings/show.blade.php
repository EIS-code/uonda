@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View Group
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
                        <td> {{ ucfirst($feed->title) }} </td>
                    </tr>
                    
                    <tr>
                        <th> Description </th>
                        <td> {!! html_entity_decode($feed->description) !!} </td>
                    </tr>
                    
                </tbody>
            </table>
    <h4 style="text-align:center">Joined User</h4>
            <table class="table table-striped course-tables show-details-table">
                <tbody>
                @foreach($feed->users as $key => $feed1)
                    <tr>
                        <td> {{ ucfirst($feed1->user->name) }} </td>
                        <td> {{ $feed1->user->email }} </td>
                    </tr>
                @endforeach
                @if(count($feed->users) == 0)
                <tr>
                    <th colspan=2>No user found</th>
                    </tr>
                @endif
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection