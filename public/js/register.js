document.addEventListener("DOMContentLoaded", function () {

    const passwordInput = document.getElementById("password");
    const confirmInput = document.getElementById("confirmPassword");

    const strengthBar = document.getElementById("strength-bar");
    const strengthText = document.getElementById("strength-text");

    const togglePassword = document.getElementById("togglePassword");
    const toggleConfirm = document.getElementById("toggleConfirm");

    const form = document.querySelector("form");


if (togglePassword) {
    togglePassword.onclick = () => {

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");
        }
    };
}

if (toggleConfirm) {
    toggleConfirm.onclick = () => {

        if (confirmInput.type === "password") {
            confirmInput.type = "text";
            toggleConfirm.classList.remove("fa-eye");
            toggleConfirm.classList.add("fa-eye-slash");
        } else {
            confirmInput.type = "password";
            toggleConfirm.classList.remove("fa-eye-slash");
            toggleConfirm.classList.add("fa-eye");
        }
    };
}

    passwordInput.addEventListener("input", function () {

        const val = passwordInput.value;

        let hasLength = val.length >= 8;
        let hasUpper = /[A-Z]/.test(val);
        let hasNumber = /[0-9]/.test(val);
        let hasSpecial = /[^A-Za-z0-9]/.test(val);

        let score = 0;
        if (hasLength) score++;
        if (hasUpper) score++;
        if (hasNumber) score++;
        if (hasSpecial) score++;

        // Strength UI
        switch (score) {
            case 0:
                strengthBar.style.width = "0%";
                strengthText.innerText = "";
                break;

            case 1:
                strengthBar.style.width = "25%";
                strengthBar.style.background = "#ef4444";
                strengthText.innerText = "Very Weak";
                break;

            case 2:
                strengthBar.style.width = "50%";
                strengthBar.style.background = "#f59e0b";
                strengthText.innerText = "Weak";
                break;

            case 3:
                strengthBar.style.width = "75%";
                strengthBar.style.background = "#3b82f6";
                strengthText.innerText = "Good";
                break;

            case 4:
                strengthBar.style.width = "100%";
                strengthBar.style.background = "#22c55e";
                strengthText.innerText = "Strong";
                break;
        }


        function setReq(id, met, label) {
            const el = document.getElementById(id);
            el.style.color = met ? "#22c55e" : "#aaa";
            el.innerHTML = `<i class="fa ${met ? 'fa-circle-check' : 'fa-circle-xmark'}"></i> ${label}`;
        }

        setReq("req-length",  hasLength,  "At least 8 characters");
        setReq("req-upper",   hasUpper,   "At least one uppercase letter (A-Z)");
        setReq("req-number",  hasNumber,  "At least one number (0-9)");
        setReq("req-special", hasSpecial, "At least one special character (!@#$...)");

    });


    form.addEventListener("submit", function (e) {

        const val = passwordInput.value;

        if (
            val.length < 8 ||
            !/[A-Z]/.test(val) ||
            !/[0-9]/.test(val) ||
            !/[^A-Za-z0-9]/.test(val)
        ) {
            e.preventDefault();
            alert("Password must have:\n- 8+ characters\n- Uppercase letter\n- Number\n- Special character");
            return;
        }

        if (passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            alert("Passwords do not match!");
        }

    });

});


function openTerms() {
    document.getElementById("openTerms").style.display = "block";
}

function closeModal() {
    document.getElementById("openTerms").style.display = "none";
}

function agreeTerms() {
    document.querySelector('input[name="terms"]').checked = true;
    closeModal();
}

// optional: click outside to close
window.onclick = function(event) {
    let modal = document.getElementById("openTerms");
    if (event.target === modal) {
        modal.style.display = "none";
    }
}
