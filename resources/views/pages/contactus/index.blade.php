@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-database icon-gradient bg-night-fade"></i>
            </div>
            <div>Contact Us
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
                <th>Text</th>
                <th>Attachment</th>
                <th>Created On</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($contactUs as $key => $contact)
                    <tr>
                        <td>{{ $key  + 1}}</td>
                        <td>{{ $contact->text }}</td>
                        <td>
                            @if (!empty($contact->attachment))
                                <a href="{{ $contact->attachment }}" target="_blank">
                                    Show
                                </a>
                            @else
                                No attachment!
                            @endif
                        </td>
                        <td>{{ date('jS M Y', $contact->created_at / 1000) }}</td>
                        <td class="icons_list">
                            <a href="javascript:void(0);" class="remove-button" data-id="{{ $contact->id }}" title="Delete"><span class="material-icons delete-button">delete</span></a>
                            <form id="remove-form-{{ $contact->id }}" action="{{ route('contactus.destroy', $contact->encrypted_contactus_id) }}" method="POST" class="d-none">
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
                var deleteId = $(this).attr('data-id');

                if (confirm('Are you sure you want to delete this?')) {
                    $('#remove-form-' + deleteId).submit();
                }
            });
        });
    </script>
@endpush