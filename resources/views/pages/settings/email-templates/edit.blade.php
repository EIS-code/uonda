@extends('layouts.app')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon">
                    <i class="lnr-picture text-danger"></i>
                </div>
                <div>
                    {{ __('Edit Email Template') }}
                </div>
            </div>
        </div>
    </div>
    <div class="main-card mb-3 card">
        <div class="card-body">
            <h5 class="card-title">{{ __('Edit Email Template') }}</h5>

            <form id="editEmailTemplateForm" class="col-md-10 mx-auto" method="POST" enctype="multipart/form-data">
                @if (!empty($fields))
                    <table style="width: 100%;" id="example" class="table table-hover table-striped table-bordered">
                        <tbody>
                            @foreach ($fields as $key => $field)
                                <tr>
                                    <td width="10">
                                        <mark>&#123;&#123;{{ $key }}&#125;&#125;</mark>
                                    </td>
                                    <td>
                                        {{ __(' Used ') }} {{ $field }}.
                                    </td>
                                    <br />
                                </tr>
                            @endforeach
                            @if ($emailTemplate->id == $emailTemplate::RESET_EMAIL_ID)
                                <tr>
                                    <td width="10">
                                        <mark>&#123;&#123;{{ __('reset_link_url') }}&#125;&#125;</mark>
                                    </td>
                                    <td>
                                        {{ __(' Used for ') }} reset link button.
                                    </td>
                                    <br />
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <br /><br />
                @endif

                @csrf

                {{ method_field('PUT') }}

                <div class="form-group">
                    <label for="title">{{ __('Email Subject') }}</label>
                    <div>
                        <input type="text" class="form-control @error('email_subject') is-invalid @enderror" id="email_subject" name="email_subject" placeholder="{{ __('Email Subject') }}" value="{{ $emailTemplate->email_subject }}" />

                        @error('email_subject')
                            <em class="error invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </em>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="email_body">{{ __('Email Body') }}</label>
                    <div>
                        <textarea type="text" class="form-control @error('email_body') is-invalid @enderror" id="email_body" name="email_body" placeholder="{{ __('Email Body') }}" value="{{ old('email_body') }}">{{ old('email_body', $emailTemplate->email_body) }}</textarea>

                        @error('email_body')
                            <em class="error invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </em>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary submit-btn" name="save" value="Save">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('custom-scripts')
    <script type="text/javascript">
        
        $(document).ready(function() {
            CKEDITOR.replace('email_body');
            CKEDITOR.config.allowedContent = true;


            $('#editEmailTemplateForm').submit(function(e) {
                e.preventDefault();

                let emailBodyValue = CKEDITOR.instances["email_body"].getData();

                $('#email_body').val(emailBodyValue);

                let formData = new FormData($(this)[0]);

                formData.append('_method', 'PUT');

                $.ajax({
                    type:'POST',
                    url: "{{ route('settings.email.templates.update', $emailTemplate->encrypted_email_id) }}",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: formData,
                    async:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        data = JSON.parse(data);

                        if (!data.success) {
                            alert(data.message);
                        } else {
                            window.location.href = "{{ route('settings.email.templates.get') }}";
                        }
                    },
                    error: function(data) {
                        if (data.responseJSON.code == 500) {
                            alert(data.responseJSON.msg);
                        }
                    }
                });
            });
        });
    </script>
@endpush
