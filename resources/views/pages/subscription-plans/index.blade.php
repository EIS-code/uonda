@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-magic-wand icon-gradient bg-mixed-hopes"></i>
            </div>
            <div>Subscription Plans
            </div>
        </div>
        
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <a href="{{ route('subscription_plan.create') }}">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add Subscription Plan
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
                <th>name</th>
                <th>price</th>
                <th>Created On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($plans as $key => $plan)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ ucfirst($plan->name) }}</td>
                        <td>{{ $plan->price }}</td>
                        <td>{{ Carbon\Carbon::parse($plan->created_at)->format('jS M Y') }}</td>
                        <td class="icons_list">
                            <a href="{{ route('subscription_plan.edit', $plan->encrypted_plan_id) }}" title="Edit Subscription Plan"><span class="material-icons">edit</span></a> 
                            <a href="javascript:void(0)" class="remove-button" data-id="{{ $plan->id }}" title="Delete Subscription Plan"><span class="material-icons delete-button">delete</span></a>
                            <a href="{{ route('subscription_plan.show', $plan->encrypted_plan_id)}}" title="Show Plan Details"><span class="material-icons">visibility</span></a>
                            <form id="remove-form-{{ $plan->id }}" action="{{ route('subscription_plan.destroy', $plan->encrypted_plan_id) }}" method="POST" class="d-none">
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