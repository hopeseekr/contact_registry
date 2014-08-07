<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title>{{ $title }} | SBConsultants</title>
        <link rel="stylesheet" type="text/css" href="main.css"/>
    </head>
    <body>
        <h2>Consultant Login - Blitz Template</h2>
    {{ BEGIN debug }}
        <pre>{{ $print_r }}</pre>
    {{ END }}
    {{ BEGIN loginFailed }}
        <div id="errors">
            <h4>Login failed: {{ $err_msg }}</h4>
        </div>
    {{ END }}
        <form method="post">
            <table id="login">
                <tr>
                    <th>Username:</th>
                    <td><input type="text" id="username" name="username" value="{{ $username }}"/></td>
                </tr>
                <tr>
                    <th>Password:</th>
                    <td><input type="password" id="password" name="password"/></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="submit" value="Log in" class="submit"/>
                    </td>
                </tr>
            </table>
        </form>
    {{ BEGIN rawHTML }}
        <div id="warnings">
            {{$rawHTML}}
        </div>
    {{ END }}
    </body>
</html>