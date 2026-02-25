// # Variables

const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");

let cssTimerInterval = null;

const timerElement = {
    text: document.getElementById("timer_time").innerText,
    parent: document.getElementById("timer_wrap")
}

// # Functions

// info grab functions
async function getCanvasConfig() {
    // authenticate

    // get canvas config

    // load into storage
    for (const data of result.config) {
        localStorage.setItem(data.key, data.value);
    }

}

async function getUserData() {
    // authenticate

    let url = "../app/services/AuthService.php";
    let auth;

    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({requests: ["username", "last_edit_at"]})
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        auth = await response.json();
    } catch (error) {
        console.error(error.message);
    }

    // get user data
    url = "../app/models/User.php";
    let result;

    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({requests: ["username", "last_edit_at"]})
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        result = await response.json();
    } catch (error) {
        console.error(error.message);
    }

    // load into storage
    for (const data of result.user) {
        localStorage.setItem(data.key, data.value);
    }
}

async function getNewPixels() {}

async function sendNewPixel(x, y, color) {
    // authenticate

    // validate time

    // send pixels


}

// canvas functions
function draw(pixels) {}

function scale() {
    canvas.width = localStorage.getItem("width") * localStorage.getItem("scale");
    canvas.height = localStorage.getItem("height") * localStorage.getItem("scale");
}

async function clickHandler(event) {
    // get x and y
    const canvasBox = canvas.getBoundingClientRect();
    const x = event.clientX - canvasBox.left;
    const y = event.clientY - canvasBox.top;

    // get color
    const color = document.getElementById("color_input").value;

    // attempt to send
    let editSendResult = await sendNewPixel(x, y, color);

    // process
    if (editSendResult.success) {
        // trigger redraw
        draw( await getNewPixels());

        // reset timer
        canvas.classList.remove("ready");
        canvas.classList.add("loading");



        // put away event listener
        canvas.removeEventListener("click", clickHandler(event));
    }
}

// css edit functions
function timerCSS() {
    let startTime = parseInt(localStorage.getItem("last_edit")); // Time of last pixel
    let waitTime = parseInt(localStorage.getItem("wait_time"));  // e.g., 30000 for 30s
    let curTime = Date.now();

    let timePassed = curTime - startTime;
    let remaining = waitTime - timePassed;

    if (remaining <= 0) {
        // User can draw
        canvas.classList.add("ready");
        canvas.classList.remove("loading");
        timerElement.text = "Ready!";
        timerElement.parent.style.borderColor = "#4df3ff";
    } else {
        // User must wait
        canvas.classList.remove("ready");
        canvas.classList.add("loading");

        // Show remaining seconds
        let seconds = Math.ceil(remaining / 1000);
        document.getElementById("timer_time").innerText = seconds + " s";
    }
}

// # Event Listeners

// Canvas

// Window - load
window.addEventListener("load", (e) => {
    // hide canvas

    // get config file

    // scale

    // get snapshot file

    // draw

    // get edits

    // draw

    // get user data

    // activate css

    // results

    // show canvas
});

// Window - visibility
document.addEventListener("visibilitychange", async () => {
    if (document.visibilityState === "visible") {
        await getUserData();
        await getCanvasConfig();
        timerCSS(); // Immediate refresh when user comes back
    }
});