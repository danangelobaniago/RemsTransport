document.addEventListener("DOMContentLoaded", function () {

    const password = document.getElementById("password");
    const togglePassword = document.getElementById("togglePassword");
    const form = document.getElementById("loginForm");
    const email = document.getElementById("email");


    if (togglePassword && password) {
        togglePassword.addEventListener("click", function () {

            const isHidden = password.type === "password";

            password.type = isHidden ? "text" : "password";

            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    }


    document.querySelectorAll(".input-box input").forEach(input => {
        input.addEventListener("focus", () => {
            input.parentElement.style.boxShadow = "0 0 0 2px #2563eb";
        });

        input.addEventListener("blur", () => {
            input.parentElement.style.boxShadow = "none";
        });
    });


    if (form) {
        form.addEventListener("submit", function (e) {

            const emailVal = email.value.trim();
            const passVal = password.value.trim();

            if (!emailVal || !passVal) {
                e.preventDefault();
                showError("Please fill in all fields.");
                return;
            }

            if (passVal.length < 8) {
                e.preventDefault();
                showError("Password must be at least 8 characters.");
                return;
            }

            /* OPTIONAL: show loading */
            showLoading();
        });
    }


    function showError(message) {
        let error = document.querySelector(".error-msg");

        if (!error) {
            error = document.createElement("p");
            error.className = "error-msg";
            form.appendChild(error);
        }

        error.innerText = message;
    }


    function showLoading() {
        let loader = document.querySelector(".loader");

        if (!loader) {
            loader = document.createElement("div");
            loader.className = "loader";
            form.appendChild(loader);
        }

        loader.style.display = "block";
    }

});
