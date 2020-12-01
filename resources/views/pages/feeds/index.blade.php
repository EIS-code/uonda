@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>Feeds
            </div>
        </div>
        
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <a href="{{ route('feeds.create') }}">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add Feeds
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
                <th>Sub title</th>
                <th>Created On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($feeds as $key => $feed)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ ucfirst($feed->title) }}</td>
                        <td>{{ $feed->sub_title }}</td>
                        <td>{{ Carbon\Carbon::parse($feed->created_at)->format('m d y') }}</td>
                        <td class="icons_list">
                            <a href="{{ route('feeds.edit', $feed->encrypted_feed_id) }}" title="Edit Feed"><i class="faicons mdi mdi-lead-pencil"></i></a> 
                            <a data-type="user" data-id="" class="remove-button" title="Delete User"><i class="faicons mdi mdi-delete delete-button"></i></a>
                            <a href="{{ route('feeds.show', $feed->encrypted_feed_id)}}" title="Show User Details"><i class="faicons mdi mdi-eye"></i></a>
                            
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="remove-item-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                    unknown printer took a galley of type and scrambled.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
@endsection