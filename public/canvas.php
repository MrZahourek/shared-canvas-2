<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canvas | Shared Canvas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=Share+Tech+Mono&family=VT323&family=Workbench:BLED,SCAN@30,-2&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/window.css?v=2">
    <link rel="stylesheet" href="assets/css/grid.css?v=6">
    <link rel="stylesheet" href="assets/css/window_content.css?v=4">
    <link rel="stylesheet" href="assets/css/music.css?v=4">
    <link rel="stylesheet" href="assets/css/screen.css?v=3">
    <link rel="stylesheet" href="assets/css/canvas.css?v=3">
    <link rel="stylesheet" href="assets/css/mobile.css">
</head>
<body>

<div class="screen">
    <img src="assets/images/bezel.png" alt="" class="bezel">
    <div class="scan-bar">
        <div class="scan"></div>
    </div>

    <div id="grid_wrap" class="content">

        <div class="window">
            <div class="window_header">
                <span class="window_name icon-user">USERNAME</span>
                <div class="window_controls">
                    <div class="window_top_btn">_</div>
                    <div class="window_top_btn">☐</div>
                    <div class="window_top_btn">X</div>
                </div>
            </div>
            <div class="window_content canvas_buttons">
                <div class="canvas_buttons_user_wrap">
                    <div class="profile_img_container">
                        <img src="assets/images/user.jpg" alt="" class="canvas_buttons_userProfile">
                        <div class="profile_glitch_overlay"></div>
                    </div>
                    <span class="canvas_buttons_username">USER_994</span>
<!--                    <span class="profile_status">STATUS: CONNECTED</span>-->
                </div>
            </div>
        </div>


    <div class="window">
        <div class="window_header">
            <span class="window_name icon-canvas">canvas_area</span>
            <div class="window_controls">
                <div class="window_top_btn">_</div>
                <div class="window_top_btn">☐</div>
                <div class="window_top_btn">X</div>
            </div>
        </div>
        <div class="window_content canvas_buttons">
            <div class="canvas_area_btn_wrap">
                <!-- ADD THIS display above the buttons -->
                <div style="
            font-family: 'VT323', monospace;
            font-size: 1rem;
            color: #888;
            margin-bottom: 4px;
            letter-spacing: 1px;
        ">CONNECTED TO:</div>
                <div id="current_canvas_display" style="
            font-family: 'VT323', monospace;
            font-size: 1.4rem;
            color: #00ff00;
            background: #050505;
            box-shadow: inset -1px -1px #fff, inset 1px 1px #0a0a0a, inset -2px -2px #dfdfdf, inset 2px 2px #808080;
            padding: 3px 10px;
            text-shadow: 0 0 5px rgba(0,255,0,0.4);
            letter-spacing: 2px;
            margin-bottom: 10px;
            width: 100%;
            text-align: center;
        ">global</div>

                <button class="window_btn">New Canvas</button>
                <button class="window_btn">Random Connect</button>
                <button class="window_btn">Custom Connect</button>
            </div>
        </div>
    </div>

    <div class="window">
        <div class="window_header">
            <span class="window_name icon-canvas">canvas_area</span>
            <div class="window_controls">
                <div class="window_top_btn">_</div>
                <div class="window_top_btn">☐</div>
                <div class="window_top_btn">X</div>
            </div>
        </div>
        <div class="window_content canvas_area">

            <div id="canvas_wrapper" style="position: relative; display: inline-block; border: groove 3px lightskyblue;">
                <canvas id="canvas" class="loading" style="display: block;"></canvas>
                <div id="grid_overlay" style="display: none; pointer-events: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>
            </div>

        </div>
    </div>

    <div class="window music">
        <div class="window_header">
            <span class="window_name icon-music">Media Player</span>
            <div class="window_controls">
                <div class="window_top_btn">_</div>
                <div class="window_top_btn">☐</div>
                <div class="window_top_btn">X</div>
            </div>
        </div>
        <div class="window_content music">
            <div class="music_main">
<!--                <img id="music_album_art" src="assets/images/music_1.jpg" alt="Album">-->

                <div class="music_main_text">
                    <span class="music_display_time">00:00</span>
                    <span class="music_display_visualiser">■ ■ ■ □ □ □</span>
                    <span class="music_display_track_info" id="track_info">Loading...</span>
                </div>
            </div>
            <div class="music_controls">
                <div class="music_controls_info">
                    <span>128kbps</span>
                </div>

                <div class="music_volume_wrap">
                    <span>VOL</span>
                    <input type="range" class="music_volume" min="0" max="100" value="50">
                </div>

                <div class="music_controls_buttons">
                    <button class="music_btn mb-prev"></button>
                    <button class="music_btn mb-play"></button>
                    <button class="music_btn mb-pause"></button>
                    <button class="music_btn mb-next"></button>
                </div>
            </div>
        </div>
    </div>

        <div class="window">
            <div class="window_header">
                <span class="window_name icon-tools">drawing_tools</span>
                <div class="window_controls">
                    <div class="window_top_btn">_</div>
                    <div class="window_top_btn">☐</div>
                    <div class="window_top_btn">X</div>
                </div>
            </div>
            <div class="window_content drawing_tools">
                <div class="draw_timer" id="timer_wrap">
                    <span> COOLDOWN: </span>
                    <span id="timer_time" class="loading"> 00:00:00 </span>
                    <span> // </span>
                    <span id="timer_status"> READY </span>
                </div>
                <div class="color_picks">
                    <button id="toggle_grid_btn" class="window_btn" style="font-size: 1.1rem; padding: 4px 16px; margin-right: 10px; flex: 1;">SHOW GRID</button>
                    <input type="color" id="color_input" title="Pick color">
                </div>
            </div>
        </div>
    </div>

<script src="assets/js/canvasManager.js?v=7"></script>
<script src="assets/js/musicPlayer.js"></script>
</body>
</html>