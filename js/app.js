document.getElementById("togglePassword").addEventListener("click", function() {
    const passwordField = document.getElementById("password");
    const type = passwordField.getAttribute("type");

    if (type === "password") {
        passwordField.setAttribute("type", "text");
        this.textContent = "🙈";
    } else {
        passwordField.setAttribute("type", "password");
        this.textContent = "👁";
    }
});
