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
async function getCanvasConfig() {}

async function getUserData() {}

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
function timerCSS(){
    // check time
    let startTime = localStorage.getItem("last_edit");
    let curTime = Date.now();
    let waitTime = localStorage.getItem("wait_time");

    let timeLeft = curTime - startTime;

    if(timeLeft => waitTime) {
        canvas.classList.add("ready");
        canvas.classList.remove("loading");

    }
    else {
        timerElement.text = Math.floor( timeLeft / 1000 ) + " s";
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