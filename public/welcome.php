<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome | Shared Canvas</title>

    <link rel="stylesheet" href="assets/css/welcome.css?v=1.0">
</head>
<body>

<h1>Shared Canvas</h1>

<div class="container">
    <div class="card">
        <h2>Login</h2>
        <form id="LogUserForm">
            <input id="LogUsername" type="text" placeholder="Username" required>
            <input id="LogPass" type="password" placeholder="Password" required>
            <input id="LogSubmit" type="submit" value="Log In">
        </form>
        <ul id="LogErrors"></ul>
    </div>

    <div class="card">
        <h2>Create Account</h2>
        <form id="NewUserForm">
            <input id="NewUsername" type="text" placeholder="Choose a Username" required>
            <input id="NewPass" type="password" placeholder="Password" required>
            <input id="NewPassCheck" type="password" placeholder="Confirm Password" required>
            <input id="NewSubmit" type="submit" value="Sign Up">
        </form>
        <ul id="NewUserErrors"></ul>
    </div>
</div>

<div class="canvas-setup">
    <h2>Canvas Setup</h2>
    <p>Which canvas do you want to join?</p>
    <input type="text" placeholder="global" id="canvasNameInput">
</div>

<script src="assets/js/userEntry.js"></script>

<script>
    // # Variables
    const loginForm = document.getElementById("LogUserForm");

    // # Functions
    async function userDataCheck(password, username, request) {
        let result = { success: false, error: [] };

        const url = "../app/models/User.php";
        try {
            /*
             * possible request:
             * check login
             * check name
            */

            const response = await fetch(url, {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({request: request, username: username, password: password})
            });

            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
            }

            result = await response.json();
        } catch (error) {
            console.error(error.message);
        }

        if (!result.success) {
            let errorList;
            if (request === "check login") {
                errorList = document.getElementById("LogErrors");
            }
            else {
                errorList = document.getElementById("NewUserErrors");
            }

            for (let i = 0; i <= result.error.length; i++) {
                const element = document.createElement("li");
                element.innerText = result.error[i];
                element.classList.add("error");
                errorList.appendChild(element);
            }
        }

        return result;
    }

    // # Listeners
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        // 1. use password and username to check if they are correct
        let password = document.getElementById("LogPass").value;
        let username = document.getElementById("LogUsername").value;
        let isDataCorrect = await userDataCheck(password, username, "check login");

        if (!isDataCorrect.success) {
            return 0;
        }

        // 2. check tokens


    })
</script>
</body>
</html>