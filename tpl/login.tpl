<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title>{{ $title }} | SBConsultants</title>
    </head>
    <body>
        <h2>Consultant Login</h2>
        <h4>Welcome back {{ $username }}!!</h4>
        <form method="post">
            <table style="border: 0">
                <tr>
                    <th>Username:</th>
                    <td><input type="text" id="username" name="username" style="width: 200px" value="{{ $username }}"/></td>
                </tr>
                <tr>
                    <th>Password:</th>
                    <td><input type="password" id="password" name="password" style="width: 200px"/></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="submit" value="Log in" style="width: 100px"/>
                    </td>
                </tr>
            </table>
        </form>
        <div id="warnings" style="border: 1px dashed black; padding: 10px">
            {{$rawHTML}}
        </div>
    </body>
</html>