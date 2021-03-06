<?
// Check querystring for form values.
$nick = isset($_GET['nick']) ? $_GET['nick'] : '';
$realname = isset($_GET['realname']) ? $_GET['realname'] : '';
$server = isset($_GET['server']) ? $_GET['server'] : '';
$port = isset($_GET['port']) ? $_GET['port'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Experimental IRC chat client</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <link rel="stylesheet" type="text/css" href="jqueryui/ui-lightness/jquery-ui-1.10.0.custom.css" />
	<style type="text/css">
		BODY {
			text-align: center;
		}

		.launcher {
			position: static;
			margin: 0 auto;
			width: 320px;
			text-align: left;
		}		

		.launcher input[type=text] {
			width: 140px;
		}
	</style>
    <script type="text/javascript" src="jquery-1.9.0.min.js"></script>
    <script type="text/javascript" src="config.js"></script>
    <script type="text/javascript">
        $(function () {
            var validateNick = function (nick) {
                return /^\S+$/.test(nick);
            };
            
            var validateServer = function (server) {
                return server.length <= 255 &&
                    /^[a-z][a-z0-9\.-]*$/i.test(server);
            };
            
            var validatePort = function (port) {
                return parseInt(port) > 0;
            };
            
            var validateLaunch = function () {
                var channel = $('#channelTextbox').val();
                if (validateNick($('#nickTextbox').val()) &&
                    validateServer($('#serverTextbox').val()) &&
                    validatePort($('#portTextbox').val())) {
                    if ($('#launchButton').attr('disabled')) {
                        $('#launchButton').removeAttr('disabled');
                    }
                }
                else {
                    if (!$('#launchButton').attr('disabled')) {
                        $('#launchButton').attr('disabled', 'disabled');
                    }
                }
            };
            
            // Normalize channel(s) with proper channel prefix.
            var normalizeChannel = function () {
                var channel = $('#channelTextbox').val();

                // Automatically prepend channels with '#'.
                var channels = channel.split(',');

                // Remove empty array elements.
                channels = $.grep(channels, function (c) { return !/^#?$/.test(c); });

                $.each(channels, function (idx, c) {
                    if (/^[^#&+!]/.test(c)) {
                        channels[idx] = '#' + c;
                    }
                });
                
                // Update form action URL.
                var channel = channels.join(',');
                $('#channelTextbox').val(channel);

                // Set form action with channel.
                $(document.launchForm).attr('action', 'client.php' + channel);
            };
            
            $('#nickTextbox')
                .keypress(function (e) {
                    // non-ASCII keypresses have charCode 0.
                    if (e.charCode === 0 || e.charCode == 13) return;

                    validateLaunch();
                    var newNick = $('#nickTextbox').val() + String.fromCharCode(e.charCode);
                    if (!validateNick(newNick)) return false;
                })
                .blur(function () {
                    var nick = $('#nickTextbox').val();
                    if (validateNick(nick)) {
                        $('#nickLabel').removeClass('ui-state-error');
                        if (window.console) console.log('valid nick');
                    }
                    else {
                        $('#nickLabel').addClass('ui-state-error');
                        if (window.console) console.log('Invalid nick!');
                    }
                });

            $('#serverTextbox')
                .keypress(function (e) {
                    // http://en.wikipedia.org/wiki/Hostname#Restrictions_on_valid_host_names
                    // non-ASCII keypresses have charCode 0.
                    if (e.charCode === 0 || e.charCode == 13) return;
                    
                    var newServer = $('#serverTextbox').val() + String.fromCharCode(e.charCode);
                    if (!validateServer(newServer)) return false;

                    validateLaunch();
                })
                .blur(function () {
                    var server = $('#serverTextbox').val();
                    if (validateServer(server)) {
                        $('#serverLabel').removeClass('ui-state-error');
                    }
                    else {
                        $('#serverLabel').addClass('ui-state-error');
                    }
                });

            $('#portTextbox')
                .keypress(function (e) {
                    // non-ASCII keypresses have charCode 0.
                    if (e.charCode === 0 || e.charCode == 13) return;

                    var newPort = $('#portTextbox').val() + String.fromCharCode(e.charCode);
                    if (!validatePort(newPort)) return false;

                    validateLaunch();
                })
                .blur(function () {
                    var port = $('#portTextbox').val();
                    if (validatePort(port)) {
                        $('#portLabel').removeClass('ui-state-error');
                    }
                    else {
                        $('#portLabel').addClass('ui-state-error');
                    }
                });
                
            $('#channelTextbox')
                .keypress(function (e) {
					// Simulate form submission on Enter.
					                    // non-ASCII keypresses have charCode 0.
                    if (e.charCode === 0)
                        return;
					else if (e.charCode === 13) {
					    validateLaunch();
					    normalizeChannel();
						document.forms.launchForm.submit();
						return;
					}
					else {
                        // Block spaces.
                        if (/^\s$/.test(String.fromCharCode(e.charCode))) return false;

                        validateLaunch();
                    }
                })
                .change(normalizeChannel);
                
            $(document.launchForm).submit(function () {
                normalizeChannel();
            });
            
            // Apply default values.
            <? if (empty($nick)): ?>
            $('#nickTextbox').val(chatmoreDefaults.nick);
            <? else: ?>
            $('#nickTextbox').val(<?=json_encode($nick)?>);
            <? endif; ?>
            <? if (empty($realname)): ?>
            $('#realnameTextbox').val(chatmoreDefaults.realname);
            <? else: ?>
            $('#realnameTextbox').val(<?=json_encode($realname)?>);
            <? endif; ?>
            <? if (empty($server)): ?>
            $('#serverTextbox').val(chatmoreDefaults.server);
            <? else: ?>
            $('#serverTextbox').val(<?=json_encode($server)?>);
            <? endif; ?>
            <? if (empty($port)): ?>
            $('#portTextbox').val(chatmoreDefaults.port);
            <? else: ?>
            $('#portTextbox').val(<?=json_encode($port)?>);
            <? endif; ?>

            if (document.location.hash === '')
                $('#channelTextbox').val(chatmoreDefaults.channel);
            else
                $('#channelTextbox').val(document.location.hash);

            $('#viewKey').val(Math.random().toString(36).substring(10));
            validateLaunch();
            
            $('#nickTextbox').focus();
        });
    </script>
</head>
<body>
    <form action="client.php" name="launchForm" method="GET">
        <input type="hidden" id="viewKey" name="viewKey" />
		<div class="chatmore">
	        <div class="launcher ui-dialog ui-widget ui-widget-content ui-corner-all">
				<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
					<span class="ui-dialog-title">Start IRC Chat</span>
				</div>
	            <div class="ui-widget-content ui-dialog-content">
	            <table>
	                <col width="*" />
	                <col width="148" />
	                <tr><th id="nickLabel" class="left">Nickname:</th><td><input type="text" id="nickTextbox" name="nick"/></td></tr>
	                <tr><th class="left">Real name:</th><td><input type="text" id="realnameTextbox" name="realname"/></td></tr>
	                <tr><th id="serverLabel" class="left">Server:</th><td><input type="text" id="serverTextbox" name="server"/></td></tr>
	                <tr><th id="portLabel" class="left">Port:</th><td><input type="text" id="portTextbox" name="port"/></td></tr>
	                <tr><th id="channelLabel" class="left wrap">Channel:</th><td><input type="text" id="channelTextbox" /></td></tr>
					<tr><td colspan="2"><span class="helpText">Can provide multiple channels separated by commas.</span></td></tr>
	                <tr><td colspan="2" style="text-align:right"><input type="submit" id="launchButton" disabled="disabled" value="Launch IRC Session" /></td></tr>
	            </table>
	            </div>
	        </div>
		</div>
        
    </form>
</body>
</html>
