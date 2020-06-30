const app = require('express')();
const server = require('http').Server(app);
const io = require('socket.io')(server);
const request = require('request');
server.listen(3000);
console.log('server started');
setInterval(function () {
    request.get('/api/score/clients',
        function (error, response, body) {
            console.log(body)
        }
    );
    request.get('/api/score/drivers',
        function (error, response, body) {
            console.log(body)
        }
    )
},86400000);
//1 кун ===  86400000 миллсек


// 192.168.0.113
//89.219.32.6

io.on('connection', function (socket) {
    console.log('connect');
    socket.on('send',function (front) {
        let data = JSON.parse(front);
        console.log('send data ',data);
        request.post('/api/driver/group/send',
            {
                json:{
                    token:data.token,
                    group_id:data.group_id,
                    text:data.text
                }
            },
            function (error, response, body) {
                io.emit('group_' + data.group_id,body);
                console.log('send body',body)

            }
        )
    });

    socket.on('driver_send',function (front) {
        let data = JSON.parse(front);
        console.log('send data ',data);
        request.post('/api/driver/chat/send',
            {
                json:{
                    token:data.token,
                    client_id:data.client_id,
                    text:data.text
                }
            },
            function (error, response, body) {
                io.emit('chat_' + body.result.chat_id,body);
                console.log('emit to ','chat_' + body.result.chat_id)
            }
        );

        request.post('/api/client/chats',
            {
                json:{
                    id:data.client_id,
                }
            },
            function (error, response, body) {
                if  (body.statusCode === 200){
                    io.emit('client_chat_list_' + data.client_id,body);

                    console.log('emit to ','client_chat_list_' + data.client_id)
                }
            }
        );
        request.post('/api/driver/chats',
            {
                json:{
                    token:data.token,
                }
            },
            function (error, response, body) {
                if  (body.statusCode === 200){
                    io.emit('driver_chat_list_' + body.driver.id,body);

                    console.log('emit to ','driver_chat_list_' + body.driver.id)
                }
            }
        )
    });
    socket.on('client_send',function (front) {
        let data = JSON.parse(front);
        console.log('send data ',data);
        request.post('/api/client/chat/send',
            {
                json:{
                    token:data.token,
                    driver_id:data.driver_id,
                    text:data.text
                }
            },
            function (error, response, body) {
                io.emit('chat_' + body.result.chat_id,body);
                console.log('emit to ','chat_' + body.result.chat_id)
            }
        );

        request.post('/api/driver/chats',
            {
                json:{
                    id:data.driver_id,
                }
            },
            function (error, response, body) {
                if  (body.statusCode === 200){
                    io.emit('driver_chat_list_' + data.driver_id,body);

                    console.log('emit to ','driver_chat_list_' +data.driver_id)
                }
            }
        )

        request.post('/api/client/chats',
            {
                json:{
                    token:data.token,
                }
            },
            function (error, response, body) {
                if  (body.statusCode === 200){
                    io.emit('client_chat_list_' + body.client.id,body);

                    console.log('emit to ','client_chat_list_' +body.client.id)
                }
            }
        );
    });




    // socket.on('messages',function (front) {
    //     let data = JSON.parse(front);
    //     console.log('mess',data);
    //     request.post('/api/driver/group/gets',
    //         {
    //             json:{
    //                 group_id:data.group_id,
    //             }
    //         },
    //         function (error, response, body) {
    //             io.emit('group_' + data.group_id,body);
    //         }
    //     )
    // });
});



