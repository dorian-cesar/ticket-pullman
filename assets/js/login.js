export function init() {
  $("#loginForm").on("submit", function (event) {
    event.preventDefault();
    preloader.preloader();
    const email = $("#email").val();
    const password = $("#password").val();

    $.ajax({
      url: "./api/login.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({ email: email, password: password }),
      success: function (response) {
        preloader.preloader("remove");
        if (response.success) {
          localStorage.setItem("userEmail", response.email);
          localStorage.setItem("userEmpresa", response.empresaName);
          localStorage.setItem("userEmpresaID", response.empresaID);
          localStorage.setItem("userArea", response.area);
          setWithExpiry("userRole", response.rol, 1000 * 60 * 30);
          renderNavbar();
          loadPage("ticket-list");
        } else {
          $.toast({
            type: "error",
            message: "Credenciales incorrectas.",
          });
        }
        
      },
      error: function () {
        $.toast({
          type: "error",
          message: "Error al iniciar sesión.",
        });
        preloader.preloader("remove");
      },
    });
  });

  preloader.preloader("remove");
}
