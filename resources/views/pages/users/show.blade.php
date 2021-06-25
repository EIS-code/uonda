@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View User
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
        <div class="table-responsive">
            <table class="table table-striped course-tables show-details-table">
                
                <tbody>
                    <tr>
                        <th> Name</th>
                        <td> {{ ucfirst($user->name) }} </td>
                    </tr>
                    <tr>
                        <th> User Name </th>
                        <td> {{ !empty($user->user_name) ? $user->user_name : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Email </th>
                        <td> {{ $user->email }} </td>
                    </tr>
                    <tr>
                        <th> Referral Code </th>
                        <td> {{ $user->referral_code }} </td>
                    </tr>
                    <tr>
                        <th> Current Location </th>
                        <td> {{ !empty($user->	current_location) ? $user->	current_location : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Nation </th>
                        <td> {{ !empty($user->nation) ? $user->nation : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Gender </th>
                        <td> {{ $user->gender == 'm' ? 'Male' : 'Female' }} </td>
                    </tr>
                    <tr>
                        <th> Birthday </th>
                        <td> {{ !empty($user->user_name) ? Carbon\Carbon::parse($user->birthday)->format('jS M Y') : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Status </th>
                        <td> {{ Config::get('globalConstant.status')[$user->current_status] }} </td>
                    </tr>
                    <tr>
                        <th> Company </th>
                        <td> {{ !empty($user->	company) ? $user->	company : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Job Position </th>
                        <td> {{ !empty($user->job_position) ? $user->job_position : '-' }} </td>
                    </tr>
                    <tr>
                        <th> University </th>
                        <td> {{ !empty($user->university) ? $user->university : '-' }} </td>
                    </tr>
                    <tr>
                        <th> School Name </th>
                        <td> {{ array_key_exists('school_name', $data) ? $data['school_name']->name : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Country Name </th>
                        <td> {{ array_key_exists('country_name', $data) ? $data['country_name']->name : '-' }} </td>
                    </tr>
                    <tr>
                        <th> City Name </th>
                        <td> {{ array_key_exists('city_name', $data) ? $data['city_name']->name : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Field Of Study </th>
                        <td> {{ !empty($user->field_of_study) ? $user->	field_of_study : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Personal Flag </th>
                        <td> {{ $user->personal_flag == 0 ? 'None' : 'Done' }} </td>
                    </tr>
                    <tr>
                        <th> School Flag </th>
                        <td> {{ $user->school_flag == 0 ? 'None' : 'Done' }} </td>
                    </tr>
                    <tr>
                        <th> Other Flag </th>
                        <td> {{ $user->other_flag == 0 ? 'None' : 'Done' }} </td>
                    </tr>
                    <tr>
                        <th> Longitude </th>
                        <td> {{ !empty($user->longitude) ? $user->longitude : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Latitude </th>
                        <td> {{ !empty($user->latitude) ? $user->latitude : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Is Enable </th>
                        <td> {{ $user->is_enable  == 1 ? 'Enabled' : 'Disabled' }} </td>
                    </tr>
                    <tr>
                        <th> Is Accepted </th>
                        <td> {{ $user->is_accepted == 0 ? 'Pending' : ($user->is_accepted == 1 ? 'Accepted' : 'Rejected') }} </td>
                    </tr>
                    <tr>
                        <th> Reason for Rejection </th>
                        <td> {{ !empty($user->reason_for_rejection) ? $user->reason_for_rejection : '-' }} </td>
                    </tr>
                </tbody>
            </table>
            <h4 style="text-align:center">Referral Users</h4>
            <table class="table table-striped course-tables show-details-table">
                <tbody>
                    @foreach($user->referralUsers as $ruser)
                        <tr>
                            <td>{{ $ruser->user->name }}</td>
                            <td>{{ $ruser->user->email }}</td>
                        </tr>
                    @endforeach
                    @if(count($user->referralUsers) == 0)
                        <tr>
                            <th colspan=2>No referral users found</th>
                        </tr>
                    @endif
                </tbody>
            </table>
            <h4 style="text-align:center">Users Documents</h4>
            <table class="table table-striped course-tables show-details-table">
                <tbody>
                    @foreach($user->userDocuments as $document)
                        <tr>
                            @php
                                $document_path_arr = explode('/', $document->document);
                                $document_name = $document_path_arr[count($document_path_arr) - 1];
                            @endphp
                            <td> 
                                <iframe frameborder="0" width="350" height="300" src="{{ $document->document }}" name="imgbox" id="imgbox">
                                    <p>iframes are not supported by your browser.</p>
                                </iframe>
                                <p>{{ $document_name }}</p>
                            </td>
                            <td> <a href="{{ $document->document }}" download>Download Now</a> </td>
                        </tr>
                    @endforeach
                    @if(count($user->userDocuments) == 0)
                        <tr>
                            <th colspan=2>No documents available for this user</th>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection