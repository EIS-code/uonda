@extends('layouts.app')

@section('content')
<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon">
                <i class="pe-7s-medal icon-gradient bg-tempting-azure"></i>
            </div>
            <div>Users
            </div>
        </div>
        <div class="page-title-actions">
            <div class="d-inline-block dropdown">
                <button type="button" class="btn-shadow btn btn-info">
                    <span class="btn-icon-wrapper pr-2 opacity-7">
                        <i class="fa fa-business-time fa-w-20"></i>
                    </span>
                    Add User
                </button>
            </div>
        </div>
    </div>
</div>            
<div class="main-card mb-3 card">
    <div class="card-body">
        <table style="width: 100%;" id="example" class="table table-hover table-striped table-bordered">
            <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Status</th>
                <th>Registered On</th>
                <th>User Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
                @foreach($users as $key => $user)
                    <tr>
                        <td>{{ ucfirst($user->name) }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->gender == 'f' ? Female : ($user->gender == 'm') ? Male : '-'  }}</td>
                        <td>{{ Config::get('globalConstant.status')[$user->current_status] }}</td>
                        <td>{{ Carbon\Carbon::parse($user->created_at)->format('M d Y') }}</td>
                        <td>
                            <input type="checkbox" checked data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
                        </td>
                        <td class="icons_list">
                            <a href="" title="Edit User"><i class="faicons mdi mdi-lead-pencil"></i></a> 
                            <a data-type="user" data-id="{{ $user->encrypted_user_id }}" class="remove-button" title="Delete User"><i class="faicons mdi mdi-delete delete-button"></i></a>
                            <a href="" title="Show User Details"><i class="faicons mdi mdi-eye"></i></a>
                            
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Office</th>
                <th>Age</th>
                <th>Start date</th>
                <th>Salary</th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<div class="modal fade" id="remove-item-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                    unknown printer took a galley of type and scrambled.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>
@endsection