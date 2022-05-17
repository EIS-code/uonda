@extends('layouts.app')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="lnr-user icon-gradient bg-ripe-malin"></i>
                </div>
                <div>
                    {{ __('Email Templates') }}
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
                        <th>{{ __('No') }}</th>
                        <th>{{ __('Email Subject') }}</th>
                        <th>{{ __('Email Body') }}</th>
                        <th>{{ __('Created On') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @if (!empty($emailTemplates) && !$emailTemplates->isEmpty())
                        @foreach($emailTemplates as $key => $emailTemplate)
                            <tr>
                                <td>{{ $key  + 1}}</td>
                                <td>{!! $emailTemplate->email_subject !!}</td>
                                <td>{!! $emailTemplate->email_body !!}</td>
                                <td>{{ Carbon\Carbon::parse($emailTemplate->created_at)->format('jS M Y') }}</td>
                                <td class="icons_list">
                                    <a href="{{ route('settings.email.templates.edit', $emailTemplate->encrypted_email_id) }}" title="Edit Email Template"><span class="material-icons">edit</span></a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">
                                <mark>{{ __('No records found!') }}</mark>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
