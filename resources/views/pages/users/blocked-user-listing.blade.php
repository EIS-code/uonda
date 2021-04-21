@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-user icon-gradient bg-ripe-malin"></i>
            </div>
            <div>Blocked Users
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
                <th>Blocked User</th>
                <th>Blocked By</th>
                <th>Is blocked</th>
                <th>Blocked On</th>
            </tr>
            </thead>
            <tbody>
                @foreach($block_profiles as $key => $profile)
                    <tr>
                        <td>{{ $key  + 1 }}</td>
                        <td><a href="{{ route('users.show', $profile->user->encrypted_user_id) }}">{{ ucfirst($profile->user->name) }}</a></td>
                        <td><a href="{{ route('users.show', $profile->blockedUser->encrypted_user_id) }}">{{ ucfirst($profile->blockedUser->name) }}</a></td>
                        <td>{{ $profile->is_block == '1' ? 'YES' : 'NO' }}</td>
                        <td>{{ Carbon\Carbon::parse($profile->created_at)->format('jS M Y') }}</td>
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