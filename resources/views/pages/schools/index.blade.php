@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-apartment icon-gradient bg-sunny-morning"></i>
            </div>
            <div>Schools
            </div>
        </div>
        
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <a href="{{ route('schools.create') }}">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add Schools
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
                <th>Name</th>
                <th>Country</th>
                <th>State</th>
                <th>City</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($schools as $key => $school)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ ucfirst($school->name) }}</td>
                        <td>{{ ucfirst($school->country->name) }}</td>
                        <td>{{ !empty($school->state->name) ? ucfirst($school->state->name) : "" }}</td>
                        <td>{{ ucfirst($school->city->name) }}</td>
                        <td class="icons_list">
                            <a href="{{ route('schools.edit', $school->encrypted_school_id) }}" title="Edit School"><span class="material-icons">edit</span></a> 
                            <a href="javascript:void(0)" class="remove-button" data-id="{{ $school->id }}" title="Delete School"><span class="material-icons delete-button">delete</span></a>
                            <a href="{{ route('schools.show', $school->encrypted_school_id)}}" title="Show School Details"><span class="material-icons">visibility</span></a>
                            <form id="remove-form-{{ $school->id }}" action="{{ route('schools.destroy', $school->encrypted_school_id) }}" method="POST" class="d-none">
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