@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-user icon-gradient bg-ripe-malin"></i>
            </div>
            <div>Settings
            </div>
        </div>
        
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <a href="{{ route('settings.create') }}">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add Settings
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
                <th>Key</th>
                <th>Value</th>
                <th>Created On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($constants as $key => $constant)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ ucfirst($constant->key) }}</td>
                        @if ($constant->key == 'TERMS_AND_CONDITIONS' || $constant->key == 'ABOUT_US')
                            <td>{!! $constant->value !!}</td>
                        @else
                            <td>{{ ucfirst($constant->value) }}</td>
                        @endif
                        <td>{{ Carbon\Carbon::parse($constant->created_at)->format('jS M Y') }}</td>
                        <td class="icons_list">
                            <a href="{{ route('settings.edit', $constant->encrypted_constant_id) }}" title="Edit Settings"><span class="material-icons">edit</span></a> 
                            <!-- <a data-type="user" data-id="{{ $constant->id }}" class="remove-button" title="Delete Settings"><span class="material-icons delete-button">delete</span></a>
                            <form id="remove-form-{{ $constant->id }}" action="{{ route('settings.destroy', $constant->encrypted_constant_id) }}" method="POST" class="d-none">
                            @csrf
                            {{ method_field('DELETE') }}
                            </form> -->
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