<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Bitcoin Live - by Evan Coury</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="http://twitter.github.io/bootstrap/assets/css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px;
      }
      .brand img { height: 25px; }
      #trades { height: 250px; overflow: scroll; border: 1px solid #000; }

    </style>
    <link href="http://twitter.github.io/bootstrap/assets/css/bootstrap-responsive.css" rel="stylesheet">
    <!--[if lt IE 9]>
      <script src="http://twitter.github.io/bootstrap/assets/js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#"><img src="http://cdn8.thecollegeinvestor.com/wp-content/uploads/2013/04/bitcoin.png"/> Bitcoin Live</a>
        </div>
      </div>
    </div>
    <div class="container">
        <h1>Mt.Gox Lag: <span id="goxlag">...</span></h1>
        <p> Real-time trades:</p>
        <pre id="trades"></pre>
    </div>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="http://<?=$_SERVER['HTTP_HOST'];?>:9999/socket.io/socket.io.js"></script>
    <script type="text/javascript">
        $(function() {
            var socket = io.connect('http://<?=$_SERVER['HTTP_HOST'];?>:9999');
            socket.on('goxlag', function (data) {
                $('#goxlag').text(data.lag);
            });
            socket.on('btctrade', function (data) {
                $('#trades').prepend(data.trade + "\n");
            });
        });
    </script>
  </body>
</html>
