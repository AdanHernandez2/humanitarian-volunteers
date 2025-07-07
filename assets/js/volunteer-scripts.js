jQuery(document).ready(function ($) {
  // Mostrar campo "Otro" en habilidades si se selecciona
  $('input[name="skills"]').change(function () {
    if ($(this).val() === "Otro") {
      $("#skills_other_container").show();
      $("#skills_other").prop("required", true);
    } else {
      $("#skills_other_container").hide();
      $("#skills_other").prop("required", false);
    }
  });

  // Mostrar campo de descripción de experiencia si se selecciona "Sí"
  $('input[name="has_experience"]').change(function () {
    if ($(this).val() === "Sí" && $(this).is(":checked")) {
      $("#experience_desc_container").show();
      $("#experience_desc").prop("required", true);
    } else {
      $("#experience_desc_container").hide();
      $("#experience_desc").prop("required", false);
    }
  });

  // Validar formulario antes de enviar
  $("#volunteer-registration-form").on("submit", function (e) {
    // Validación básica
    if (!$("#first_name").val() || !$("#last_name").val()) {
      showMessage("Por favor ingresa tu nombre y apellido", "danger");
      return false;
    }

    if (!$("#email").val() || !isValidEmail($("#email").val())) {
      showMessage("Por favor ingresa un correo electrónico válido", "danger");
      return false;
    }

    if (!$("#phone").val()) {
      showMessage("Por favor ingresa tu número de teléfono", "danger");
      return false;
    }

    return true;
  });

  // Mostrar mensaje
  function showMessage(message, type) {
    var messageContainer = $("#form-message");
    messageContainer
      .removeClass("alert-success alert-danger")
      .addClass("alert-" + type)
      .find(".message-content")
      .text(message);
    messageContainer.show();

    // Desplazar a mensaje
    $("html, body").animate(
      {
        scrollTop: messageContainer.offset().top - 100,
      },
      500
    );
  }

  // Validar email
  function isValidEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }
});
