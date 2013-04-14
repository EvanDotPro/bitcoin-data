<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Bitcoin Live - by Evan Coury</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="http://twitter.github.io/bootstrap/assets/css/bootstrap.css" rel="stylesheet">
    <link href="./fontawesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px;
      }
      .brand img { height: 25px; }
      #footer p { text-align: center; }
      td.up i { color: #00CC00; }
      td.down i { color: #CC0000; }
      table#last-price th { width: 20%; }
    </style>
    <link href="http://twitter.github.io/bootstrap/assets/css/bootstrap-responsive.css" rel="stylesheet">
    <!--[if lt IE 9]>
      <script src="http://twitter.github.io/bootstrap/assets/js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
    <a href="https://github.com/EvanDotPro/bitcoin-data"><img style="position: fixed; top: 0; right: 0; border: 0; z-index: 9999;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png" alt="Fork me on GitHub"></a>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#"><img src="http://cdn8.thecollegeinvestor.com/wp-content/uploads/2013/04/bitcoin.png"/> Bitcoin Live</a>
        </div>
      </div>
    </div>
    <div class="container">
        <h3>Latest Price (USD)</h3>
        <table class="table table-bordered" id="last-price">
            <tr>
                <th>Mt.Gox (lag: <span id="goxlag">...</span> secs)</th>
                <th>BTC-e</th>
                <th>bitfloor</th>
                <th>CampBx</th>
                <th>Bitstamp</th>
            </tr>
            <tr>
                <td id="MtGox-last">...</td>
                <td id="BTC-e-last">...</td>
                <td id="bitfloor-last">...</td>
                <td id="CampBx-last">...</td>
                <td id="Bitstamp-last">...</td>
            </tr>
        </table>

        <h3>Real-time USD trades:</h3>
        <table class="table" id="trades">
            <tr>
                <th>Exchange</th>
                <th>BTC Amount</th>
                <th>Price</th>
            </tr>
        </table>
    </div>

    <hr/>
    <div id="footer">
        <p>A product of the procrastination of <a href="http://evan.pro">Evan Coury</a>.</p>
        <p id="userCount"></p>
    </div>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script type="text/javascript" src="https://raw.github.com/jquery/jquery-color/eec69d070d22300916962730d705c3ca0dda0ac2/jquery.color.js"></script>
    <script type="text/javascript" src="http://<?=$_SERVER['HTTP_HOST'];?>:9999/socket.io/socket.io.js"></script>
    <script type="text/javascript">
        $(function() {
            var exchangeNames = {
                'mtgox': 'MtGox',
                'bitstamp': 'Bitstamp',
                'btce': 'BTC-e',
                'cbx': 'CampBx'
            };
            var mtgox = io.connect('https://socketio.mtgox.com/mtgox?Currency=USD');
            mtgox.on('message', function(data) {
                if (data.channel_name != 'trade.BTC') return;
                trade = {
                    exchange: 'MtGox',
                    amount: data.trade.amount.toFixed(4),
                    price: data.trade.price,
                    currency: data.trade.price_currency
                };
                handleTrade(trade);
            });
            var socket = io.connect('http://<?=$_SERVER['HTTP_HOST'];?>:9999');
            socket.on('userCount', function (data) {
                if (data.count == 1) {
                    $('#userCount').text('There is 1 user lurking right now... Just you.');
                } else {
                    $('#userCount').text('There are '+data.count+' users lurking right now.');
                }
            });
            socket.on('goxlag', function (data) {
                $('#goxlag').text(data.lag);
            });

            var handleTrade = function(trade) {
                trade.price = trade.price.toFixed(4);
                lastTradeBox = $('td#' + trade.exchange + '-last');
                lastTradePrice = lastTradeBox.data('last');
                up = trade.price >= lastTradePrice;
                color = up ? '#00CC00' : '#CC0000';
                icon  = up ? 'icon-arrow-up' : 'icon-arrow-down';
                lastTradeBox.data('last', trade.price);
                lastTradeBox.removeClass('up down');
                lastTradeBox.addClass(up ? 'up' : 'down');
                lastTradeBox.html(' <i class="icon-align-right icon-large ' + icon + '"></i> $' + trade.price);
                lastTradeBox.animate({backgroundColor: color}, {
                    complete: function() {
                        $(this).animate({backgroundColor: '#FFFFFF'});
                    }
                });
                tradeString = '<tr><td>' + trade.exchange + '</td><td>' + trade.amount + '</td><td>$' + trade.price + '</td></tr>';
                $('#trades tr:first').after(tradeString);
                if ($('#trades tr').length > 10) {
                    $('#trades tr:last').remove();
                }
            };
            socket.on('btctrade', function (data) {
                message = data.message.replace(/\s+/g, ' ').split(' ');
                switch (data.to)
                {
                case '#bitcoin-market':
                    if (data.from != 'amphipod') return;
                    offset = message[3].substring(0,1) == 'x' ? 1 : 0;
                    trade = {
                        exchange: message[2],
                        amount: message[3 + offset],
                        price: parseFloat(message[5 + offset]),
                        currency: message[6 + offset]
                    };
                    break;
                case '#bitcoin-watch':
                    return; // ignore for now
                    if (data.from != 'ljrbot') return;
                    if (message[0].substring(0,5) != 'trade') return;
                    trade = {
                        exchange: message[1].replace(':',''),
                        amount: message[2],
                        price: parseFloat(message[5]),
                        currency: message[6].substr(message[6].length - 3, message[6].length)
                    };
                    break;
                }
                if (trade.currency != 'USD') return;
                if (exchangeNames[trade.exchange] != undefined) trade.exchange = exchangeNames[trade.exchange];
                if (trade.exchange == 'MtGox') return; // getting mtgox data directly now
                handleTrade(trade);
            });
        });
    </script>
  </body>
</html>
