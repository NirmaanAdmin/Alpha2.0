$(function () {
  $("#forms_bulk_actions").on("show.bs.modal", function () {
    $("#primary_form_id")
      .find("option")
      .remove()
      .end()
      .append("<option></option>");
    $("#merge_forms").prop("checked", false);
    $("#merge_forms").trigger("change");
  });

  $("#merge_forms").on("change", function () {
    var $mergeCheckbox = $(this);
    var merge_forms = $mergeCheckbox.prop("checked");
    var $bulkChange = $("#bulk_change");
    var $formsSelect = $("#primary_form_id");
    var rows = $(".table-forms").find("tbody tr");

    $formsSelect.find("option").remove().end().append("<option></option>");
    if (merge_forms) {
      $("#bulk_change").addClass("hide");
      $("#merge_forms_wrapper").removeClass("hide");
      $(".mass_delete_checkbox").addClass("hide");
      $("#mass_delete").prop("checked", false);
      $bulkChange.addClass("hide");

      $.each(rows, function () {
        var checkbox = $($(this).find("td").eq(0)).find("input");
        if (checkbox.prop("checked") == true) {
          $formsSelect.append(
            '<option value="' +
            checkbox.val() +
            '" data-status="' +
            checkbox.data("status") +
            '">' +
            checkbox.data("name") +
            "</option"
          );
        }
      });
      $formsSelect.selectpicker("refresh");
    } else {
      $("#merge_forms_wrapper").addClass("hide");
      $bulkChange.removeClass("hide");
      $(".mass_delete_checkbox").removeClass("hide");
    }
  });

  $("#primary_form_id").on("change", function () {
    var status = $(this).find("option:selected").data("status");
    $("#primary_form_status").selectpicker("val", status);
  });

  // Add predefined reply click
  $("#insert_predefined_reply").on("change", function (e) {
    e.preventDefault();
    var selectpicker = $(this);
    var id = selectpicker.val();
    if (id != "") {
      requestGetJSON("forms/get_predefined_reply_ajax/" + id).done(function (
        response
      ) {
        tinymce.activeEditor.execCommand(
          "mceInsertContent",
          false,
          response.message
        );
        selectpicker.selectpicker("val", "");
      });
    }
  });

  $("#form_no_contact").on("click", function (e) {
    e.preventDefault();
    validate_new_form_form();
    let allGood = true;
    $('.commentInput').each(function () {
      if (!$(this).val().trim()) {
        allGood = false;
        $(this).addClass('is-invalid'); // you can style .is-invalid in CSS
      } else {
        $(this).removeClass('is-invalid');
      }
    });
    if (!allGood) {
      e.preventDefault();
      alert('Please fill in all comment fields before submitting.');
    }
    $("#name, #email").prop("disabled", false);
    $("#name").val("").rules("add", { required: true });
    $("#email").val("").rules("add", { required: true });

    $(this).addClass("hide");

    $("#contactid").removeAttr("required");
    $("#contactid").selectpicker("val", "");
    $('input[name="userid"]').val("");

    $("#form_to_contact").removeClass("hide");
    $("#form_contact_w").addClass("hide");
  });

  $("#form_to_contact").on("click", function (e) {
    e.preventDefault();
    $("#name, #email").prop("disabled", true);
    $("#form_no_contact").removeClass("hide");
    $("#contactid").attr("required", true);
    $("#name").rules("remove", "required");
    $("#email").rules("remove", "required");
    $("#form_no_contact, #form_contact_w").removeClass("hide");
    $(this).addClass("hide");
  });

  $(".block-sender").on("click", function () {
    var sender = $(this).data("sender");
    if (sender == "") {
      alert("No Sender Found");
      return false;
    }
    $.post(admin_url + "forms/block_sender", {
      sender: sender,
    }).done(function () {
      window.location.reload();
    });
  });

  // Admin form note add
  $(".add_note_form").on("click", function (e) {
    e.preventDefault();
    var note_description = $('textarea[name="note_description"]').val();
    var formid = $('input[name="formid"]').val();
    if (note_description == "") {
      return;
    }
    $(e.target).addClass("disabled");
    $.post(admin_url + "misc/add_note/" + formid + "/form", {
      description: note_description,
    }).done(function () {
      window.location.reload();
    });
  });

  // Update form settings from settings tab
  $(".save_dpr_changes").on("click", function (e) {
    e.preventDefault();
    var data = {};

    var $settingsArea = $("#settings");
    var errors = false;

    if ($settingsArea.find('input[name="subject"]').val() == "") {
      errors = true;
      $settingsArea
        .find('input[name="subject"]')
        .parents(".form-group")
        .addClass("has-error");
    } else {
      $settingsArea
        .find('input[name="subject"]')
        .parents(".form-group")
        .removeClass("has-error");
    }

    var selectRequired = ["department", "priority"];

    if ($("#contactid").data("no-contact") != true) {
      selectRequired.push("contactid");
    }

    for (var i = 0; i < selectRequired.length; i++) {
      var $select = $settingsArea.find(
        'select[name="' + selectRequired[i] + '"]'
      );
      if ($select.selectpicker("val") == "") {
        errors = true;
        $select.parents(".form-group").addClass("has-error");
      } else {
        $select.parents(".form-group").removeClass("has-error");
      }
    }

    var cf_required = $settingsArea.find('[data-custom-field-required="1"]');

    $.each(cf_required, function () {
      var cf_field = $(this);
      var parent = cf_field.parents(".form-group");
      if (cf_field.is(":checkbox")) {
        var checked = parent.find('input[type="checkbox"]:checked');
        if (checked.length == 0) {
          errors = true;
          parent.addClass("has-error");
        } else {
          parent.removeClass("has-error");
        }
      } else if (cf_field.is("input") || cf_field.is("textarea")) {
        if (cf_field.val() === "") {
          errors = true;
          parent.addClass("has-error");
        } else {
          parent.removeClass("has-error");
        }
      } else if (cf_field.is("select")) {
        if (cf_field.selectpicker("val") == "") {
          errors = true;
          parent.addClass("has-error");
        } else {
          parent.removeClass("has-error");
        }
      }
    });

    if (errors == true) {
      return;
    }

    // Create a FormData object
    var formData = new FormData();

    // Serialize the form data
    $("#settings *")
      .serializeArray()
      .forEach(function (field) {
        formData.append(field.name, field.value);
      });

    // Add the form ID
    formData.append("formid", $('input[name="formid"]').val());

    // Add the CSRF token if available
    if (typeof csrfData !== "undefined") {
      formData.append(csrfData["token_name"], csrfData["hash"]);
    }

    // Append all dynamic file inputs
    $('.attachment_new input[type="file"]').each(function () {
      var fileInput = $(this)[0]; // Get the file input element
      if (fileInput.files.length > 0) {
        // Use the dynamic name attribute for the key
        formData.append($(this).attr('name'), fileInput.files[0]);
      }
    });
    // Send the AJAX request
    $.ajax({
      url: admin_url + "forms/update_dpr_changes",
      type: "POST",
      data: formData,
      processData: false, // Prevent jQuery from automatically processing the data
      contentType: false, // Prevent jQuery from setting the Content-Type header
      success: function (response) {
        response = JSON.parse(response);
        if (response.success === true) {
          if (typeof response.department_reassigned !== "undefined") {
            window.location.href = admin_url + "forms/";
          } else {
            window.location.reload();
          }
        } else if (typeof response.message !== "undefined") {
          alert_float("warning", response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        alert_float("danger", "An error occurred while processing your request.");
      },
    });

  });

  $("#new_form_form").submit(function () {
    $("#project_id").prop("disabled", false);
    // $('#loader-container').removeClass('hide');
    return true;
  });

  // Change form status without replying
  $('select[name="status_top"]').on("change", function () {
    var status = $(this).val();
    var formid = $('input[name="formid"]').val();
    requestGetJSON(
      "forms/change_status_ajax/" + formid + "/" + status
    ).done(function (response) {
      alert_float(response.alert, response.message);
    });
  });

  // Select form user id
  $('body.form select[name="contactid"]').on("change", function () {
    var contactid = $(this).val();

    var projectAjax = $('select[name="project_id"]');
    var projectAutoSelected = projectAjax.attr("data-auto-project");
    var projectsWrapper = $(".projects-wrapper");
    if (!projectAjax.attr("disabled")) {
      var clonedProjectsAjaxSearchSelect;
      if (!projectAutoSelected) {
        clonedProjectsAjaxSearchSelect = projectAjax.html("").clone();
      } else {
        clonedProjectsAjaxSearchSelect = projectAjax.clone();
        clonedProjectsAjaxSearchSelect.prop("disabled", true);
      }
      projectAjax.selectpicker("destroy").remove();
      projectAjax = clonedProjectsAjaxSearchSelect;
      $("#project_ajax_search_wrapper").append(clonedProjectsAjaxSearchSelect);
      init_ajax_search("project", projectAjax, {
        customer_id: function () {
          return $('input[name="userid"]').val();
        },
      });
    }
    if (contactid != "") {
      $.post(admin_url + "forms/form_change_data/", {
        contact_id: contactid,
      }).done(function (response) {
        response = JSON.parse(response);
        if (response.contact_data) {
          $('input[name="name"]').val(
            response.contact_data.firstname +
            " " +
            response.contact_data.lastname
          );
          $('input[name="email"]').val(response.contact_data.email);
          $('input[name="userid"]').val(response.contact_data.userid);
          if (response.contact_data.ticket_emails == "0") {
            show_form_no_contact_email_warning(
              response.contact_data.userid,
              response.contact_data.id
            );
          } else {
            clear_form_no_contact_email_warning();
          }
        }
        if (!projectAutoSelected) {
          if (response.customer_has_projects) {
            projectsWrapper.removeClass("hide");
          } else {
            projectsWrapper.addClass("hide");
          }
        } else {
          projectsWrapper.removeClass("hide");
        }
      });
    } else {
      $('input[name="name"]').val("");
      $('input[name="email"]').val("");
      $('input[name="contactid"]').val("");
      if (!projectAutoSelected) {
        projectsWrapper.addClass("hide");
      } else {
        projectsWrapper.removeClass("hide");
      }
      clear_form_no_contact_email_warning();
    }
  });
});

// Insert form knowledge base link modal
function insert_form_knowledgebase_link(e) {
  var id = $(e).val();
  if (id == "") {
    return;
  }
  requestGetJSON("knowledge_base/get_article_by_id_ajax/" + id).done(function (
    response
  ) {
    var textarea = $('textarea[name="message"]');
    tinymce.activeEditor.execCommand(
      "mceInsertContent",
      false,
      '<a href="' +
      site_url +
      "knowledge_base/" +
      response.slug +
      '">' +
      response.subject +
      "</a>"
    );
    $(e).selectpicker("val", "");
  });
}

function forms_bulk_action(event) {
  if (confirm_delete()) {
    var mass_delete = $("#mass_delete").prop("checked");
    var merge_forms = $("#merge_forms").prop("checked");
    var ids = [];
    var data = {};

    if (typeof merge_forms != "undefined" && merge_forms == true) {
      data.merge_forms = true;
      data.primary_form = $("#primary_form_id").val();
      data.primary_form_status = $("#primary_form_status").val();

      if (data.primary_form == "") {
        console.log("empty");

        return;
      }
    } else if (mass_delete == false || typeof mass_delete == "undefined") {
      data.status = $("#move_to_status_forms_bulk").val();
      data.department = $("#move_to_department_forms_bulk").val();
      data.priority = $("#move_to_priority_forms_bulk").val();
      data.service = $("#move_to_service_forms_bulk").val();
      data.tags = $("#tags_bulk").tagit("assignedTags");
      if (
        data.status == "" &&
        data.department == "" &&
        data.priority == "" &&
        data.service == "" &&
        data.tags == ""
      ) {
        return;
      }
    } else {
      data.mass_delete = true;
    }
    var rows = $(".table-forms").find("tbody tr");
    $.each(rows, function () {
      var checkbox = $($(this).find("td").eq(0)).find("input");
      if (checkbox.prop("checked") == true) {
        ids.push(checkbox.val());
      }
    });
    data.ids = ids;
    $(event).addClass("disabled");
    setTimeout(function () {
      $.post(admin_url + "forms/bulk_action", data).done(function () {
        window.location.reload();
      });
    }, 50);
  }
}

function show_form_no_contact_email_warning(userid, contactid) {
  if ($("#contact_email_notifications_warning").length == 0) {
    $("#new_form_form, #single-form-form").prepend(
      '<div class="alert alert-warning" id="contact_email_notifications_warning">Email notifications for forms is disabled for this contact, if you want the contact to receive form emails you must enable by clicking <a href="' +
      admin_url +
      "clients/client/" +
      userid +
      "?contactid=" +
      contactid +
      '" target="_blank">here</a>.</div>'
    );
  }
}

function clear_form_no_contact_email_warning() {
  $("#contact_email_notifications_warning").remove();
}

function validate_new_form_form() {
  $("#new_form_form").appFormValidator();

  setTimeout(function () {
    $.each(
      $("#new_form_form").find('[data-custom-field-required="1"]'),
      function () {
        $(this).rules("add", "required");
      }
    );
  }, 10);
  
}
