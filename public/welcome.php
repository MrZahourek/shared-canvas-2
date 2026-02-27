<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Shared Canvas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Share+Tech+Mono&family=VT323&family=Workbench:BLED,SCAN@30,-2&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/screen.css">
    <link rel="stylesheet" href="assets/css/window.css">
    <link rel="stylesheet" href="assets/css/welcome.css?v=1.1">
</head>
<body>

<div class="screen">
    <img src="assets/images/bezel.png" alt="" class="bezel">
    <div class="scan-bar">
        <div class="scan"></div>
    </div>

    <div class="content welcome_layout">
        <h1 class="glitch_title">SHARED_CANVAS // PORTAL</h1>

        <div class="container">
            <div class="window">
                <div class="window_header">
                    <span class="window_name icon-user">Login.exe</span>
                    <div class="window_controls">
                        <div class="window_top_btn">_</div>
                        <div class="window_top_btn">X</div>
                    </div>
                </div>
                <div class="window_content login_content">
                    <form id="LogUserForm">
                        <input id="LogUsername" type="text" placeholder="Username" required>
                        <input id="LogPass" type="password" placeholder="Password" required>
                        <input id="LogSubmit" type="submit" value="Log In" class="window_btn">
                    </form>
                    <ul id="LogErrors"></ul>
                </div>
            </div>

            <div class="window">
                <div class="window_header">
                    <span class="window_name icon-tools">Create_Account.exe</span>
                    <div class="window_controls">
                        <div class="window_top_btn">_</div>
                        <div class="window_top_btn">X</div>
                    </div>
                </div>
                <div class="window_content login_content">
                    <form id="NewUserForm">
                        <input id="NewUsername" type="text" placeholder="Choose a Username" required>
                        <input id="NewPass" type="password" placeholder="Password" required>
                        <input id="NewPassCheck" type="password" placeholder="Confirm Password" required>
                        <input id="NewSubmit" type="submit" value="Sign Up" class="window_btn">
                    </form>
                    <ul id="NewUserErrors"></ul>
                </div>
            </div>
        </div>

        <div class="window canvas-setup">
            <div class="window_header">
                <span class="window_name icon-canvas">Target_Server.ini</span>
            </div>
            <div class="window_content login_content text-center">
                <p>CONNECTION TARGET:</p>
                <input type="text" placeholder="global" id="canvasNameInput">
            </div>
        </div>
    </div>
</div>

<script src="assets/js/userEntry.js?v=1.3"></script>
</body>
</html>