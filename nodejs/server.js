// Global Variables.
let app     = require('express')();
let server  = require('http').Server(app);
let cors    = require('cors');
let mysql   = require('mysql');
// var redis   = require('redis');
let env     = require('dotenv');
let envPath = { path: '../.env' };

let opts = {
    extraHeaders: {
       'Access-Control-Allow-Origin': '*',
       'Access-Control-Allow-Credentials': 'false'
    },
    cors: {
        origin: env.config(envPath).parsed.APP_URL,
        methods: ["GET", "POST"],
        credentials: false
    }
};

let io = require('socket.io')(server, opts);

app.use(cors());

app.use(function (req, res, next) {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'DELETE, GET, POST, PUT, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept-Type');
    res.header('Access-Control-Allow-Credentials', 'true');
    next();
});

// MySql
let con  = mysql.createPool({
    connectionLimit : 100000,//2000000, // default = 10
    host            : env.config(envPath).parsed.DB_HOST,
    user            : env.config(envPath).parsed.DB_USERNAME,
    password        : env.config(envPath).parsed.DB_PASSWORD,
    database        : env.config(envPath).parsed.DB_DATABASE,
    strict          : false,
    socketPath      : env.config(envPath).parsed.APP_ENV == 'dev' ? '/opt/lampp/var/mysql/mysql.sock' : ''
});

// Database tables.
let modelUsers         = 'users',
    modelChatRooms     = 'chat_rooms',
    modelChatRoomUsers = 'chat_room_users',
    modelChats         = 'chats';

// Global variables.
var isError = false;

/*io.use((socket, next)=>{

})*/

io.on('connection', function (socket) {
    /*var redisClient = redis.createClient();
    redisClient.subscribe('messageSend');*/

    socket.on('individualJoin', function(joinData) {

        if (typeof joinData === typeof undefined) {
            io.emit('error', {error: "Provide senderId and receiverId."});
            return false;
            isError = true;
        } else if (typeof joinData.senderId === typeof undefined) {
            io.emit('error', {error: "Provide senderId."});
            return false;
            isError = true;
        } else if (typeof joinData.receiverId === typeof undefined) {
            io.emit('error', {error: "Provide receiverId."});
            return false;
            isError = true;
        }

        try {
            var senderId   = joinData.senderId,
                receiverId = joinData.receiverId;
        } catch(error) {
            io.emit('error', {error: "Provide senderId and receiverId."});
            return false;
            isError = true;
        }

        // Join Rooms
        let roomId = 'individualJoin-' + senderId;
        socket.join(roomId);

        // Emit room id.
        io.sockets.to(roomId).emit('roomId', {id: roomId});

        // Create room.
        var uuid            = generateUuid(10),
            now             = mysqlDate(new Date()),
            timestampsQuery = "`created_at` = '" + now + "', `updated_at` = '" + now + "'",
            chatRoomId      = false,
            chatRoomUserId  = false;

        // Check is exists.
        con.getConnection(function(err, connection) {
            if (err) {
                io.emit('error', {error: err.message});
                return false;
                isError = true;
            };

            let sqlCheckRoomUser = "SELECT * FROM `" + modelChatRoomUsers + "` WHERE (`sender_id` = '" + senderId + "' AND `receiver_id` = '" + receiverId + "' OR `sender_id` = '" + receiverId + "' AND `receiver_id` = '" + senderId + "') LIMIT 1";

            connection.query(sqlCheckRoomUser, function (err1, chatRoomUser) {
                if (err1) {
                    io.emit('error', {error: err1.message});
                    return false;
                    isError = true;
                };

                if (chatRoomUser.length <= 0) {
                    let sqlInsertRoom = "INSERT INTO `" + modelChatRooms + "` SET `uuid` = '" + uuid + "', " + timestampsQuery;

                    connection.query(sqlInsertRoom, function (err2, insertRoom) {
                        if (err2) {
                            io.emit('error', {error: err2.message});
                            return false;
                            isError = true;
                        };

                        chatRoomId = insertRoom.insertId;
                    });
                } else {
                    chatRoomId     = chatRoomUser[0].chat_room_id;
                    chatRoomUserId = chatRoomUser[0].id;
                }

                // Check is exists.
                let sqlCheckRoomUser = "SELECT * FROM `" + modelChatRoomUsers + "` WHERE `sender_id` = '" + senderId + "' AND `receiver_id` = '" + receiverId + "' LIMIT 1";

                connection.query(sqlCheckRoomUser, function (err8, checkRoomUser) {
                    if (err8) {
                        io.emit('error', {error: err8.message});
                        return false;
                        isError = true;
                    };

                    if (checkRoomUser.length <= 0) {
                        let sqlInsertRoomUser = "INSERT INTO `" + modelChatRoomUsers + "` SET `chat_room_id` = '" + chatRoomId + "', `sender_id` = '" + senderId + "', `receiver_id` = '" + receiverId + "', " + timestampsQuery;
                        connection.query(sqlInsertRoomUser, function (err3, insertRoomUser) {
                            if (err3) {
                                io.emit('error', {error: err3.message});
                                return false;
                                isError = true;
                            };

                            chatRoomUserId = insertRoomUser.insertId;
                        });
                    }
                });

                if (!isError) {
                    socket.on("messageSend", function(message) {
                        let sqlQuery  = "INSERT INTO `" + modelChats + "` SET `message` = '" + message.message + "', `chat_room_id` = '" + chatRoomId + "', `chat_room_user_id` = '" + chatRoomUserId + "', " + timestampsQuery;

                        connection.query(sqlQuery, async function (err4, insertChat, fields) {
                            if (err4) {
                                io.emit('error', {error: err4.message});
                                return false;
                                isError = true;
                            };

                            let sqlGetChat = "SELECT id, message FROM `" + modelChats + "` as c WHERE c.`id` = '" + insertChat.insertId + "' LIMIT 1";

                            connection.query(sqlGetChat, async function (err5, resultChat, fields) {
                                if (err5) {
                                    io.emit('error', {error: err5.message});
                                    return false;
                                    isError = true;
                                };

                                var senderData   = {},
                                    receiverData = {};

                                let sqlGetSenderUser = "SELECT * FROM `" + modelUsers + "` WHERE `id` = '" + senderId + "' LIMIT 1";

                                connection.query(sqlGetSenderUser, async function (err6, resultSenderUser, fields) {
                                    if (err6) {
                                        io.emit('error', {error: err6.message});
                                        return false;
                                        isError = true;
                                    };

                                    senderData = {type: "new-message", message: resultChat[0].message, user: resultSenderUser[0]};

                                    io.sockets.to('individualJoin-' + senderId).emit('messageAcknowledge', senderData);
                                });

                                let sqlGetReceiverUser = "SELECT * FROM `" + modelUsers + "` WHERE `id` = '" + receiverId + "' LIMIT 1";

                                connection.query(sqlGetReceiverUser, async function (err7, resultReceiverUser, fields) {
                                    if (err7) {
                                        io.emit('error', {error: err7.message});
                                        return false;
                                        isError = true;
                                    };

                                    receiverData = {type: "new-message", message: resultChat[0].message, 'user': resultReceiverUser[0]};

                                    io.sockets.to('individualJoin-' + receiverId).emit('messageRecieve', receiverData);
                                });
                            });
                        });
                    });
                }
            });

            connection.release();
        });
    });

    

    socket.on('disconnect', function() {
        console.log('disconnected');
    });
});

server.listen(6002, () => {
    console.log('started on port 6002');
});

// List of functions.
function mysqlDate(dateVal)
{
    let newDate = new Date(dateVal);
    let sMonth  = padValue(newDate.getMonth() + 1);
    let sDay    = padValue(newDate.getDate());
    let sYear   = newDate.getFullYear();
    let sHour   = newDate.getHours();
    let sMinute = padValue(newDate.getMinutes());
    let sSecond = padValue(newDate.getSeconds());

    sHour = padValue(sHour);

    return sYear + "-" + sMonth + "-" + sDay + " " + sHour + ":" + sMinute + ":" + sSecond ;
}
function padValue(value)
{
    return (value < 10) ? "0" + value : value;
}
function generateUuid(count) {
    let _sym = 'abcdefghijklmnopqrstuvwxyz1234567890',
        str  = '';

    for(var i = 0; i < count; i++) {
        str += _sym[parseInt(Math.random() * (_sym.length))];
    }

    return str;
}
