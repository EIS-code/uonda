// Global Variables.
let app     = require('express')();
let server  = require('http').Server(app);
let cors    = require('cors');
let mysql   = require('mysql');
// var redis   = require('redis');
let env     = require('dotenv');
let envPath = { path: '../.env' };
let axios   = require('axios');

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

let axiosConfigs = {};

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
    socketPath      : env.config(envPath).parsed.APP_ENV == 'dev' ? '/var/lib/mysql/mysql.sock' : '/var/run/mysqld/mysqld.sock'
});

// Database tables.
let modelUsers          = 'users',
    modelChatRooms      = 'chat_rooms',
    modelChatRoomUsers  = 'chat_room_users',
    modelChats          = 'chats',
    modelChatAttachment = 'chat_attachments',
    modelChatDelete     = 'chat_delets';

// Global variables.
var isError                     = false,
    onlineUsers                 = {},
    stringAppProtocol           = '${APP_PROTOCOL}',
    appProtocol                 = env.config(envPath).parsed.APP_PROTOCOL,
    appUrl                      = env.config(envPath).parsed.APP_URL,
    appUrl                      = (appUrl.includes(stringAppProtocol) === true) ? appUrl.replace(stringAppProtocol, appProtocol) : appUrl,
    attachmentUrl               = removeTrailingSlash(appUrl) + '/' + 'storage' + '/' + 'user' + '/' + 'chat' + '/' + 'attachment' + '/',
    listenerIndividual          = 'individualJoin',
    listenerGroup               = 'groupJoin',
    listenMessageSend           = "messageSend",
    listenerDisconnect          = 'disconnect',
    listenerSendAttachment      = "messageSendAttachment",
    listenerMessageHistory      = "messageHistory",
    listenerDoOnline            = "doOnline",
    emitterMessageAcknowledge   = 'messageAcknowledge-',
    emitterMessageReceive       = 'messageRecieve-',
    emitterMessageDetails       = 'messageDetails-',
    errorOnlyEmitter            = 'error',
    emitterOnline               = 'getOnline',
    socketIds                   = [];

io.on('connection', function (socket) {

    /* Emit connected. */
    socket.emit('connected', {
        connected: "Connected !!"
    });

    socket.on(listenerDoOnline, (userId) => {
        // Set online users.
        onlineUsers[socket.id] = userId;

        con.getConnection(function(err10, connection) {
            if (err10) {
                io.emit('error', {error: err10.message});
                return false;
                isError = true;
            }

            let sqlSetOnline = "UPDATE " + modelUsers + " SET `is_online` = '1', `socket_id` = '" + socket.id + "' WHERE `id` = '" + userId + "'";
            connection.query(sqlSetOnline, function (err11, setOnline) {
                if (err11) {
                    io.emit('error', {error: err11.message});
                    return false;
                    isError = true;
                }

                io.sockets.emit('getOnline', onlineUsers);
            });
        });
    });

    socket.on(listenerIndividual, function(joinData, callbackFunction) {
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
            var senderId        = joinData.senderId,
                receiverId      = joinData.receiverId,
                roomDataEmitter = 'roomData-' + senderId + '-' + receiverId,
                errorEmitter    = 'error-' + senderId + '-' + receiverId;
        } catch(error) {
            io.emit('error', {error: "Provide senderId and receiverId."});
            return false;
            isError = true;
        }

        // Join Rooms
        var roomId          = listenerIndividual + '-' + senderId + "-" + receiverId,
            receiverRoomId  = listenerIndividual + '-' + receiverId + "-" + senderId;

        try {
            if (!io.sockets.adapter.rooms[roomId]) {
                // socket.leave(roomId);

                socket.join(roomId);
            }

            /*if (io.sockets.adapter.rooms[receiverRoomId]) {
                socket.leave(receiverRoomId);
            }*/
        } catch(e) {
            /* Handle errors. */
        }

        // Emit room id.
        io.sockets.to(roomId).emit('roomId', {id: roomId});

        // Error Handling.
        var errorFun = function(errMessage) {
            io.sockets.to(roomId).emit(errorEmitter, {error: errMessage});

            isError = true;

            socket.leave(roomId);

            return false;
        };

        // Create room.
        var uuid            = generateUuid(10),
            now             = mysqlDate(new Date()),
            timestampsQuery = "`created_at` = NOW(), `updated_at` = NOW()",
            chatRoomId      = false,
            chatRoomUserId  = false;

        con.getConnection(function(err, connection) {
            if (err) {
                return errorFun(err.message);
            }

            let sqlCheckRoomUser = "SELECT * FROM `" + modelChatRoomUsers + "` WHERE ((`sender_id` = '" + senderId + "' AND `receiver_id` = '" + receiverId + "') OR (`sender_id` = '" + receiverId + "' AND `receiver_id` = '" + senderId + "')) LIMIT 1";

            connection.query(sqlCheckRoomUser, function (err1, chatRoomUser) {
                if (err1) {
                    return errorFun(err1.message);
                }

                let oPromise = new Promise(function(resolve, reject) {
                    if (chatRoomUser.length <= 0) {
                        let sqlInsertRoom = "INSERT INTO `" + modelChatRooms + "` SET `uuid` = '" + uuid + "', " + timestampsQuery;

                        connection.query(sqlInsertRoom, function (err2, insertRoom) {
                            if (err2) {
                                return errorFun(err2.message);
                            }

                            chatRoomId = insertRoom.insertId;

                            resolve();
                        });
                    } else {
                        chatRoomId     = chatRoomUser[0].chat_room_id;
                        chatRoomUserId = chatRoomUser[0].id;

                        resolve();
                    }
                });

                oPromise.then(function(response) {
                    let sPromise = new Promise(function(resolve, reject) {
                        // Check is exists.
                        let sqlCheckRoomUser = "SELECT * FROM `" + modelChatRoomUsers + "` WHERE `sender_id` = '" + senderId + "' AND `receiver_id` = '" + receiverId + "' LIMIT 1";

                        connection.query(sqlCheckRoomUser, function (err8, checkRoomUser) {
                            if (err8) {
                                return errorFun(err8.message);
                            }

                            if (checkRoomUser.length <= 0) {
                                let sqlInsertRoomUser = "INSERT INTO `" + modelChatRoomUsers + "` SET `chat_room_id` = '" + chatRoomId + "', `sender_id` = '" + senderId + "', `receiver_id` = '" + receiverId + "', " + timestampsQuery;
                                connection.query(sqlInsertRoomUser, function (err3, insertRoomUser) {
                                    if (err3) {
                                        return errorFun(err3.message);
                                    }

                                    chatRoomUserId = insertRoomUser.insertId;

                                    resolve();
                                });
                            } else {
                                chatRoomUserId = checkRoomUser[0].id;

                                resolve();
                            }
                        });
                    });

                    sPromise.then(function(response) {
                        let roomData = {senderId: senderId, receiverId: receiverId, chatRoomId: chatRoomId, chatRoomUserId: chatRoomUserId, isGroup: false};
                        // Emit room data.
                        socket.emit(roomDataEmitter, roomData);
                        /* Callbacks. */
                        callbackFunction(roomData);
                    });
                });
            });

            connection.release();
        });
    });

    if (!isError) {
        con.getConnection(function(err, connection) {

            let now             = mysqlDate(new Date()),
                timestampsQuery = "`created_at` = NOW(), `updated_at` = NOW()";

            socket.on(listenMessageSend, function(data, callbackFunction) {

                try {

                    var isGroup         = (data.isGroup == true),
                        chatRoomId      = data.chatRoomId,
                        chatRoomUserId  = data.chatRoomUserId,
                        senderId        = data.senderId;

                    if (isGroup) {
                        var message                 = data.message,
                            acknowledgeEmitter      = emitterMessageAcknowledge + senderId,
                            messageRecieveEmitter   = emitterMessageReceive + chatRoomId,
                            roomId                  = listenerGroup + '-' + chatRoomId,
                            errorEmitter            = 'error-' + chatRoomId;
                    } else {
                        var message                 = data.message,
                            receiverId              = data.receiverId,
                            acknowledgeEmitter      = emitterMessageAcknowledge + senderId + '-' + receiverId,
                            messageRecieveEmitter   = emitterMessageReceive + senderId + '-' + receiverId,
                            roomId                  = listenerIndividual + '-' + senderId + "-" + receiverId,
                            receiverRoomId          = listenerIndividual + '-' + receiverId + "-" + senderId,
                            errorEmitter            = 'error-' + senderId;
                    }

                } catch(error) {
                    io.emit('error', {error: "Provide chatRoomId, chatRoomUserId, senderId or receiverId."});
                    return false;
                    isError = true;
                }

                // Error Handling.
                var errorFun = function(errMessage) {
                    io.sockets.to(roomId).emit(errorEmitter, {error: errMessage});

                    isError = true;

                    socket.leave(roomId);

                    return false;
                };

                if (!isError) {
                    connection.query("SET NAMES utf8mb4;", async function (err15, resultUTFMB4, fields) {});

                    if (isGroup) {

                        let sqlQuery  = "INSERT INTO `" + modelChats + "` SET `message` = '" + message + "', `chat_room_id` = '" + chatRoomId + "', `chat_room_user_id` = '" + chatRoomUserId + "', " + timestampsQuery;

                        connection.query(sqlQuery, async function (err3, insertChat, fields) {
                            if (err3) {
                                return errorFun(err3.message);
                            }

                            let sqlGetChat = "SELECT c.id, c.message, ca.mime_type, ca.attachment, ca.address, ca.url, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type, UNIX_TIMESTAMP(c.created_at) * 1000 AS created_at FROM `" + modelChats + "` AS c LEFT JOIN `" + modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE c.`id` = '" + insertChat.insertId + "' LIMIT 1";

                            connection.query(sqlGetChat, async function (err4, resultChat, fields) {
                                if (err4) {
                                    return errorFun(err4.message);
                                }

                                var senderData   = {},
                                    receiverData = {};

                                if (resultChat[0]['attachment'] !== null && resultChat[0]['attachment'].length > 0) {
                                    resultChat[0]['attachment'] = buildAttachmentUrl(resultChat[0].id, resultChat[0]['attachment']);
                                }

                                resultChat[0].sender_id = senderId;
                                resultChat[0].groupId   = chatRoomId;

                                senderData = resultChat[0];

                                io.sockets.to(roomId).emit(acknowledgeEmitter, senderData);

                                receiverData = resultChat[0];

                                io.sockets.to(roomId).emit(messageRecieveEmitter, receiverData);

                                /* Callbacks. */
                                callbackFunction(receiverData);

                                // Send push notification if user is not online.
                                sendPushNotificationsGroup(chatRoomId, senderId, message);
                            });
                        });
                    } else {

                        let sqlQuery  = "INSERT INTO `" + modelChats + "` SET `message` = '" + message + "', `chat_room_id` = '" + chatRoomId + "', `chat_room_user_id` = '" + chatRoomUserId + "', " + timestampsQuery;

                        connection.query(sqlQuery, async function (err4, insertChat, fields) {
                            if (err4) {
                                return errorFun(err4.message);
                            }

                            // let sqlGetChat = "SELECT id, message FROM `" + modelChats + "` as c WHERE c.`id` = '" + insertChat.insertId + "' LIMIT 1";
                            let sqlGetChat = "SELECT c.id, c.message, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type, UNIX_TIMESTAMP(c.created_at) * 1000 AS created_at FROM `" + modelChats + "` AS c LEFT JOIN `" + modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE c.`id` = '" + insertChat.insertId + "' LIMIT 1";

                            connection.query(sqlGetChat, async function (err5, resultChat, fields) {
                                if (err5) {
                                    return errorFun(err5.message);
                                }

                                var senderData   = {},
                                    receiverData = {};

                                let sqlGetSenderUser = "SELECT `id`, `name`, `user_name`, `email`, `profile` FROM `" + modelUsers + "` WHERE `id` = '" + senderId + "' LIMIT 1";

                                connection.query(sqlGetSenderUser, async function (err6, resultSenderUser, fields) {
                                    if (err6) {
                                        return errorFun(err6.message);
                                    }

                                    if (resultChat[0]['attachment'] !== null && resultChat[0]['attachment'].length > 0) {
                                        resultChat[0]['attachment'] = buildAttachmentUrl(resultChat[0].id, resultChat[0]['attachment']);
                                    }

                                    // senderData = {type: "new-message", message: resultChat[0], user: resultSenderUser[0]};
                                    resultChat[0].sender_id  = senderId;
                                    resultChat[0].receiverId = receiverId;

                                    senderData = resultChat[0];
                                    io.sockets.to(roomId).emit(acknowledgeEmitter, senderData);

                                    /* Callbacks. */
                                    callbackFunction(senderData);
                                });

                                let sqlGetReceiverUser = "SELECT `id`, `name`, `user_name`, `email`, `profile` FROM `" + modelUsers + "` WHERE `id` = '" + receiverId + "' LIMIT 1";

                                connection.query(sqlGetReceiverUser, async function (err7, resultReceiverUser, fields) {
                                    if (err7) {
                                        return errorFun(err7.message);
                                    }

                                    if (resultChat[0]['attachment'] !== null && resultChat[0]['attachment'].length > 0) {
                                        resultChat[0]['attachment'] = buildAttachmentUrl(resultChat[0].id, resultChat[0]['attachment']);
                                    }

                                    // receiverData = {type: "new-message", message: resultChat[0], 'user': resultReceiverUser[0]};
                                    resultChat[0].sender_id  = senderId;
                                    resultChat[0].receiverId = receiverId;

                                    receiverData = resultChat[0];
                                    io.sockets.to(receiverRoomId).emit(messageRecieveEmitter, receiverData);

                                    /* Callbacks. */
                                    callbackFunction(receiverData);

                                    // Send push notification if user is not online.
                                    sendPushNotifications(receiverId, senderId, message, chatRoomId);
                                });
                            });
                        });
                    }
                }
            });

            socket.on(listenerSendAttachment, function(data, callbackFunction) {

                try {
                    var isGroup     = (data.isGroup == true),
                        chatId      = data.id,
                        senderId    = data.senderId;

                    if (isGroup) {
                        var chatRoomId              = data.chatRoomId,
                            chatRoomUserId          = data.chatRoomUserId,
                            acknowledgeEmitter      = emitterMessageAcknowledge + senderId,
                            messageRecieveEmitter   = emitterMessageReceive + chatRoomId,
                            roomId                  = listenerGroup + '-' + chatRoomId,
                            errorEmitter            = 'error-' + chatRoomId;
                    } else {
                        var receiverId              = data.receiverId,
                            roomId                  = listenerIndividual + '-' + senderId + "-" + receiverId,
                            receiverRoomId          = listenerIndividual + '-' + receiverId + "-" + senderId,
                            acknowledgeEmitter      = emitterMessageAcknowledge + senderId + '-' + receiverId,
                            messageRecieveEmitter   = emitterMessageReceive + senderId + '-' + receiverId,
                            errorEmitter            = 'error-' + senderId;
                    }
                } catch(error) {
                    io.emit('error', {error: "Provide chatId, senderId or receiverId."});
                    return false;
                    isError = true;
                }

                // Error Handling.
                var errorFun = function(errMessage) {
                    io.sockets.to(roomId).emit(errorEmitter, {error: errMessage});

                    isError = true;

                    socket.leave(roomId);

                    return false;
                };

                if (!isError) {
                    connection.query("SET NAMES utf8mb4;", async function (err15, resultUTFMB4, fields) {});

                    if (isGroup) {
                        // let sqlGetChat = "SELECT c.id, c.message, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type FROM `" + modelChats + "` AS c LEFT JOIN `" + modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE c.`id` = '" + chatId + "' LIMIT 1";
                        let sqlGetChat = "SELECT c.id, c.message, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type, UNIX_TIMESTAMP(c.created_at) * 1000 AS created_at FROM `" + modelChats + "` AS c LEFT JOIN `" + modelChatDelete + "` AS cd ON `c`.`id` = `cd`.`chat_id` AND `cd`.`user_id` = '" + senderId + "' LEFT JOIN `" + modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE c.`id` = '" + chatId + "' AND `cd`.`id` IS NULL LIMIT 1";

                        connection.query(sqlGetChat, async function (err14, resultChat, fields) {
                            if (err14) {
                                return errorFun(err14.message);
                            }

                            if (resultChat[0]['attachment'] !== null && resultChat[0]['attachment'].length > 0) {
                                resultChat[0]['attachment'] = buildAttachmentUrl(resultChat[0].id, resultChat[0]['attachment']);
                            }

                            resultChat[0].sender_id = senderId;
                            resultChat[0].groupId   = chatRoomId;

                            io.sockets.to(roomId).emit(acknowledgeEmitter, resultChat[0]);
                            io.sockets.to(roomId).emit(messageRecieveEmitter, resultChat[0]);

                            /* Callbacks. */
                            callbackFunction(resultChat[0]);
                        });

                    } else {
                        let sqlGetChat = "SELECT c.id, c.message, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type, UNIX_TIMESTAMP(c.created_at) * 1000 AS created_at FROM `" + modelChats + "` AS c LEFT JOIN `" + modelChatDelete + "` AS cd ON `c`.`id` = `cd`.`chat_id` AND `cd`.`user_id` = '" + senderId + "' LEFT JOIN `" + modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE c.`id` = '" + chatId + "' AND `cd`.`id` IS NULL LIMIT 1";

                        connection.query(sqlGetChat, async function (err14, resultChat, fields) {
                            if (err14) {
                                return errorFun(err14.message);
                            }

                            if (resultChat.length > 0) {
                                if (resultChat[0]['attachment'] !== null && resultChat[0]['attachment'].length > 0) {
                                    resultChat[0]['attachment'] = buildAttachmentUrl(resultChat[0].id, resultChat[0]['attachment']);
                                }

                                resultChat[0].sender_id  = senderId;
                                resultChat[0].receiverId = receiverId;

                                io.sockets.to(roomId).emit(acknowledgeEmitter, resultChat[0]);
                                io.sockets.to(receiverRoomId).emit(messageRecieveEmitter, resultChat[0]);

                                /* Callbacks. */
                                callbackFunction(resultChat[0]);
                            } else {

                                io.sockets.to(roomId).emit(acknowledgeEmitter, []);
                                io.sockets.to(receiverRoomId).emit(messageRecieveEmitter, []);

                                /* Callbacks. */
                                callbackFunction([]);
                            }
                        });
                    }
                }
            });

            socket.on(listenerMessageHistory, function(data) {

                try {
                    var isGroup     = (data.isGroup == true),
                        senderId    = data.senderId;

                    if (isGroup) {
                        var chatRoomId              = data.chatRoomId,
                            roomId                  = listenerGroup + '-' + chatRoomId,
                            messageDetailsEmitter   = emitterMessageDetails + chatRoomId,
                            errorEmitter            = 'error-' + chatRoomId;
                    } else {
                        var receiverId              = data.receiverId,
                            roomId                  = listenerIndividual + '-' + senderId + "-" + receiverId,
                            messageDetailsEmitter   = emitterMessageDetails + senderId + '-' + receiverId,
                            errorEmitter            = 'error-' + senderId;
                    }
                } catch(error) {
                    io.emit('error', {error: "Provide senderId or receiverId."});
                    return false;
                    isError = true;
                }

                // Error Handling.
                var errorFun = function(errMessage) {
                    io.sockets.to(roomId).emit(errorEmitter, {error: errMessage});

                    isError = true;

                    socket.leave(roomId);

                    return false;
                };

                if (isGroup) {

                    let sqlGetChatHistory = "SELECT c.id, c.message, cru.sender_id, cru.receiver_id, c.created_at, c.updated_at, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' WHEN c.message != '' THEN 'text' ELSE NULL END AS message_type, u.name AS user_name, u.profile, u.profile_icon FROM `" + modelChatRoomUsers +"` AS cru JOIN `" + modelChats + "` AS c ON cru.id = c.chat_room_user_id JOIN `" + modelChatRooms + "` AS cr ON cru.chat_room_id = cr.id JOIN `" + modelUsers + "` AS u ON cru.sender_id = u.id LEFT JOIN `" + modelChatDelete + "` AS cd ON c.id = cd.chat_id AND cd.user_id = '" + senderId + "' LEFT JOIN `"+ modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE cr.id = '" + chatRoomId + "' AND cd.id IS NULL ORDER BY c.updated_at ASC";

                    connection.query(sqlGetChatHistory, function (err9, resultChatHistory, fields) {
                        if (err9) {
                            return errorFun(err9.message);
                        }

                        io.sockets.to(roomId).emit(messageDetailsEmitter, resultChatHistory);
                    });
                } else {

                    let sqlGetChatHistory = "SELECT c.id, c.message, cru.sender_id, cru.receiver_id, CASE cru.sender_id WHEN '" + senderId + "' THEN 'sender' ELSE 'receiver' END AS sender_receiver_flag, c.created_at, c.updated_at FROM `" + modelChatRoomUsers + "` AS cru JOIN `" + modelChats + "` AS c ON cru.id = c.chat_room_user_id WHERE ((cru.`sender_id` = '" + senderId + "' AND cru.`receiver_id` = '" + receiverId + "') OR (cru.`sender_id` = '" + receiverId + "' AND cru.`receiver_id` = '" + senderId + "'))";

                    connection.query(sqlGetChatHistory, function (err9, resultChatHistory, fields) {
                        if (err9) {
                            return errorFun(err9.message);
                        }

                        io.sockets.to(roomId).emit(messageDetailsEmitter, resultChatHistory);
                    });
                }
            });

            connection.release();
        });
    }

    isError = false;

    socket.on(listenerGroup, function(groupData, callbackFunction) {
        if (typeof groupData.groupId === typeof undefined) {
            io.emit('error', {error: "Provide groupId."});
            isError = true;
            return false;
        }

        try {
            var senderId = groupData.senderId,
                groupId  = groupData.groupId;
        } catch(error) {
            io.emit('error', {error: "Provide senderId and groupId."});
            isError = true;
            return false;
        }

        // Join Rooms
        var roomId              = listenerGroup + '-' + groupId,
            chatRoomId          = groupId,
            chatRoomUserId      = false,
            groupDataEmitter    = 'groupData-' + groupId,
            errorEmitter        = 'error-' + groupId;

        try {
            if (io.sockets.adapter.rooms[roomId]) {
                // socket.leave(roomId);
            }

            // Create room.
            socket.join(roomId);

            /*if (io.sockets.adapter.rooms[receiverRoomId]) {
                socket.leave(receiverRoomId);
            }*/
        } catch(e) {
            /* Handle errors. */
        }

        // Emit room id.
        io.sockets.to(roomId).emit('roomId', {id: roomId});

        // Error Handling.
        var errorFun = function(errMessage) {
            io.sockets.to(roomId).emit(errorEmitter, {error: errMessage});

            isError = true;

            socket.leave(roomId);

            return false;
        };

        con.getConnection(function(err, connection) {
            if (err) {
                return errorFun(err.message);
            }

            // Check is exists.
            let oPromise = new Promise(function(resolve, reject) {
                let sqlCheckRooms = "SELECT * FROM `" + modelChatRooms + "` WHERE `id` = '" + chatRoomId + "' LIMIT 1";

                connection.query(sqlCheckRooms, async function (err1, chatRooms) {
                    if (err1) {
                        return errorFun(err1.message);
                    }

                    if (chatRooms.length <= 0) {
                        return errorFun("Group not found.");
                    }

                    resolve();
                });
            });

            oPromise.then(function(response) {
                let sPromise = new Promise(function(resolve, reject) {
                    let sqlCheckRoomUser = "SELECT * FROM `" + modelChatRoomUsers + "` WHERE `chat_room_id` = '" + chatRoomId + "' AND `sender_id` = '" + senderId + "' LIMIT 1";

                    connection.query(sqlCheckRoomUser, async function (err2, chatRoomUser) {
                        if (err2) {
                            return errorFun(err2.message);
                        }

                        if (chatRoomUser.length <= 0) {
                            return errorFun("User not added in this group.");
                        } else {
                            chatRoomUserId = chatRoomUser[0].id;

                            resolve();
                        }
                    });
                });

                sPromise.then(function(response) {
                    let groupData = {senderId: senderId, chatRoomId: chatRoomId, chatRoomUserId: chatRoomUserId, isGroup: true};
                    // Emit room data.
                    socket.emit(groupDataEmitter, groupData);
                    /* Callbacks. */
                    callbackFunction(groupData);
                });
            });

            connection.release();
        });
    });

    /*if (!isError) {
        con.getConnection(function(err, connection) {
            socket.on(listenMessageSend, function(data) {
                socketIds[senderId] = socket.id;

                try {
                    var message                 = data.message,
                        chatRoomId              = data.chatRoomId,
                        chatRoomUserId          = data.chatRoomUserId,
                        senderId                = data.senderId,
                        acknowledgeEmitter      = emitterMessageAcknowledge + senderId,
                        messageRecieveEmitter   = emitterMessageReceive + chatRoomId,
                        roomId                  = listenerGroup + '-' + chatRoomId,
                        now                     = mysqlDate(new Date()),
                        timestampsQuery         = "`created_at` = NOW(), `updated_at` = NOW()",
                        errorEmitter            = 'error-' + chatRoomId;
                } catch(error) {
                    io.emit('error', {error: "Provide chatRoomId, chatRoomUserId, senderId & receiverId."});
                    return false;
                    isError = true;
                }

                // Error Handling.
                var errorFun = function(errMessage) {
                    io.sockets.to(roomId).emit(errorEmitter, {error: errMessage});

                    isError = true;

                    socket.leave(roomId);

                    return false;
                };

                let sqlQuery  = "INSERT INTO `" + modelChats + "` SET `message` = '" + message + "', `chat_room_id` = '" + chatRoomId + "', `chat_room_user_id` = '" + chatRoomUserId + "', " + timestampsQuery;

                connection.query(sqlQuery, async function (err3, insertChat, fields) {
                    if (err3) {
                        return errorFun(err3.message);
                    }

                    let sqlGetChat = "SELECT c.id, c.message, ca.mime_type, ca.attachment, ca.address, ca.url, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type FROM `" + modelChats + "` AS c LEFT JOIN `" + modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE c.`id` = '" + insertChat.insertId + "' LIMIT 1";

                    connection.query(sqlGetChat, async function (err4, resultChat, fields) {
                        if (err4) {
                            return errorFun(err4.message);
                        }

                        var senderData   = {},
                            receiverData = {};

                        if (resultChat[0]['attachment'] !== null && resultChat[0]['attachment'].length > 0) {
                            resultChat[0]['attachment'] = buildAttachmentUrl(resultChat[0].id, resultChat[0]['attachment']);
                        }

                        resultChat[0].sender_id = senderId;
                        resultChat[0].groupId   = chatRoomId;

                        senderData = resultChat[0];

                        io.sockets.to(roomId).emit(acknowledgeEmitter, senderData);

                        receiverData = resultChat[0];

                        io.sockets.to(roomId).emit(messageRecieveEmitter, receiverData);
                    });
                });
            });

            socket.on("messageSendAttachment", function(data) {
                if (typeof data === typeof undefined) {
                    return errorFun("Chat id is required.");
                } else if (typeof data.id === typeof undefined) {
                    return errorFun("Chat id is required.");
                }

                try {
                    var chatId = data.id;
                } catch(error) {
                    return errorFun("Chat id is required.");
                }

                if (!isError) {
                    // let sqlGetChat = "SELECT c.id, c.message, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type FROM `" + modelChats + "` AS c LEFT JOIN `" + modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE c.`id` = '" + chatId + "' LIMIT 1";
                    let sqlGetChat = "SELECT c.id, c.message, ca.mime_type, ca.attachment, ca.url, ca.address, ca.name, ca.contacts, CASE WHEN ca.mime_type != '' && ca.attachment != '' THEN 'attachment' WHEN ca.url != '' THEN 'location' WHEN ca.name && ca.contacts THEN 'contacts' ELSE NULL END AS message_type FROM `" + modelChats + "` AS c LEFT JOIN `" + modelChatDelete + "` AS cd ON `c`.`id` = `cd`.`chat_id` AND `cd`.`user_id` = '" + senderId + "' LEFT JOIN `" + modelChatAttachment + "` AS ca ON c.id = ca.chat_id WHERE c.`id` = '" + chatId + "' AND `cd`.`id` IS NULL LIMIT 1";

                    connection.query(sqlGetChat, async function (err14, resultChat, fields) {
                        if (err14) {
                            return errorFun(err14.message);
                        }

                        if (resultChat[0]['attachment'] !== null && resultChat[0]['attachment'].length > 0) {
                            resultChat[0]['attachment'] = buildAttachmentUrl(resultChat[0].id, resultChat[0]['attachment']);
                        }

                        resultChat[0].sender_id = senderId;
                        resultChat[0].groupId   = chatRoomId;

                        io.sockets.to(roomId).emit('messageAcknowledge-' + senderId, resultChat[0]);
                        io.sockets.to(roomId).emit('messageRecieve', resultChat[0]);
                    });
                }
            });

            connection.release();
        });
    }*/

    socket.on(listenerDisconnect, function() {
        console.log('Disconnected. SocetId : ' + socket.id);

        let userId = false;

        try {
            userId = onlineUsers[socket.id];
        } catch (e) {
            /*io.emit('error', {error: "UserId not found while disconnect."});
            return false;
            isError = true;*/
        }

        if (userId) {
            con.getConnection(function(err12, connection) {
                if (err12) {
                    io.emit(errorOnlyEmitter, {error: err12.message});
                    return false;
                    isError = true;
                }

                let sqlSetOnline = "UPDATE " + modelUsers + " SET `is_online` = '0', `socket_id` = '' WHERE `id` = '" + userId + "' AND `socket_id` = '" + socket.id + "'";
                connection.query(sqlSetOnline, function (err13, setOnline) {
                    if (err13) {
                        io.emit('error', {error: err13.message});
                        return false;
                        isError = true;
                    }

                    delete onlineUsers[socket.id];

                    io.sockets.emit(emitterOnline, onlineUsers);
                });
            });
        }
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

function removeTrailingSlash(url)
{
    return url.replace(/\/$/, "");
}

function buildAttachmentUrl(id, file)
{
    return attachmentUrl + id + '/' + file;
}

async function sendPushNotifications(receiverId, senderId, message, chatRoomId)
{
    var isOnline = false;

    if (Object.keys(onlineUsers).length) {
        Object.keys(onlineUsers).forEach(function(key) {
            let value = onlineUsers[key];

            if (value == receiverId) {
                isOnline = true;

                return false;
            }
        });
    }

    // !isOnline
    if (true) {
        axios.post(removeTrailingSlash(appUrl) + '/api/user/chat/notification/message/send', {
            "user_id": receiverId,
            "message": message,
            "from_user_id": senderId,
            "chat_room_id": chatRoomId
        }).then(function(response) {
            /*console.log(response.data);
            console.log(response.status);
            console.log(response.statusText);
            console.log(response.headers);
            console.log(response.config);*/
        }).catch(() => {});
    }
}

function getRoomUsers(roomId)
{
    return new Promise((resolve, reject) => {
        var roomUsers = [];

        con.getConnection(async function(err16, connection) {
            let sqlGetRoomUsers = "SELECT * FROM `" + modelChatRoomUsers + "` WHERE `chat_room_id` = '" + roomId + "' LIMIT 100";

            connection.query(sqlGetRoomUsers, function (err17, getRoomUser) {
                if (!err17) {
                    getRoomUser.forEach(function(data, key) {
                        roomUsers[key] = data.sender_id;
                    });

                    resolve(roomUsers);
                }
            });
        });

        // return roomUsers;
    });
}

async function sendPushNotificationsGroup(roomId, senderId, message)
{
    /*var isOnline  = false,
        roomUsers = await getRoomUsers(roomId);

    if (Object.keys(onlineUsers).length) {
        Object.keys(onlineUsers).forEach(function(key) {
            let value = onlineUsers[key];

            if (value.includes(roomUsers)) {
                isOnline = true;

                return false;
            }
        });
    }*/

    axios.post(removeTrailingSlash(appUrl) + '/api/user/chat/notification/message/group/send', {
        "room_id": roomId,
        "message": message,
        "from_user_id": senderId
    }).then(function(response) {
        /*console.log(response.data);
        console.log(response.status);
        console.log(response.statusText);
        console.log(response.headers);
        console.log(response.config);*/
    }).catch(() => {});
}
