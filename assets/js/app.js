// Lista de ficheros sin JS
const urlScriptless = ["home"];

// Objeto general para renderizar el navbar y los action buttons de cada modulo, tomando como referencia el rol del usuario (SuperAdmin, Admonistrador, Cliente, Invitado)
const menuItems = {
  "Super Usuario": {
    navbar: [
      { label: "Inicio", page: "home" },
      { label: "Mis Tickets", page: "ticket-list" },
      { label: "Generar Ticket", page: "ticket" },
      { label: "Informe", page: "informe" },
      { label: "Cerrar sesión", action: "logout" },
    ],
    actions: {
      "ticket-list": [
        {
          label: false,
          icon: "bi-arrow-counterclockwise",
          page: false,
          id: "btn-updateTable",
        },
        { label: "Generar Ticket", icon: false, page: "ticket", id: false },
        { label: false, icon: "bi-bar-chart", page: "informe", id: false },
      ],
      ticket: [
        {
          label: "Lista de Tickets",
          icon: false,
          page: "ticket-list",
          id: false,
        },
        { label: false, icon: "bi-bar-chart", page: "informe", id: false },
      ],
      informe: [
        {
          label: false,
          icon: "bi-arrow-counterclockwise",
          page: false,
          id: "btnActualizar",
        },
        {
          label: "Lista de Tickets",
          icon: false,
          page: "ticket-list",
          id: false,
        },
      ],
    },
  },
  Administrador: {
    navbar: [
      { label: "Inicio", page: "home" },
      { label: "Mis Tickets", page: "ticket-list" },
      { label: "Generar Ticket", page: "ticket" },
      { label: "Informe", page: "informe" },
      { label: "Cerrar sesión", action: "logout" },
    ],
    actions: {
      "ticket-list": [
        {
          label: false,
          icon: "bi-arrow-counterclockwise",
          page: false,
          id: "btn-updateTable",
        },
        { label: "Generar Ticket", icon: false, page: "ticket", id: false },
        { label: false, icon: "bi-bar-chart", page: "informe", id: false },
      ],
      ticket: [
        {
          label: "Lista de Tickets",
          icon: false,
          page: "ticket-list",
          id: false,
        },
        { label: false, icon: "bi-bar-chart", page: "informe", id: false },
      ],
      informe: [
        {
          label: false,
          icon: "bi-arrow-counterclockwise",
          page: false,
          id: "btnActualizar",
        },
        {
          label: "Lista de Tickets",
          icon: false,
          page: "ticket-list",
          id: false,
        },
      ],
    },
  },
  Cliente: {
    navbar: [
      { label: "Inicio", page: "home" },
      { label: "Mis Tickets", page: "ticket-list" },
      { label: "Generar Ticket", page: "ticket" },
      { label: "Cerrar sesión", action: "logout" },
    ],
    actions: {
      "ticket-list": [
        {
          label: false,
          icon: "bi-arrow-counterclockwise",
          page: false,
          id: "btn-updateTable",
        },
        { label: "Generar Ticket", icon: false, page: "ticket", id: false },
      ],
      ticket: [
        {
          label: "Lista de Tickets",
          icon: false,
          page: "ticket-list",
          id: false,
        },
      ],
    },
  },
  Invitado: {
    navbar: [
      { label: "Inicio", page: "home" },
      { label: "Login", page: "login" },
    ],
  },
};

// Función para cargar las páginas dinámicas
function loadPage(page) {
  $("#content").load(`/pages/${page}.html`, function (response, status) {
    if (status === "error") {
      //   window.location.href = "./pages/404.html"
      $("#content").html("");
    } else {
      if (!urlScriptless.some((url) => page.includes(url)))
        import(`/assets/js/${page}.js`)
          .then((module) => module.init())
          .catch(() => console.warn(`No script module for ${page}`));
    }
  });

  window.history.pushState({}, "", `/${page}`);
}

// Carga inicial según la URL
$(document).ready(() => {
  const url = window.location.pathname;
  // Dividir la URL por el primer '/' y tomar solo la primera parte
  const page = url.split("/")[0] || "home";
  loadPage(page);
  renderNavbar();
});

// Manejo de la navegación hacia atrás/adelante en el historial
window.addEventListener("popstate", (event) => {
  if (event.state && event.state.page) {
    loadPage(event.state.page);
  }
});

function renderNavbar() {
  const role = getWithExpiry("userRole");
  const navbar = document.getElementById("navbarNavList");
  navbar.innerHTML = ""; // Limpiamos el navbar anterior
  menuItems[role].navbar.forEach((item) => {
    const listItem = document.createElement("li");
    listItem.classList.add("nav-item");

    const link = document.createElement("a");
    link.classList.add("nav-link");
    link.textContent = item.label;
    if (item.action === "logout") {
      link.href = `javascript: logout()`;
    } else {
      link.href = `javascript: loadPage('${item.page}')`;
    }

    listItem.appendChild(link);
    navbar.appendChild(listItem);
  });
}

function renderActions(page) {
  // Utilizo el rol traido por el usuario
  const role = getWithExpiry("userRole");
  const actions = document.getElementById("actions-panel");
  // Limpiamos el div.actions anterior
  actions.innerHTML = "";

  // Recorre lalista completa de acciones del rol
  menuItems[role].actions[page].forEach((item) => {
    //Crea un elmento nuevo "Button" y le agrega las clases "btn", "btn-black" y "mb-3"
    const buttonList = document.createElement("button");
    buttonList.classList.add("btn", "btn-black", "mb-3");

    // Valido si el item tiene icono y creo el elemento para agregarlo dentro del Botón
    if (item.icon) {
      const icon = document.createElement("i");
      icon.classList.add("bi", item.icon);
      buttonList.appendChild(icon);
    } else {
      // Si no tiene icono, agrego el texto del Botón
      buttonList.textContent = item.label;
    }

    if (item.id) buttonList.id = item.id;

    // Si esta asignada la variable Page, se agrega el evento Click
    if (item.page) {
      buttonList.addEventListener("click", function () {
        loadPage(`${item.page}`);
      });
    }

    actions.appendChild(buttonList);
  });
}

function logout() {
  localStorage.removeItem("userRole");
  localStorage.removeItem("userEmail");
  localStorage.removeItem("userEmpresa");
  localStorage.removeItem("userEmpresaID");
  renderNavbar();
  loadPage("login");
}

function setWithExpiry(key, value, ttl) {
  const now = new Date();

  const item = {
    value: value,
    expiry: now.getTime() + ttl, // ttl en milisegundos (ej: 1000 * 60 * 60 para 1 hora)
  };

  localStorage.setItem(key, JSON.stringify(item));
}

function getWithExpiry(key) {
  const itemStr = localStorage.getItem(key);

  if (!itemStr) return "Invitado";

  const item = JSON.parse(itemStr);
  const now = new Date();

  // Si ha expirado, eliminamos el item y devolvemos null
  if (now.getTime() > item.expiry) {
    localStorage.removeItem(key);
    logout();
    return "Invitado";
  }

  return item.value;
}

// Exportamos las funciones al ámbito global
window.loadPage = loadPage;
window.logout = logout;
window.renderNavbar = renderNavbar;
window.renderActions = renderActions;
window.getWithExpiry = getWithExpiry;
window.setWithExpiry = setWithExpiry;
