export function init() {
  const previewImage = $("#previewImage");
  const previewPDF = $("#previewPDF");
  const previewContainer = $("#previewContainer");

  $("#fileInput").on("change", function (event) {
    const file = event.target.files[0];

    previewContainer.show();
    if (file) {
      const fileType = file.type;
      $("#fileName").text(file.name);

      if (fileType.startsWith("image/")) {
        // Si es imagen
        const reader = new FileReader();
        reader.onload = function (e) {
          previewImage.attr("src", e.target.result).show();
          previewPDF.hide();
        };
        reader.readAsDataURL(file);
      } else if (fileType === "application/pdf") {
        // Si es PDF
        const fileURL = URL.createObjectURL(file);
        previewPDF.attr("src", fileURL).show();
        previewImage.hide();
      } else {
        $.toast({
          type: "error",
          message: `Formato no soportado, solo se pueden adjuntar imagenes(.png, .jpeg, .jpg, .svg) o documentos PDF.`,
        });
        removeFileInput();
      }
    }else{
      $("#fileName").text("Ningún archivo seleccionado");
    }
  });

  // Botón para limpiar el input y vista previa
  $("#clearFile").on("click", function () {
    removeFileInput();
  });

  $("#generateTicketForm").on("submit", function (e) {
    e.preventDefault();
    let email = localStorage.getItem("userEmail") || "";

    const data = {
      file: $("#fileInput")[0].files[0] || "",
      empresa: $("#empresa").val(),
      areaEjecutora: $("#areaEjecutora").val(),
      tipoAtencion: $("#tipoAtencion").val(),
      producto: $("#producto").val(),
      descripcion: $("#descripcion").val(),
      estado: "generado",
      email: email,
    };

    const formData = new FormData();

    for (let key in data) {
      if (data.hasOwnProperty(key)) {
        formData.append(key, data[key]); // Agregar cada propiedad del data al FormData
      }
    }

    $.ajax({
      url: "api/tickets/create.php",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      beforeSend: function () {
        preloader.preloader();
      },
      success: function (response) {
        preloader.preloader("remove");
        if (response.success) {
          $.toast({
            type: "success",
            message: `Ticket generado exitosamente.`,
          });
          $("#areaEjecutora").val("");
          $("#tipoAtencion").val("");
          $("#producto").val("");
          $("#descripcion").val("");
          removeFileInput();
        } else {
          $.toast({
            type: "error",
            message: `Error al generar el ticket: ${response.message}`,
          });
        }
      },
      error: function (error) {
        console.error("Error al generar el ticket:", error);
        preloader.preloader("remove");
      },
    });
  });

  renderActions("ticket");
  const role = getWithExpiry("userRole");
  const empresa = localStorage.getItem("userEmpresa");

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

  preloader.preloader("remove");
}

function removeFileInput() {
  $("#fileInput").val("");
  $("#previewImage").hide().attr("src", "");
  $("#previewPDF").hide().attr("src", "");
  $("#previewContainer").hide();
  $("#fileName").text("Ningún archivo seleccionado");
}
