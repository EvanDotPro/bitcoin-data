#!/usr/bin/env node
var io = require('socket.io').listen(9999);
var spawn = require('child_process').spawn;
var goxLag = function() {
    php = "ini_set('default_socket_timeout', 2); ini_set('user_agent', 'McLagger'); echo @json_decode(@file_get_contents('https://data.mtgox.com/api/1/generic/order/lag'))->return->lag_secs;";
    lag = spawn('php', ['-r', php]);
    lag.stdout.on('data', function (data) {
        lagTime = parseFloat(data, 10).toFixed(2);
        if (!lagTime) {
            console.log('Unable to retreive Mt.Gox lag...');
        }
        console.log('Mt.Gox Lag: ' + data);
        io.sockets.emit('goxlag', {lag: parseFloat(data, 10).toFixed(2)});
    });
    lag.on('close', function (code) {
        setTimeout(function(){ goxLag(); }, 700);
    });
};
goxLag();
