// # Variables

const loginForm = document.getElementById("LogUserForm");
const newUserForm = document.getElementById("NewUserForm");

// # Functions
// Helper function to render errors to the UI
function displayErrors(listId, errors) {
    const errorList = document.getElementById(listId);
    errorList.innerHTML = ""; // Clear old errors

    // Create a new <li> for each error the server sends back
    errors.forEach(err => {
        const li = document.createElement("li");
        li.classList.add("error");
        li.innerText = err;
        errorList.appendChild(li);
    });
}

// # Listeners

// --- LOGIN ---
loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // 1. get input variables
    const userInput = {
        username: document.getElementById("LogUsername").value,
        password: document.getElementById("LogPass").value
    }

    const url = "../app/services/AuthService.php";

    try {
        // 2. Await the fetch directly
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            // FIX: Changed authType to "login"
            body: JSON.stringify({username: userInput.username, password: userInput.password, authType: "login"})
        });

        if (!response.ok) throw new Error(`Response status: ${response.status}`);

        const auth = await response.json();

        // 3. handle data
        if (!auth.success) {
            // Show errors on the screen instead of just the console!
            displayErrors("LogErrors", auth.errorList);
        } else {
            // auth success ... redirect
            let targetCanvas = document.getElementById("canvasNameInput").value || "global";
            localStorage.setItem("canvas name", targetCanvas);
            window.location.href = "../public/canvas.php";
        }

    } catch (error) {
        console.error(error.message);
        displayErrors("LogErrors", ["Failed to connect to the server."]);
    }
});


// --- NEW USER ---
newUserForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // 1. get input variables
    const userInput = {
        username: document.getElementById("NewUsername").value,
        password: document.getElementById("NewPass").value,
        passwordCheck: document.getElementById("NewPassCheck").value
    };

    // 2. Check if the passwords match each other
    if (userInput.password !== userInput.passwordCheck) {
        // FIX: Actually display the error and stop the function
        displayErrors("NewUserErrors", ["Passwords do not match!"]);
        return;
    }

    const url = "../app/services/AuthService.php";

    try {
        // 3. Await the fetch directly
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({username: userInput.username, password: userInput.password, authType: "newUser"})
        });

        if (!response.ok) throw new Error(`Response status: ${response.status}`);

        const auth = await response.json();

        // 4. handle data
        if (!auth.success) {
            displayErrors("NewUserErrors", auth.errorList);
        } else {
            // auth success ... redirect
            let targetCanvas = document.getElementById("canvasNameInput").value || "global";
            localStorage.setItem("canvas name", targetCanvas);
            window.location.href = "../public/canvas.php";
        }

    } catch (error) {
        console.error(error.message);
        displayErrors("NewUserErrors", ["Failed to connect to the server."]);
    }
});