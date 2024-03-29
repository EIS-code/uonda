@extends('layouts.app')

<script src="{{ env('APP_URL') }}:6002/socket.io/socket.io.js"></script>
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
    var appUrl = "{{ env('APP_URL') }}";
    var socket = io.connect(appUrl + ':6002');
    var chatRoute = '{{ route("user.chat.send") }}';
    var senderId   = '{{ $userId }}';
    var receiverId = '{{ $sendBy }}';
    var groupId = '{{ $groupId }}';
    var joinData = {};
    var userName = '{{ auth()->user()->name }}';

    socket.on('connect', function() {
        socket.on('connected', function (data) {
            /*let oPromise = new Promise(function(resolve, reject) {
                socket.emit('individualJoin', {'senderId': senderId, 'receiverId': receiverId}, function (responseData) {
                    joinData = responseData;

                    resolve();
                });
            });*/

            let oPromise = new Promise(function(resolve, reject) {
                socket.emit('groupJoin', {groupId: groupId, 'senderId': senderId}, function (responseData) {
                    joinData = responseData;
                    // console.log(joinData);
                    resolve();
                });
            });

            oPromise.then(function(response) {
                if (joinData) {
                    socket.emit('doOnline', senderId);

                    socket.on('error-' + senderId + '-' + receiverId, function (data) {
                        console.log(data);
                    });

                    socket.on('error', function (data) {
                        console.log(data);
                    });

                    socket.on('roomId', function (data) {
                        console.log('roomId : ' + data.id);
                    });

                    socket.on('roomData-' + senderId + '-' + receiverId, function (data) {
                        // console.log(data);
                    });

                    socket.on('roomData-' + groupId, function (data) {
                        console.log(data);
                    });

                    /*socket.emit('messageHistory', Object.assign({}, joinData, {'id': 482}));

                    socket.on('messageDetails-' + groupId, function (data) {
                        console.log(data);
                    });*/

                    /*socket.on('messageRecieve-' + receiverId + '-' + senderId, function (data) {
                        console.log("messageRecieve", data);

                        $( "#messages" ).append( "<strong> " + userName + " :</strong><p>"+data.message+"</p>" );
                    });*/

                    // socket.emit('messageSendAttachment', {id: 274});

                    /*socket.on('messageAcknowledge-' + senderId + '-' + receiverId, function (data) {
                        console.log("messageAcknowledge", data);

                        $( "#messages" ).append( "<strong> " + userName + " :</strong><p>"+data.message+"</p>" );
                    });*/

                    /* For Groups. */
                    socket.on('messageRecieve-' + groupId, function (data) {
                        if (data.sender_id != senderId) {
                            console.log("messageRecieve", data);

                            $( "#messages" ).append( "<strong> " + userName + " :</strong><p>"+data.message+"</p>" );
                        }
                    });

                    // socket.emit('messageSendAttachment', {id: 274});

                    socket.on('messageAcknowledge-' + senderId, function (data) {
                        console.log("messageAcknowledge", data);

                        $( "#messages" ).append( "<strong> " + userName + " :</strong><p>"+data.message+"</p>" );
                    });

                    socket.on('getOnline', function (data) {
                        // console.log(data);
                    });

                    // socket.emit('messageSendAttachment', Object.assign({}, joinData, {'id': 482}));
                }
            });
        });
    });

    $(".send-msg").click(function(e){
        e.preventDefault();
        var token = $("input[name='_token']").val();
        var user = $("input[name='user']").val();
        var msg = $(".msg").val();

        if (msg != '') {
            // console.log('messageSend-' + senderId + '-' + receiverId);

            socket.emit('messageSend', Object.assign({}, joinData, {'message': msg}));

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
