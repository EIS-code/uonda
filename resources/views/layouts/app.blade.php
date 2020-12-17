<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link type="text/css" href="https://cdn.materialdesignicons.com/5.4.55/css/materialdesignicons.min.css" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body>
<div class="app-container app-theme-white body-tabs-shadow fixed-header fixed-sidebar ">
    <div class="app-header header-shadow">
        <div class="app-header__logo">
            <div class="logo-src"></div>
            <div class="header__pane ml-auto">
                <div>
                    <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                    </span>
                    </button>
                </div>
            </div>
        </div>
        <div class="app-header__mobile-menu">
            <div>
                <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                <span class="hamburger-inner"></span>
                </span>
                </button>
            </div>
        </div>
        <div class="app-header__menu">
            <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
            <span class="btn-icon-wrapper">
            <i class="fa fa-ellipsis-v fa-w-6"></i>
            </span>
            </button>
            </span>
        </div>
        <div class="app-header__content">
            <div class="app-header-right">
                <div class="header-btn-lg pr-0">
                    <div class="widget-content p-0">
                        <div class="widget-content-wrapper">
                            <div class="widget-content-left">
                                <div class="btn-group">
                                    <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                    <img width="42" class="rounded-circle" src="{{ !empty(Auth::user()->profile_pic) ? URL::asset('storage/admin-profile/'. Auth::user()->profile_pic) : asset('assets/images/avatars/2.jpg') }}" alt="">
                                    <i class="fa fa-angle-down ml-2 opacity-8"></i>
                                    </a>
                                    <div tabindex="-1" role="menu" aria-hidden="true" class="rm-pointers dropdown-menu-lg dropdown-menu dropdown-menu-right">
                                        <div class="dropdown-menu-header">
                                            <div class="dropdown-menu-header-inner bg-info">
                                                <div class="menu-header-image opacity-2" style="background-image: url('{{ asset('assets/images/dropdown-header/city3.jpg') }}');"></div>
                                                <div class="menu-header-content text-left">
                                                    <div class="widget-content p-0">
                                                        <div class="widget-content-wrapper">
                                                            <div class="widget-content-left mr-3">
                                                                <img width="42" class="rounded-circle" src="{{ !empty(Auth::user()->profile_pic) ? URL::asset('storage/admin-profile/'. Auth::user()->profile_pic) : asset('assets/images/avatars/2.jpg') }}" alt="">
                                                            </div>
                                                            <div class="widget-content-left">
                                                                <div class="widget-heading">{{ ucfirst(Auth::user()->name) }}</div>
                                                                <div class="widget-subheading opacity-8">Administration Access</div>
                                                            </div>
                                                            <div class="widget-content-right mr-2">
                                                                <a class="btn-pill btn-shadow btn-shine btn btn-focus" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                                                    document.getElementById('logout-form').submit();">
                                                                        {{ __('Logout') }}
                                                                    </a>

                                                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                                                        @csrf
                                                                    </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="scroll-area-xs" style="height: 70px;">
                                            <div class="scrollbar-container ps">
                                                <ul class="nav flex-column">
                                                    <li class="nav-item-header nav-item">Activity</li>
                                                    <li class="nav-item">
                                                        <a href="{{ route('profile') }}" class="nav-link">My Profile
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-content-left  ml-3 header-user-info">
                                <div class="widget-heading"> {{ ucfirst(Auth::user()->name) }} </div>
                                <div class="widget-subheading"> ADMIN </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="header-btn-lg">
                    <button type="button" class="hamburger hamburger--elastic open-right-drawer">
                    <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                    </span>
                    </button>
                </div> -->
            </div>
        </div>
    </div>
    <div class="app-main">
        <div class="app-sidebar sidebar-shadow">
            <div class="app-header__logo">
                <div class="logo-src"></div>
                <div class="header__pane ml-auto">
                    <div>
                        <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                        <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                        </span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="app-header__mobile-menu">
                <div>
                    <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                    <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                    </span>
                    </button>
                </div>
            </div>
            <div class="app-header__menu">
                <span>
                <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
                </button>
                </span>
            </div>
            <div class="scrollbar-sidebar">
                <div class="app-sidebar__inner">
                    <ul class="vertical-nav-menu">
                        <li class="app-sidebar__heading">Menu</li>
                            <li class="{{ Request::is('/') ? 'mm-active' : '' }}">
                                <a href="{{ route('dashboard') }}" >
                                    <i class="metismenu-icon pe-7s-rocket"></i>Dashboard
                                </a>
                            </li>
                            <li class="{{ Request::is('users*') ? 'mm-active' : '' }}">
                                <a href="{{ route('users.index') }}" >
                                    <i class="metismenu-icon pe-7s-id"></i>Users
                                </a>
                            </li>
                            <li class="{{ Request::is('schools*') ? 'mm-active' : '' }}">
                                <a href="{{ route('schools.index') }}" >
                                    <i class="metismenu-icon pe-7s-display2"></i>Schools
                                </a>
                            </li>
                            <li class="{{ Request::is('promo-codes*') ? 'mm-active' : '' }}">
                                <a href="{{ route('promo-codes.index') }}" >
                                    <i class="metismenu-icon pe-7s-diamond"></i>Promocodes
                                </a>
                            </li>
                            <li class="{{ Request::is('feeds*') ? 'mm-active' : '' }}">
                                <a href="{{ route('feeds.index') }}" >
                                    <i class="metismenu-icon pe-7s-graph1"></i>Feeds
                                </a>
                            </li>
                            <li class="{{ Request::is('subscription_plan*') ? 'mm-active' : '' }}">
                                <a href="{{ route('subscription_plan.index') }}" >
                                    <i class="metismenu-icon pe-7s-way"></i>Subscription Plans
                                </a>
                            </li>
                            <li class="{{ Request::is('groups*') ? 'mm-active' : '' }}">
                                <a href="{{ route('groups.index') }}" >
                                    <i class="metismenu-icon pe-7s-light"></i>Groups
                                </a>
                            </li>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="app-main__outer">
            <div class="app-main__inner">
                @yield('content')
            </div>
            <div class="app-wrapper-footer">
                <div class="app-footer">
                    <div class="app-footer__inner">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="app-drawer-overlay d-none animated fadeIn"></div>
<script type="text/javascript" src="{{ asset('js/main.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
<script src="https://cdn.ckeditor.com/4.15.1/standard/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/21.0.0/decoupled-document/ckeditor.js"></script>
@stack('custom-scripts')
</body>
</html>
<div class="modal" id="rejectionModal" tabindex="-1" role="dialog" aria-labelledby="rejectionModal"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header text-center">
        <h4 class="modal-title w-100 font-weight-bold">Reject User </h4>
        <button type="button" class="close close-modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body mx-3">
        <div class="md-form mb-5">
            <label data-error="wrong" data-success="right" for="defaultForm-email">Description for Rejection</label>
            <textarea id="description-for-rejection" class="form-control validate rejection-description"></textarea>
            <span class="rejection-description-err" style="display:none; color:red">Description is required</span>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-center">
        <button class="btn btn-default reject-btn">Reject</button>
      </div>
    </div>
  </div>
</div>
<script>
    $('.close-modal').on('click', function() {
        $('.rejection-description').val('');
        $('.rejection-description-err').hide();
        $('#rejectionModal').removeAttr('data-id');
        $('#rejectionModal').hide();
    })
    $('.reject-btn').on('click', function() {
        var desc = $('#description-for-rejection').val();
        var user_id = $('#rejectionModal').attr('data-id');
        if(desc && user_id) {
            var url = " {{ url('users') }}/" + user_id;
            console.log(url);
            $.ajax({
                url: url,
                type: "POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: { description : desc, '_method' : "PUT"}, 
                success: function(data) {
                    if(data.status == 200) {
                        location.reload();
                    }
                },
                error: function(error) {
                    if(error.responseJSON != '' && error.status == 400) {
                        alert(error.responseJSON.error);
                    }
                }
            });
        } else {
            $('.rejection-description-err').show();
        }
    })
</script>




