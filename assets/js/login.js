export function init() {

  $("#loginForm").on("submit", function (event) {
    event.preventDefault();
    const email = $("#email").val();
    const password = $("#password").val();

    $.ajax({
      url: "./api/login.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({ email: email, password: password }),
      success: function (response) {
        if (response.success) {
          localStorage.setItem("userEmail", response.email);
          localStorage.setItem("userEmpresa", response.empresaName);
          localStorage.setItem("userEmpresaID", response.empresaID);
          setWithExpiry('userRole', response.rol, 1000 * 60 * 30);
          renderNavbar();
          loadPage("ticket-list");
        } else {
          alert("Credenciales incorrectas");
        }
      },
      error: function () {
        alert("Error al iniciar sesión");
      },
    });
  });
}
