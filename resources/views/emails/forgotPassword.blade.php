<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
        <meta charset="utf-8"> <!-- utf-8 works for most cases -->
        <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
        <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'UONDA') }}</title>

        @include('emails.css')
    </head>
    <body>
        @php
            $user = $body;
        @endphp
        <span class="preheader"></span>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
            <tr>
                <td>&nbsp;</td>
                <td class="container">
                    <div class="content">
                        <!-- START CENTERED WHITE CONTAINER -->
                        <table role="presentation" class="main">
                            <!-- START MAIN CONTENT AREA -->
                            <tr>
                                <td class="wrapper">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td>
                                                <p>Hi there,</p>
                                                <p><h4><b>{{ $user->name }}</b>{{ __(' having trouble with system. Click below link for reset password (If click not working then copy paste given URL).') }}</h4></p>
                                                <p>
                                                    <h4><b><u><a href="#"></a></u></b></h4>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- END MAIN CONTENT AREA -->
                        </table>
                        <!-- END CENTERED WHITE CONTAINER -->
                        <!-- START FOOTER -->
                        <div class="footer">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-block">
                                        <span class="apple-link">
                                            <h2>
                                                <a href="{{ env('APP_URL') }}" target="__black">
                                                    {{ config('app.name', 'UONDA') }}
                                                </a>
                                            </h2>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!-- END FOOTER -->
                    </div>
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </body>
</html>
