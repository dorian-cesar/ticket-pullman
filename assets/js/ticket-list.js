import "https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js";
import "https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js";

let dataTable;
let lastFocusedButton;

export function init() {
  if ($("#ticketsTable").length == 0) {
    // Creamos un MutationObserver para observar cambios en el DOM
    const observer = new MutationObserver((mutationsList, observer) => {
      // Verificar si #ticketsTable ha sido agregado al DOM
      if ($("#ticketsTable").length > 0) {
        console.log("Elemento encontrado, inicializando DataTable...");
        initializeDataTable();
        observer.disconnect(); // Detener la observación
      }
    });

    // Configurar el observer para observar cambios en el body
    observer.observe(document.body, {
      childList: true, // Observar la adición de nodos hijos
      subtree: true, // Observar cambios en todo el árbol del DOM
    });
  } else {
    initializeDataTable();
  }
}

function initializeDataTable() {
  renderActions("ticket-list");
  dataTable = $("#ticketsTable").DataTable({
    ajax: {
      url: "api/tickets/read.php",
      dataSrc: function (json) {
        // Asegúrate de que json es un array de objetos
        if (!Array.isArray(json)) {
          console.error("Invalid JSON structure:", json);
          return [];
        }
        return json;
      },
    },
    columns: [
      { data: "id", visible: false },
      {
        data: null,
        render: function (data, type, row) {
          return row.area_solicitante != ""
            ? row.area_solicitante
            : row.empresa;
        },
      },
      { data: "area_ejecutora" },
      { data: "tipo_atencion" },
      { data: "producto" },
      { data: "descripcion" },
      { data: "estado" },
      {
        data: null,
        orderable: false,
        className: "actions-col",
        render: function (data, type, row) {
          let buttons = "";

          if (row.estado == "generado")
            buttons = `<button class="btn btn-black mb-1 change-status" data-id="${row.id}" data-email="${row.email}" title="Cambiar a 'En Curso'">
              <i class="bi bi-person-check"></i>
            </button>`;

          buttons += `
            <button class="btn btn-black mb-1 finish-ticket" data-id="${row.id}" data-email="${row.email}" title="Cerrar Ticket">
              <i class="bi bi-lock-fill"></i>
            </button>`;

          return buttons;
        },
      },
    ],
    order: [[6, "asc"]],
    language: {
      // "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" // Para traducir DataTables a español
      paginate: {
        previous: "Anterior", // Texto para el botón de "anterior"
        next: "Siguiente", // Texto para el botón de "siguiente"
      },
      sProcessing: "Procesando...",
      sLengthMenu: "Mostrar _MENU_ registros",
      sZeroRecords: "No se encontraron resultados",
      sEmptyTable: "Ningún dato disponible en esta tabla",
      sInfo:
        "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
      sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
      sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
      sInfoPostFix: "",
      sSearch: "Buscar:",
      sUrl: "",
      oAria: {
        sSortAscending:
          ": Activar para ordenar la columna de manera ascendente",
        sSortDescending:
          ": Activar para ordenar la columna de manera descendente",
      },
    },
  });

  $("#btn-updateTable").on("click", () => {
    reloadDataTable();
  });

  // Eventos para los botones
  $("#ticketsTable").on("click", ".change-status", function () {
    lastFocusedButton = this;
    const id = $(this).data("id");
    const email = $(this).data("email");
    console.log(`Cambiando estado del ticket ${id}`);

    $("#statusModalMessage").html(
      "¿Seguro que deseas cambiar el estado de este ticket a <b>En Curso<b/>?"
    );
    $("#statusModal").modal("show");
    $("#confirmChange")
      .off("click")
      .on("click", function () {
        actualizarEstadoTicket(id, "en curso", "", email);
        $("#statusModal").modal("hide");
      });
  });

  $("#ticketsTable").on("click", ".finish-ticket", function () {
    const id = $(this).data("id");
    const email = $(this).data("email");
    console.log(`Finalizando ticket ${id}`);

    $("#finishModalMessage").html(
      "¿Seguro que deseas cerrar el ticket seleccionado?"
    );
    $("#finishModal").modal("show");
    $("#confirmFinish")
      .off("click")
      .on("click", function () {
        const reason = $("#finishReason").val().trim();

        if (reason === "") {
          alert("Por favor, escribe un motivo para el cambio de estado.");
          return;
        }

        actualizarEstadoTicket(id, "cerrado", reason, email);
        $("#finishModal").modal("hide");
      });

    // Aquí puedes mandar una petición a la API para finalizar el ticket
  });

  $("#finishModal").on("hidden.bs.modal", function () {
    $("#finishReason").val("");
    if (lastFocusedButton) {
      $(lastFocusedButton).focus(); // Devuelve el foco al botón original
    }
  });
}

// Función para recargar el DataTable
function reloadDataTable() {
  if (dataTable) {
    console.log("Recargando la tabla");
    dataTable.ajax.reload(null, false); // false mantiene la paginación actual
  }
}

function actualizarEstadoTicket(ticketId, estado, descripcion = "", email) {

  $.ajax({
    url: "api/tickets/update.php",
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      id: ticketId,
      estado: estado,
      descripcion: descripcion,
      email: email,
    }),
    success: function (response) {
      if (response.success) {
        alert("Estado del ticket actualizado: " + response.message);
        reloadDataTable();
      } else {
        alert("Error al actualizar el estado del ticket: " + response.message);
      }
    },
    error: function () {
      alert("Error al actualizar el estado del ticket");
    },
  });
}
