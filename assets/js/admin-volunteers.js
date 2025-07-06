jQuery(document).ready(function ($) {
  // Manejar verificación de voluntarios
  $(".verify-btn").on("click", function (e) {
    e.preventDefault();

    const button = $(this);
    const userId = button.data("user-id");
    const row = button.closest("tr");

    button.prop("disabled", true).text("Verificando...");

    $.ajax({
      url: hv_admin.ajax_url,
      type: "POST",
      data: {
        action: "verify_volunteer",
        user_id: userId,
        nonce: hv_admin.nonce,
      },
      success: function (response) {
        if (response.success) {
          // Actualizar estado en la tabla
          row.find(".verification-status").html(response.data.new_status);
          button.replaceWith(response.data.new_button);

          // Mostrar mensaje de éxito
          alert(response.data.message);
        } else {
          alert("Error: " + response.data.message);
          button.prop("disabled", false).text("Verificar");
        }
      },
      error: function () {
        alert("Error en la solicitud");
        button.prop("disabled", false).text("Verificar");
      },
    });
  });
});
