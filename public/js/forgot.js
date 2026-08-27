document.getElementById("forgotForm").addEventListener("submit", function(e){

e.preventDefault();

const email = document.getElementById("email").value;
const message = document.getElementById("message");

if(email === "admin@gmail.com" || email === "user@gmail.com"){

message.style.color = "lightgreen";
message.textContent = "Password reset instructions sent to your email.";

}
else{

message.style.color = "red";
message.textContent = "Email not found in system.";

}

});