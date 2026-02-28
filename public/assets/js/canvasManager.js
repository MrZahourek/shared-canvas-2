// # Variables
const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");

let canvasConfig = null;
let scale, width, height;

// # Functions

// canvas functions
function draw(pixelData) {
    for (const pixel of pixelData) {
        ctx.fillStyle = pixel.color;
        ctx.fillRect(pixel.x, pixel.y, 1, 1);
    }
}

function scaleCanvas() {
    scale = localStorage.getItem("canvas_scale");
    width = localStorage.getItem("canvas_width");
    height = localStorage.getItem("canvas_height");

    // logical resolution
    canvas.width = width;
    canvas.height = height;

    // style resolution
    canvas.style.width = width * scale + "px";
    canvas.style.height = height * scale + "px";

    // 1. Tells the CSS renderer to use blocky nearest-neighbor scaling
    canvas.style.imageRendering = "pixelated";
    // 2. Tells the Canvas 2D context to stop blurring shapes when drawing
    ctx.imageSmoothingEnabled = false;
}

// css functions
function startCooldownTimer(lastEditMs, waitTimeMs) {
    const timerWrap = document.getElementById("timer_wrap");
    const timerTime = document.getElementById("timer_time");
    const timerStatus = document.getElementById("timer_status");

    // If it's a brand new user (null), set their last edit to 0 so they are instantly ready
    lastEditMs = lastEditMs ? lastEditMs : 0;

    const timerInterval = setInterval(() => {
        const now = Date.now();
        const elapsedMs = now - lastEditMs;
        const remainingMs = waitTimeMs - elapsedMs;

        if (remainingMs <= 0) {
            // --- TIMER IS DONE ---
            clearInterval(timerInterval);
            timerTime.innerText = "00:00:00";
            timerStatus.innerText = "READY";

            // Switch CSS classes
            timerWrap.classList.remove("loading");
            timerWrap.classList.add("ready");

            canvas.classList.remove("loading");
            canvas.classList.add("ready");

        } else {
            // --- TIMER IS RUNNING ---
            const mins = Math.floor(remainingMs / 60000);
            const secs = Math.floor((remainingMs % 60000) / 1000);
            const ms = Math.floor((remainingMs % 1000) / 10);

            timerTime.innerText =
                (mins < 10 ? "0" : "") + mins + ":" +
                (secs < 10 ? "0" : "") + secs + ":" +
                (ms < 10 ? "0" : "") + ms;

            timerStatus.innerText = "LOADING...";

            // Calculate the percentage
            const progressPct = (elapsedMs / waitTimeMs) * 100;

            // Update the CSS Variable dynamically!
            timerWrap.style.setProperty('--progress', progressPct + '%');

            // Switch CSS classes
            timerWrap.classList.add("loading");
            timerWrap.classList.remove("ready");

            canvas.classList.add("loading");
            canvas.classList.remove("ready");
        }
    }, 50);
}

// handlers
async function clickHandler(event) {
    // get x and y on canvas
    const canvasBox = canvas.getBoundingClientRect();
    const x = event.clientX - canvasBox.left;
    const y = event.clientY - canvasBox.top;

    // get color
    const color = document.getElementById("color_input").value;

    // validate that user can edit
    if (canvas.classList.contains("ready")) {
        // 1. also check with database
        let userData = await getUserData();

        if ((Date.now() - userData.last_edit_at) >= parseInt(localStorage.getItem("canvas_wait_time"))) {
            let editResult = await sendEdit(x, y, color);

            if (editResult.success) {
                // reset timer and canvas state
                canvas.classList.remove("ready");
                canvas.classList.add("loading");

                // remove event listener
                canvas.removeEventListener("click", clickHandler);
            }
        }
    }
}

// php functions

async function getUserData() {}

async function sendEdit(x, y, color) {}

async function getRecentEdits() {}

async function getInitData() {
    return new Promise(async function(resolve, reject) {
        let url = "../app/services/CanvasService.php";
        let initData;
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({action: "init", canvasName: localStorage.getItem("canvas name")})
            });

            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            initData = await response.json();
        } catch (error) {
            console.error(error.message);
            reject("fetch error");
        }

        if (initData != null ) {
            resolve(initData);
        }
    });
}

// # Listeners

// Window - load
window.addEventListener("load", async (event) => {
    console.log("started page setup");
    canvas.style.visibility = "hidden";

    let init = await getInitData();

    if (init && init.success) {
        // -> load the user and the last edit at and place them
        document.querySelector(".canvas_buttons_username").innerText = init.username;

        // FIX: Match the exact key sent by your PHP (user_last_edit_at)
        localStorage.setItem("last_edit_at", init.user_last_edit_at);

        // Save config to local storage
        let config = init.canvas_config;
        localStorage.setItem("canvas_width", config.canvas_width);
        localStorage.setItem("canvas_height", config.canvas_height);
        localStorage.setItem("canvas_scale", config.canvas_scale);
        localStorage.setItem("canvas_wait_time", config.canvas_wait_time);

        // Scale the canvas visually
        scaleCanvas();
        ctx.fillStyle = "#00ff00";
        for (let x = 0; x < 10; x++) {
            ctx.fillRect(5 + (2 * x) , 5, 1, 1);
        }

        // -> Start active cooldown timer
        startCooldownTimer(init.user_last_edit_at, config.canvas_wait_time);
    } else {
        console.error("Failed to load init data");
    }

    canvas.style.visibility = "visible";
    console.log("page setup complete");
});