@extends('layouts.app')

<script src="{{env('APP_URL')}}:6002/socket.io/socket.io.js"></script>
<script src="{{ asset('/js/app.js') }}"></script>

@section('content')
<style type="text/css">
    #messages{
        border: 1px solid black;
        height: 300px;
        margin-bottom: 8px;
        overflow: scroll;
        padding: 5px;
    }
</style>
<div class="container spark-screen">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Chat Message Module</div>
                <div class="panel-body">
 
                <div class="row">
                    <div class="col-lg-8" >
                      <div id="messages" ></div>
                    </div>
                    <div class="col-lg-8" >
                            <form action="sendmessage" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" >
                                <input type="hidden" name="user" value="{{ Auth::user()->name }}" >
                                <textarea class="form-control msg"></textarea>
                                <br/>
                                <input type="button" value="Send" class="btn btn-success send-msg">
                            </form>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var socket = io.connect('http://evolution_community_uonda:6002');
    var chatRoute = '{{ route("user.chat.send") }}';
    var userId = '{{ $userId }}';
    var sendBy = '{{ $sendBy }}';

    socket.on('connect', function() {
        console.log("connected !!");

        socket.on('messageRecieve', function (data) {
            // data = jQuery.parseJSON(data);
            // console.log(data);

            $( "#messages" ).append( "<strong>"+data.user.name+":</strong><p>"+data.message+"</p>" );
        });
    });

    $(".send-msg").click(function(e){
        e.preventDefault();
        var token = $("input[name='_token']").val();
        var user = $("input[name='user']").val();
        var msg = $(".msg").val();

        if (msg != '') {
            socket.emit('messageSend', {'_token':token, 'message':msg, 'user':user, "send_by": sendBy, "user_id": userId});
            /*$.ajax({
                type: "POST",
                url: chatRoute,
                dataType: "json",
                data: {'_token':token, 'message':msg, 'user':user, "request_user_id": sendBy, "user_id": userId},
                success:function(data){
                    $(".msg").val('');
                }
            });*/
        } else {
            alert("Please Add Message.");
        }
    });
</script>
@endsection
