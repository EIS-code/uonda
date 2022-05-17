@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="lnr-user icon-gradient bg-ripe-malin"></i>
            </div>
            <div>API response texts
            </div>
        </div>
    </div>
</div>   
<div class="main-card mb-3 card">
    <div class="card-body">
        <table style="width: 100%;" id="editable" class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>No</th>
                <th>English Text</th>
                <th>Show Text</th>
            </tr>
            </thead>
            <tbody>
                @foreach($apiResponseTexts as $key => $text)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td style="display:none;">{{ $text->id }}</td>
                        <td>{{ ucfirst($text->english_text) }}</td>                        
                        <td>
                            <a href="" class="update" data-name="show_text" data-type="text" data-id="{{ $text->id }}">{{ ucfirst($text->show_text) }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('custom-scripts')
<script type="text/javascript" src="{{ asset('js/tableedit.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
   
        $.ajaxSetup({
          headers:{
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $('#editable').Tabledit({
          url:'{{ route("update.apiResponse.text") }}',
          dataType:"json",
          columns:{
            identifier:[1, 'id'],
            editable:[[3, 'show_text']]
          },
          buttons: {
            edit: {
                    class: 'btn-info',
                    html: 'Edit',
                    action: 'edit'
            },
            delete: {
                    class: 'd-none'
            }
            },
          restoreButton:false,
          onSuccess:function(data, textStatus, jqXHR)
          {
          }
        });

    });
</script>
@endpush