$(function() {
  var socket = io.connect('http://evan.pro:9999');
    socket.on('goxlag', function (data) {
        $('#goxlag').text(data.lag);
    });
});
