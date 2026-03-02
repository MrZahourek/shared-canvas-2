// # Variables
const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");

let canvasConfig = null;
let scale, width, height;

// The Master Queue Array
const pixelQueue = [];
let refreshInterval;

// The Background Worker
function startRenderWorker() {
    function processQueue() {
        // If there are pixels waiting in line...
        if (pixelQueue.length > 0) {

            // Slice off up to 100 pixels from the front of the line
            // (You can change 100 to make the loading animation faster or slower)
            const chunk = pixelQueue.splice(0, 100);

            // Draw that chunk
            for (const pixel of chunk) {
                ctx.fillStyle = pixel.color;
                ctx.fillRect(pixel.x, pixel.y, 1, 1);
                console.info(pixel);
            }
        }

        // Tell the browser to run this function again on the next frame
        requestAnimationFrame(processQueue);
    }

    // Kick off the infinite loop
    requestAnimationFrame(processQueue);
}

// Start the worker immediately when the JS loads
startRenderWorker();

// # Functions

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
            console.log("handler ready");
            canvas.addEventListener("click", clickHandler);

            clearInterval(timerInterval);
            timerTime.innerText = "00:00:00";
            timerStatus.innerText = "READY";

            // Switch CSS classes
            timerWrap.classList.remove("loading");
            timerWrap.classList.add("ready");

            canvas.classList.remove("loading");
            canvas.classList.add("ready");

        } else {
            canvas.removeEventListener("click", clickHandler);

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
console.log("handler is on");
// get x and y on canvas
const canvasBox = canvas.getBoundingClientRect();
const x = event.clientX - canvasBox.left;
const y = event.clientY - canvasBox.top;

// get color
const color = document.getElementById("color_input").value;

// validate that user can edit
if (canvas.classList.contains("ready")) {
        let editResult = await sendEdit(x, y, color);

        if (editResult.success) {
            // reset timer and canvas state
            canvas.classList.remove("ready");
            canvas.classList.add("loading");

            // remove event listener
            canvas.removeEventListener("click", clickHandler);

            pixelQueue.push({x: x, y: y, color: color});
            console.warn("painted pixel");
        }
        else {
            console.error("failed to paint a pixel");
        }
    }
}

async function editHandler() {
    let newEdits = await getRecentEdits();

    if(newEdits !== "no edits") {
        localStorage.setItem("last_edit_id", newEdits.last_edit_id);
        pixelQueue.push(...newEdits.edits);
    }
}

// php functions
async function sendEdit(x, y, color) {
    return new Promise(async function(resolve, reject) {
        let url = "../app/services/CanvasService.php";
        let edit;
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({action: "new edit", x: x, y: y, color: color, wait:localStorage.getItem("canvas_wait_time"), canvasName: localStorage.getItem("canvas name")})
            });

            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            edit = await response.json();
        } catch (error) {
            console.error(error.message);
            reject("fetch error");
        }

        if (edit != null ) {
            resolve(edit);
        }
    });
}

async function getRecentEdits() {
    return new Promise(async function(resolve, reject) {
        let url = "../app/services/CanvasService.php";
        let recentEdits;
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({action: "get edits", canvasName: localStorage.getItem("canvas name"), last_edit_id: localStorage.getItem("last_edit_id")})
            });

            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            recentEdits = await response.json();
        } catch (error) {
            console.error(error.message);
            reject("fetch error");
        }

        if (recentEdits != null ) {
            resolve(recentEdits);
        }
        else {
            resolve("no edits");
        }
    });
}

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
        localStorage.setItem("last_edit_id", init.canvas_snapshot.last_edit_id);

        // Scale the canvas visually
        scaleCanvas();

        // 1. Combine the snapshot and the recent edits into one massive array
        const combinedEdits = init.canvas_snapshot.edits.concat(init.canvas_recent_edits);

        // 2. Filter out all the redundant/covered pixels instantly!
        const pureEdits = optimizeEdits(combinedEdits);

        // 3. Push the highly optimized array into our background queue worker
        pixelQueue.push(...pureEdits);

        refreshInterval = setInterval(editHandler, 1000);

        // Start active cooldown timer
        startCooldownTimer(init.user_last_edit_at, config.canvas_wait_time);
    } else {
        console.error("Failed to load init data");
    }

    canvas.style.visibility = "visible";
    console.log("page setup complete");
});


function optimizeEdits(allEdits) {
    const pixelMap = new Map();

    for (const edit of allEdits) {
        // Create a unique string key for this exact coordinate (e.g., "5,10")
        const key = `${edit.x},${edit.y}`;

        // Check if we already put a pixel at this spot in our Map
        const existingEdit = pixelMap.get(key);

        // If the spot is empty, OR if the new pixel is newer (bigger editID)...
        if (!existingEdit || edit.editID > existingEdit.editID) {
            // ...save this pixel to the map, overwriting the old one!
            pixelMap.set(key, edit);
        }
    }

    // Convert the Map values back into a clean, flat array of pixels
    return Array.from(pixelMap.values());
}