// # Variables
const canvas = document.getElementById("canvas");
const ctx = canvas.getContext("2d");

// # Functions

// canvas functions
function draw(pixelData) {
    for (const pixel of pixelData) {
        ctx.fillStyle = pixel.color;
        ctx.fillRect(pixel.x, pixel.y, 1, 1);
    }
}

function scale() {
    canvas.width = localStorage.getItem("canvas_width") * localStorage.getItem("canvas_scale");
    canvas.height = localStorage.getItem("canvas_height") * localStorage.getItem("canvas_scale");
}

// php functions

async function getUserData() {
    let url = "../app/models/User.php";
    let user;
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({})
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        user = await response.json();


    } catch (error) {
        console.error(error.message);
    }

    return user;
}

async function getCanvasConfig() {
    // find canvas config file
    let url = "../app/models/Canvas.php";
    let canvasConfig;
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({canvasName: localStorage.getItem("canvas_name")})
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        canvasConfig = await response.json();


    } catch (error) {
        console.error(error.message);
    }

    // load it into local storage
    if (canvasConfig.success) {
        for (const [key, value] of Object.entries(canvasConfig)) {
            if (key !== "success") {
                localStorage.setItem(key, value);
            }
        }
    }
}

async function getSnapshot() {
    let url = "../app/services/CanvasService.php";
    let snapshotData;
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({canvasName: localStorage.getItem("canvas_name"), action: "get snapshot"})
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        snapshotData = await response.json();
    } catch (error) {
        console.error(error.message);
    }

    if (snapshotData.edits !== []) {
        localStorage.setItem("lastID", snapshotData.lastID);
        return snapshotData.edits;
    }
}

async function getEdits() {
    let url = "../app/services/CanvasService.php";
    let pixelData;
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({canvasName: localStorage.getItem("canvas_name"), lastID: localStorage.getItem("lastID"), action: "get edits"})
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        pixelData = await response.json();
    } catch (error) {
        console.error(error.message);
    }

    if (pixelData.edits !== []) {
        localStorage.setItem("lastID", pixelData.lastID);
        return pixelData.edits;
    }
}

async function sendEdit(x, y, color) {
    // authenticate
    const auth = await authenticate();

    if (!auth.success) {
        console.error("auth fail");
        return 0;
    }

    let url = "../app/services/CanvasService.php";
    let editResult;
    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({canvasName: localStorage.getItem("canvas_name"), x: x, y: y, color: color, action: "new edit"})
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        editResult = await response.json();
    } catch (error) {
        console.error(error.message);
    }

    return editResult;
}

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

        if ((Date.now() - userData.last_edit_at) >= praseInt(localStorage.getItem("canvas_wait_time")) ) {
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
    // 1. hide canvas so the change isnt as sharp
    canvas.style.visibility = "hidden";

    // 2. get the init files - canvas snap, canvas edits, canvas config and user info
    let init = await getInitData();
    await init;
    // 3. handle output
    // -> load the user and the last edit at and place them
    document.querySelector(".canvas_buttons_username").innerText = init.username;
    localStorage.setItem("last_edit_at", init.last_edit_at);


    // -> last user edit & canvas wait period ... active cooldown

    // -> show username

    // 10. show canvas
    canvas.style.visibility = "visible";
    console.log("page setup complete");
})