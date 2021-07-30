<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @if (env('APP_ENV') == 'production' && env('APP_PROTOCOL') == 'https')
        <meta http-equiv="Content-Security-Policy" content="block-all-mixed-content">
    @else
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @endif

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Styles -->
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/cropper/cropper.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body>
<div class="app-container app-theme-white body-tabs-shadow fixed-header fixed-sidebar ">
    @auth
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
                <div class="dropdown">
                    <a class="dropbtn fa fa-bell read-all" data-ids="">
                        <span class="badge">0</span>
                    </a>

                    <div id="notification-dropdown" class="dropdown-content">
                        <a href="{{ route('notification.index') }}">
                            <li class="text-center all-notifications">{{ __('Open all') }}</li>
                        </a>
                    </div>
                </div>
                
                <div class="app-header-right">
                    <div class="header-btn-lg pr-0">
                        <div class="widget-content p-0">
                            <div class="widget-content-wrapper">
                                <div class="widget-content-left">
                                    <div class="btn-group">
                                        <a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                        <img width="42" class="rounded-circle" src="{{ !empty(Auth::user()->profile) ? URL::asset('storage/admin-profile/'. Auth::user()->profile) : asset('assets/images/avatars/2.jpg') }}" alt="">
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
                                                                    <img width="42" class="rounded-circle" src="{{ !empty(Auth::user()->profile) ? URL::asset('storage/admin-profile/'. Auth::user()->profile) : asset('assets/images/avatars/2.jpg') }}" alt="">
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
<!--                     <div class="header-btn-lg">
                        <button type="button" class="hamburger hamburger--elastic open-right-drawer">
                        <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                        </span>
                        </button>
                    </div> -->
                </div>
            </div>
        </div>
    @endauth
    <div class="app-main">
        @auth
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
                                    <a href="javascript::void(0)">
                                        <i class="metismenu-icon pe-7s-id"></i>Users
                                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                    </a>
                                    <ul>
                                        <li class="{{ Request::is('users-list/accepted*') ? 'mm-active' : '' }}">
                                            <a href="{{ route('users.index', 'accepted') }}" >
                                                <i class="metismenu-icon"></i>Accepted Users
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('users-list/rejected*') ? 'mm-active' : '' }}">
                                            <a href="{{ route('users.index', 'rejected') }}" >
                                                <i class="metismenu-icon"></i>Rejected Users
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('users-list/pending*') ? 'mm-active' : '' }}">
                                            <a href="{{ route('users.index', 'pending') }}" >
                                                <i class="metismenu-icon"></i>Pending Users
                                            </a>
                                        </li>
                                    </ul>
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
                                <!-- <li class="{{ Request::is('groups*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('groups.index') }}" >
                                        <i class="metismenu-icon pe-7s-light"></i>Groups
                                    </a>
                                </li> -->
                                <li class="{{ Request::is('chat*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('chats.index') }}" >
                                        <i class="metismenu-icon pe-7s-chat"></i>Chat Groups
                                    </a>
                                </li>
                                <li class="{{ Request::is('reports-questions*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('reports-questions.index') }}" >
                                        <i class="metismenu-icon pe-7s-news-paper"></i>Report Questions
                                    </a>
                                </li>
                                <li class="{{ Request::is('users-reports*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('users-reports') }}" >
                                        <i class="metismenu-icon pe-7s-note2"></i>User Reports
                                    </a>
                                </li>                                
                                <li class="{{ Request::is('settings*') ? 'mm-active' : '' }}">
                                    <a href="javascript::void(0)">
                                        <i class="metismenu-icon pe-7s-config"></i>Settings
                                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                    </a>
                                    <ul>
                                        <li class="{{ Request::is('settings/constants*') ? 'mm-active' : '' }}">
                                            <a href="{{ route('settings.constants') }}" >
                                                <i class="metismenu-icon"></i>Constants
                                            </a>
                                        </li>
                                        <li class="{{ Request::is('settings/notification*') ? 'mm-active' : '' }}">
                                            <a href="javascript::void(0)" >
                                                <i class="metismenu-icon"></i>Texts
                                                <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                                            </a>
                                            <ul>
                                                <li class="{{ Request::is('settings/notification/texts*') ? 'mm-active' : '' }}">
                                                    <a href="{{ route('notification.texts') }}" >
                                                        <i class="metismenu-icon"></i>Notification texts
                                                    </a>
                                                </li>
                                                <li class="{{ Request::is('settings/apiResponse/texts*') ? 'mm-active' : '' }}">
                                                    <a href="{{ route('apiResponse.texts') }}" >
                                                        <i class="metismenu-icon"></i>API response texts
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="{{ Request::is('settings/email/templates*') ? 'mm-active' : '' }}">
                                            <a href="{{ route('settings.email.templates.get') }}" >
                                                <i class="metismenu-icon"></i>Email Templates
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                                                                                                                                                                             
                                <li class="{{ Request::is('notification*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('notification.index') }}" >
                                        <i class="metismenu-icon pe-7s-info"></i>Notification
                                    </a>
                                </li>
                                <li class="{{ Request::is('emails*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('emails.index') }}" >
                                        <i class="metismenu-icon pe-7s-mail"></i>Sent Emails
                                    </a>
                                </li>
                                <li class="{{ Request::is('blocked-users*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('blocked-users') }}" >
                                        <i class="metismenu-icon pe-7s-delete-user"></i>Blocked Users 
                                    </a>
                                </li>
                                <li class="{{ Request::is('promotions*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('promotions.index') }}" >
                                        <i class="metismenu-icon pe-7s-medal"></i>Promotions
                                    </a>
                                </li>
                                <li class="{{ Request::is('country*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('country.index') }}" >
                                        <i class="metismenu-icon pe-7s-world"></i>Country
                                    </a>
                                </li>
                                <li class="{{ Request::is('state*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('state.index') }}" >
                                        <i class="metismenu-icon pe-7s-global"></i>State
                                    </a>
                                </li>
                                <li class="{{ Request::is('city*') ? 'mm-active' : '' }}">
                                    <a href="{{ route('city.index') }}" >
                                        <i class="metismenu-icon pe-7s-global"></i>City
                                    </a>
                                </li>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @endauth
        <div class="app-main__outer" style="@auth @else padding-left: 0px !important; @endauth">
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
<!-- <script src="https://cdn.ckeditor.com/4.15.1/standard/ckeditor.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/21.0.0/decoupled-document/ckeditor.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script type="text/javascript" src="{{ asset('plugins/cropper/cropper.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/ckeditor/ckeditor.js') }}"></script>
<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.7.0/firebase.js"></script>
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
    function readAll(event) {
        event.preventDefault();

        // document.getElementById("notification-dropdown").classList.toggle("show");
        $(document).find('#notification-dropdown').toggleClass("show");

        let self            = $(this),
            notificationIds = self.attr('data-ids');

        if (notificationIds.length > 0) {
            $.ajax({
                url: "{{ route('notification.read.all') }}",
                type: "POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: {"ids": JSON.parse(notificationIds)},
                success: function(data) {
                    if (data > '0') {
                        $(document).find('.dropbtn').find('.badge').html("0");

                        // $(document).find('#notification-dropdown').find('a.actual-notification').remove();

                        self.attr('data-ids', "");
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
    }

    function appendNotification(payload)
    {
        let title                = payload.notification.title;

        let notificationDropdown = $(document).find('#notification-dropdown');

        if (title != "" && title != null && title.length > 0 && notificationDropdown.length > 0) {
            getAllNotificaionts();
        }
    }

    function getAllNotificaionts()
    {
        let notificationDropdown  = $(document).find('.dropdown'),
            notificationDropbtn   = notificationDropdown.find('.dropbtn'),
            notificationCounter   = notificationDropdown.find('.badge'),
            notificationLi        = notificationDropdown.find('#notification-dropdown'),
            notificationIds       = [],
            notificationHtml      = "",
            notificationRoute     = "{{ route('notification.index') }}";

        notificationDropbtn.attr('data-ids', "");
        notificationLi.prepend(notificationHtml).find('a.actual-notification').remove();

        $.ajax({
            url: "{{ route('notification.get.all') }}",
            type: "GET",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(data) {
                let dataArray = JSON.parse(data);

                notificationCounter.empty();
                notificationCounter.html(dataArray.length);

                if (dataArray.length > 0) {
                    $.each(dataArray, function(key, item) {
                        if (key > 9) {
                            return false;
                        }

                        let notificationHref = notificationRoute + "?search=" + item.id + "#" + item.id;

                        notificationIds.push(item.id);

                        notificationHtml += "<a href=\"" + notificationHref + "\" class=\"actual-notification\">";
                            notificationHtml += "<li>";
                                notificationHtml += item.title;
                            notificationHtml += "</li>";
                        notificationHtml += "</a>";
                    });

                    notificationDropbtn.attr('data-ids', JSON.stringify(notificationIds));
                    notificationLi.prepend(notificationHtml);
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    $(document).find('.read-all').on("click", readAll);

    getAllNotificaionts();

      // Close the dropdown if the user clicks outside of it
      window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
          var dropdowns = document.getElementsByClassName("dropdown-content");
          var i;
          for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
              openDropdown.classList.remove('show');

              getAllNotificaionts();
            }
          }
        }
      }
    
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
    });

    // Web FCM.
    // Your web app's Firebase configuration
    // For Firebase JS SDK v7.20.0 and later, measurementId is optional
    var apiKey              = "{{ env('FCM_WEB_API_KEY', '') }}",
        authDomain          = "{{ env('FCM_WEB_AUTH_DOMAIN', '') }}",
        projectId           = "{{ env('FCM_WEB_PROJECT_ID', '') }}",
        storageBucket       = "{{ env('FCM_WEB_STORAGE_BUCKET', '') }}",
        messagingSenderId   = "{{ env('FCM_SENDER_ID', '') }}",
        appId               = "{{ env('FCM_WEB_APP_ID', '') }}",
        measurementId       = "{{ env('FCM_WEB_MEASUREMENT_ID', '') }}";

    var firebaseConfig = {
        apiKey: apiKey,
        authDomain: authDomain,
        projectId: projectId,
        storageBucket: storageBucket,
        messagingSenderId: messagingSenderId,
        appId: appId,
        measurementId: measurementId
    };

    firebase.initializeApp(firebaseConfig);

    const messaging = firebase.messaging();

    function startFCM() {
        messaging
            .requestPermission()
            .then(function () {
                return messaging.getToken()
            })
            .then(function (response) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '{{ route("store.token") }}',
                    type: 'POST',
                    data: {
                        token: response
                    },
                    dataType: 'JSON',
                    success: function (response) {
                        // console.log('Token stored.');
                    },
                    error: function (error) {
                        console.log(error);
                    },
                });

            }).catch(function (error) {
                console.log(error);
            });
    }

    messaging.onMessage(function (payload) {
        console.log(payload);

        const title = payload.notification.title;

        const options = {
            body: payload.notification.body,
            icon: payload.notification.icon,
            // click_action: "{{ route('notification.index') }}",
            // data: {click_action: "{{ route('notification.index') }}"}
        };

        var notification = new Notification(title, options);

        notification.onclick = function(event) {
            // Prevent the browser from focusing the Notification's tab
            event.preventDefault();

            window.open("{{ route('notification.index') }}", '_blank');

            notification.close();
        }

        appendNotification(payload);
    });

    startFCM();
</script>
