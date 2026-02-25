<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome</title>
</head>
<body>
<h2>Login</h2>
<form id="LogUserForm">
    <input id="LogUsername" type="text">
    <input id="LogPass" type="password">
    <input id="LogSubmit" type="submit">
</form>
<ul id="LogErrors"></ul>

<h2>New account</h2>
<form id="NewUserForm">
    <input id="NewUsername" type="text">
    <input id="NewPass" type="password">
    <input id="NewPassCheck" type="password">
    <input id="NewSubmit" type="submit">
</form>

<ul id="NewUserErrors"></ul>

<h2>Canvas name</h2>
<input type="text" placeholder="global" id="canvasNameInput">

<script src="assets/js/userEntry.js"></script>
</body>
</html>