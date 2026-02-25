// # Variables

const loginForm = document.getElementById("LogUserForm");
const newUserForm = document.getElementById("NewUserForm");

// # Functions

async function sendData(type, username, password) {
    const url = "../app/services/AuthService.php";
    let result;

    try {
        const response = await fetch(url, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({authType: type, username: username, password: password})
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        result = await response.json();
    } catch (error) {
        console.error(error.message);
    }

    return(result);
}

// # Listeners

// login
loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    // variables
    const userInput = {
        username: document.getElementById("LogUsername").value,
        password: document.getElementById("LogPass").value
    }

    // go check if correct
    const authResult = await sendData("login", userInput.username, userInput.password);

    // show error
    if (!authResult.success) {
        const errorList = document.getElementById("LogErrors");
        for (const error of authResult.errorList) {
            let errorText = document.createElement("li");
            errorText.innerText = error;
            errorText.classList.add("error");
            errorList.appendChild(errorText);
        }
    }
    else {
        localStorage.setItem("canvas name", document.getElementById("canvasNameInput").value);
        // redirect
        window.location.href = "../public/canvas.php";
    }
});

// new user
newUserForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    // variables
    const userInput = {
        username: document.getElementById("NewUsername").value,
        password: document.getElementById("NewPass").value
    }

    // go check if correct
    const authResult = await sendData("newUser", userInput.username, userInput.password);

    // show error
    if (!authResult.success) {
        const errorList = document.getElementById("NewUserErrors");
        for (const error of authResult.errorList) {
            let errorText = document.createElement("li");
            errorText.innerText = error;
            errorText.classList.add("error");
            errorList.appendChild(errorText);
        }
    }
    else {
        localStorage.setItem("canvas_name", document.getElementById("canvasNameInput").value);
        // redirect
        window.location.href = "../public/canvas.php";
    }
});