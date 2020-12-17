@extends('layouts.app')

<script src="http://{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>
<script src="{{ asset('/js/app.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

@section('content')
<form id="newMessageForm">
    <div class="form-group">
        <label for="messageInput">Message</label>
        <input type="text" class="form-control" id="messageInput" placeholder="Enter some message">
    </div>

    <button id="submitButton" type="submit" class="btn btn-primary">Submit</button>

    <br/><br/>

    <div class="form-group">
        <label for="messagesList">Received messages:</label>
        <ul id="messagesList" class="list-group">
        </ul>
    </div>
</form>

<script>
    var chatRoute = '{{ route("user.chat.send") }}';
    var userId = '{{ $userId }}';
    var sendBy = '{{ $sendBy }}';

    $("#newMessageForm").submit(function(event) {
        event.preventDefault();
        const message = $("#messageInput").val();

        if (message === "") {
            alert("Please add some message!");
            return;
        }

        postMessageToBackend(message);
    });

    $.ajaxSetup({
        beforeSend: function(xhr) {
            xhr.setRequestHeader("api-key", "f8225af31740d7e9a32ac01419775dbe");
        }
    });

    function postMessageToBackend(message) {
        $.post(chatRoute, {
            "_token": "{{ csrf_token() }}",
            "message": message,
            "request_user_id": sendBy,
            "user_id": userId
        }).fail(function() {
            alert("Error occurred during sending the data. Please retry again later.");
        }).done(function() {
            $("#messageInput").val("");
        });
    }

    window.Echo.channel('users.' + userId + '.' + sendBy).listen('MessageCreated', (e) => {
        addMessageToList(e.message);
    });

    function addMessageToList(message) {
        const newLi = `<li class="list-group-item">${message}</li>`;
        $("#messagesList").prepend(newLi);
    }
</script>
@endsection
