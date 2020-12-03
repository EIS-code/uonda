@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>Groups
            </div>
        </div>
        
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <a href="{{ route('groups.create') }}">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add Groups
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
                <th>Title</th>
                <th>Created On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($feeds as $key => $feed)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ ucfirst($feed->title) }}</td>
                        <td>{{ Carbon\Carbon::parse($feed->created_at)->format('jS M Y') }}</td>
                        <td class="icons_list">
                            <a href="{{ route('groups.edit', $feed->encrypted_feed_id) }}" title="Edit Group"><i class="faicons mdi mdi-lead-pencil"></i></a> 
                            <a data-type="user" data-id="" class="remove-button" title="Delete Group"><i class="faicons mdi mdi-delete delete-button"></i></a>
                            <a href="{{ route('groups.show', $feed->encrypted_feed_id)}}" title="Show Group Details"><i class="faicons mdi mdi-eye"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection