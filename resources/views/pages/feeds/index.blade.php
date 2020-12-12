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
                        <td>{{ Carbon\Carbon::parse($feed->created_at)->format('jS M Y') }}</td>
                        <td class="icons_list">
                            <a href="{{ route('feeds.edit', $feed->encrypted_feed_id) }}" title="Edit Feed"><i class="faicons mdi mdi-lead-pencil"></i></a> 
                            <a href="javascript:void(0);" class="remove-button" data-id="{{ $feed->id }}" title="Delete Feed"><i class="faicons mdi mdi-delete delete-button"></i></a>
                            <a href="{{ route('feeds.show', $feed->encrypted_feed_id)}}" title="Show Feed Details"><i class="faicons mdi mdi-eye"></i></a>
                            <form id="remove-form-{{ $feed->id }}" action="{{ route('feeds.destroy', $feed->encrypted_feed_id) }}" method="POST" class="d-none">
                            @csrf
                            {{ method_field('DELETE') }}
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('custom-scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.remove-button').on('click', function() {
    		var delete_id = $(this).attr('data-id');
    		if(confirm('Are you sure you want to delete this?')) {
                $('#remove-form-'+delete_id).submit();
            }
    	});
    });
</script>
@endpush