// # Variables

const loginForm = document.getElementById("LogUserForm");
const newUserForm = document.getElementById("NewUserForm");

// # Listeners

// login
loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    // 1. get input variables
    const userInput = {
        username: document.getElementById("LogUsername").value,
        password: document.getElementById("LogPass").value
    }

    // 2. use AuthService.php to try login
    let url = "../app/services/AuthService.php";
    let authResult = async () => {
        let auth;
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({username: userInput.username, password: userInput.password, authType: "newUser"})
            });

            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            auth = await response.json();
        } catch (error) {
            console.error(error.message);
        }

        return (auth);
    };

    // 3. handle data
    if (!authResult().success) {
        // auth failed ... show errors
        console.error(authResult().errorList);
    }
    else {
        // auth success ... redirect
        let targetCanvas = document.getElementById("canvasNameInput").value ?? "global";
        localStorage.setItem("canvas name", targetCanvas);

        // redirect
        window.location.href = "../public/canvas.php";
    }
});

// new user
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
        // -> passwords dont match ... display error
    }
    else {
        // -> password match ...  use AuthService.php to try make account
        let url = "../app/services/AuthService.php";
        let authResult = async () => {
            let auth;
            try {
                const response = await fetch(url, {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({username: userInput.username, password: userInput.password, authType: "newUser"})
                });

                if (!response.ok) {
                    throw new Error(`Response status: ${response.status}`);
                }

                auth = await response.json();
            } catch (error) {
                console.error(error.message);
            }

            return (auth);
        };

        // 3. handle data
        if (!authResult().success) {
            // auth failed ... show errors
            console.error(authResult().errorList);
        }
        else {
            // auth success ... redirect
            let targetCanvas = document.getElementById("canvasNameInput").value ?? "global";
            localStorage.setItem("canvas name", targetCanvas);

            // redirect
            window.location.href = "../public/canvas.php";
        }
    }
});