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

  $("#resend-credentials-btn").on("click", function (e) {
    e.preventDefault();
    const btn = $(this);
    const status = $("#resend-credentials-status");

    btn.prop("disabled", true).text("Enviando...");
    status.text("").removeClass("error success");

    if (typeof hv_admin === "undefined") {
      status.text("Error de configuración").addClass("error");
      btn.prop("disabled", false).text("Reenviar credenciales");
      return;
    }

    $.ajax({
      url: hv_admin.ajax_url,
      type: "POST",
      data: {
        action: "resend_credentials",
        user_id: btn.data("user-id"),
        security: hv_admin.nonce,
      },
      success: function (response) {
        if (response && response.success) {
          status.text(response.data).addClass("success");
        } else {
          const fallback = "Error desconocido";
          const msg = response?.data?.message || response?.data || fallback;
          let debug = "";

          if (response?.data?.debug) {
            debug += "\nDebug:";
            for (let key in response.data.debug) {
              debug += `\n${key}: ${JSON.stringify(response.data.debug[key])}`;
            }
          }

          alert(msg + debug);
          status.text(msg).addClass("error");
        }
      },
      error: function (xhr) {
        let errorMsg = "Error de conexión";
        try {
          const response = JSON.parse(xhr.responseText);
          if (response?.data?.message) {
            errorMsg = response.data.message;
          }
        } catch (e) {}
        alert(errorMsg);
        status.text(errorMsg).addClass("error");
      },
      complete: function () {
        btn.prop("disabled", false).text("Reenviar credenciales");
      },
    });
  });
});
