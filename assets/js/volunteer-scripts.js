jQuery(document).ready(function ($) {
  // Mostrar campo "Otro" en habilidades si se selecciona
  $('input[name="skills"]').change(function () {
    if ($(this).val() === "Otro") {
      $("#skills_other_container").show();
    } else {
      $("#skills_other_container").hide();
    }
  });

  // Mostrar campo de descripción de experiencia si se selecciona "Sí"
  $('input[name="has_experience"]').change(function () {
    if ($(this).val() === "Sí" && $(this).is(":checked")) {
      $("#experience_desc_container").show();
    } else {
      $("#experience_desc_container").hide();
    }
  });

  // Validación adicional antes de enviar el formulario
  $("#volunteer-registration-form").on("submit", function (e) {
    // Verificar que se haya seleccionado un área de interés
    if (!$('input[name="skills"]:checked').length) {
      alert("Por favor selecciona un área de interés");
      return false;
    }

    // Si se seleccionó "Otro", verificar que se haya especificado
    if (
      $('input[name="skills"]:checked').val() === "Otro" &&
      !$("#skills_other").val()
    ) {
      alert("Por favor especifica tu área de interés");
      return false;
    }

    // Verificar que se hayan respondido todas las preguntas de disponibilidad
    if (
      !$('input[name="weekend_availability"]:checked').length ||
      !$('input[name="travel_availability"]:checked').length ||
      !$('input[name="has_experience"]:checked').length
    ) {
      alert(
        "Por favor responde todas las preguntas de disponibilidad y experiencia"
      );
      return false;
    }

    // Si se seleccionó "Sí" en experiencia, verificar que se haya descrito
    if (
      $('input[name="has_experience"]:checked').val() === "Sí" &&
      !$("#experience_desc").val()
    ) {
      alert("Por favor describe brevemente tu experiencia");
      return false;
    }

    return true;
  });
});
