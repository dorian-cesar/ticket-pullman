export function init() {

  renderActions("informe");
  
  $("#btnActualizar").on("click", () => {
    $("#informeIframe").attr("src", $("#informeIframe").attr("src"));
  });

  
}
