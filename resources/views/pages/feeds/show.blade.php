@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>View Feed
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
                        <th> Title </th>
                        <td> {{ ucfirst($feed->title) }} </td>
                    </tr>
                    <tr>
                        <th> Sub Title </th>
                        <td> {{ $feed->sub_title }} </td>
                    </tr>
                    <tr>
                        <th> Description </th>
                        <td> {!! html_entity_decode($feed->description) !!} </td>
                    </tr>
                    @if(!empty($feed->attachment))
                    <tr>
                        <th> attachment Type </th>
                        <td> {{ $feed->type != 0 ? Config::get('globalConstant.types')[$feed->type] : '-' }} </td>
                    </tr>
                    <tr>
                        <th> Cover Image </th>
                        <td> 
                            <iframe frameborder="0" width="350" height="300"
                            src="{{ URL::asset('storage/feed/'. explode('/', $feed->attachment)[4]) }}" name="imgbox" id="imgbox">
                            <p>iframes are not supported by your browser.</p>
                            </iframe>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
            <h4 style="text-align:center">Liked By</h4>
            <table class="table table-striped course-tables show-details-table">
                <tbody>
                    @foreach($feed->likedByUser as $user)
                        <tr>
                            <td> <a href="{{ route('users.show', $user->encrypted_user_id) }}" target="_blank">{{$user->name}}</a> </td>
                        </tr>
                    @endforeach
                    @if(count($feed->likedByUser) == 0)
                        <tr>
                            <th colspan=2>No Likes by any user</th>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection