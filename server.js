#!/usr/bin/env node
var io = require('socket.io').listen(9999);
var spawn = require('child_process').spawn;
var irc = require('irc');
var client = new irc.Client('chat.freenode.net', 'bitcoinrover2', {
    channels: [
    '#bitcoin-market',
    '#bitcoin-watch'
    ],
    stripColors: true
});
var connectedCount = 0;

var replayTrades = 25;
var latestTrades = [];

client.addListener('message', function (from, to, message) {
    trade = {'from': from, 'to': to, 'message': message};
    io.sockets.emit('btctrade', trade);
    latestTrades.unshift(trade);
    if (latestTrades.length > replayTrades) {
        latestTrades.pop();
    }
});

io.on("connection", function(client){
    connectedCount += 1;
    io.sockets.emit('userCount', {count: connectedCount});
    for (i=0; i<latestTrades.length; i++) {
        io.sockets.emit('btctrade', latestTrades[i]);
    }
    client.on("disconnect", function(){
        connectedCount -= 1;
        io.sockets.emit('userCount', {count: connectedCount});
    });
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
