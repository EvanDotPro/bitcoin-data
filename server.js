#!/usr/bin/env node
var io = require('socket.io').listen(9999);
var spawn = require('child_process').spawn;
var irc = require('irc');

var client = new irc.Client('chat.freenode.net', 'bitcoinrover', {
    channels: ['#bitcoin-market'],
});
client.addListener('message', function (from, to, message) {
    currency = message.substring(message.length - 3, message.length);
    console.log(message.substring(message.length - 3, message.length));
    if (from !== 'amphipod' || currency != 'USD') return;
    io.sockets.emit('btctrade', {trade: message});
});
var goxLag = function() {
    php = "ini_set('default_socket_timeout', 2); ini_set('user_agent', 'McLagger'); echo @json_decode(@file_get_contents('https://data.mtgox.com/api/1/generic/order/lag'))->return->lag_secs;";
    lag = spawn('php', ['-r', php]);
    lag.stdout.on('data', function (data) {
        lagTime = parseFloat(data, 10).toFixed(2);
        console.log('Mt.Gox Lag: ' + lagTime);
        io.sockets.emit('goxlag', {lag: parseFloat(data, 10).toFixed(2)});
    });
    lag.on('close', function (code) {
        setTimeout(function(){ goxLag(); }, 700);
    });
};
goxLag();
