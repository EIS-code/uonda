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

/*io.use((socket, next)=>{

})*/

io.on('connection', function (socket) {
    /*var redisClient = redis.createClient();
    redisClient.subscribe('messageSend');*/

    socket.on("messageSend", function(data) {
        con.getConnection(function(err, connection) {
            if (err) throw err;

            let createdAt = mysqlDate(new Date());
            let sqlQuery  = "INSERT INTO `chats` (message, user_id, send_by, created_at, updated_at) VALUES ('"+ data.message +"','"+ data.user_id+"','"+data.send_by+"','"+createdAt+"','"+createdAt+"')";

            connection.query(sqlQuery, function (err, resultInsertChat, fields) {
                if (err) throw err;

                let getChat = "SELECT id, message, user_id FROM `chats` as c WHERE c.`id` = '" + resultInsertChat.insertId + "' LIMIT 1";

                connection.query(getChat, function (err, resultChat, fields) {
                    if (err) throw err;

                    let getUser = "SELECT * FROM `users` WHERE `id` = '" + resultChat[0].user_id + "' LIMIT 1";

                    connection.query(getUser, function (err, resultUser, fields) {
                        if (err) throw err;

                        io.sockets.emit('messageRecieve', {id: resultChat[0].id, message: resultChat[0].message, user: resultUser[0]});
                    });
                });
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
