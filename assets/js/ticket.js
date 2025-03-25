export function init() {
  $("#generateTicketForm").on("submit", function (e) {
    e.preventDefault();
    let email = localStorage.getItem("userEmail") || "";
    const data = {
      empresa: $("#empresa").val(),
      areaEjecutora: $("#areaEjecutora").val(),
      tipoAtencion: $("#tipoAtencion").val(),
      producto: $("#producto").val(),
      descripcion: $("#descripcion").val(),
      estado: "generado",
      email: email,
    };

    console.log("Info del ticket: ", data);
    $.ajax({
      url: "api/tickets/create.php",
      type: "POST",
      data: JSON.stringify(data),
      contentType: "application/json; charset=utf-8",
      beforeSend: function () {
        showLoader();
      },
      success: function (response) {
        if (response.success) {
          alert("Ticket generado exitosamente");
          $("#areaEjecutora").val("");
          $("#tipoAtencion").val("");
          $("#producto").val("");
          $("#descripcion").val("");
        } else {
          alert("Error al generar el ticket: " + response.message);
        }
        hideLoader();
      },
      error: function (error) {
        console.error("Error al generar el ticket:", error);
        hideLoader();
      },
    });
  });

  renderActions("ticket");
  const role = getWithExpiry("userRole");
  const empresa = localStorage.getItem("userEmpresa");

  console.log(`Rol: ${role}, Empresa: ${empresa}`);

  if (role == "Super Usuario") {
    $("#empresasSegment")
      .html("")
      .append(
        `<label for="empresa" class="form-label"
          >Empresas</label
        >
        <select
          class="form-select"
          id="empresa"
          name="empresa"
          required
        >
          <option value="Empresa 1">Empresa 1</option>
          <option value="Empresa 2">Empresa 2</option>
          <option value="Empresa 3">Empresa 3</option>
        </select>`
      )
      .removeClass("hidden");
  } else {
    $("#empresa").val(empresa);
  }
}

function showLoader() {
  $(".loader-overlay").show();
  $(".loader").show();
}

function hideLoader() {
  $(".loader-overlay").hide();
  $(".loader").hide();
}
