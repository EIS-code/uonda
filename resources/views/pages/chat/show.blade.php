@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View Chat Room
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
            <table class="shtable table-striped course-tables show-details-table">
                
                <tbody>
                    <tr>
                        <th> Title </th>
                        <td> {{ ucfirst($chat_room->title) }} </td>
                    </tr>
                    <tr>
                        <th> Is Group </th>
                        <td> {{ $chat_room->is_group == 1 ? 'Yes' : 'No' }} </td>
                    </tr>
                    @if(!empty($chat_room->group_icon))
                        <tr>
                            <th> Group Icon </th>
                            <td> 
                                <img width="250" src="{{ URL::asset('storage/'. $chat_room->group_icon) }}">
                            </td>
                        </tr>
                    @endif
                    @if(!empty($chat_room->group_icon_actual))
                        <tr>
                            <th> Group Icon Actual</th>
                            <td> 
                                <img width="250" src="{{ URL::asset('storage/'. $chat_room->group_icon_actual) }}">
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <h4 style="text-align:center">Joined User</h4>
            <table class="table table-striped course-tables show-details-table">
                <tbody>
                    @foreach($chat_room->ChatRoomUsers as $room_user)
                        <tr>
                            <td> <a href="{{ route('users.show', $room_user->Users->encrypted_user_id) }}" target="_blank">{{$room_user->Users->name}}</a> </td>
                        </tr>
                    @endforeach
                    @if(count($chat_room->ChatRoomUsers) == 0)
                        <tr>
                            <th colspan=2>No user in this chat room</th>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection