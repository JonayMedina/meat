$.blockUI.defaults.message = $('#blockUIMessage');

let $toastSuccess = $('.toast-success');

$toastSuccess.toast({
  delay: 5000,
  animation: true
});


function success(text, title) {
  title = title || $('#ui-success-title').html();
  $toastSuccess.find('.toast-title').html(title);
  $toastSuccess.find('.toast-body').html(text);

  $toastSuccess.toast('show');
}

function error(text, title) {
  title = title || $('#ui-error-title').html();
  $toastSuccess.find('.toast-title').html(title);
  $toastSuccess.find('.toast-body').html(text);

  $toastSuccess.toast('show');
}

function blockUI() {
  $.blockUI();
}

function unblockUI() {
  $.unblockUI();
}

function initCounter() {
  $('.input-counter').each(function() {
    var $input = $(this);
    var max = $input.attr('maxlength');

    $input.on('keyup change', function() {
      var length = $(this).val().length;
      var label = $(this).parent('.form-group').find('.counter-label');

      label.html(length + "/" + max);
    }).change();
  });
}

function reload() {
  window.location = window.location;
}

$(document).ready(function () {
  var $deleteButton = $('.btn-delete');

  $deleteButton.on('click', function (e) {
    e.preventDefault();
    var $button = $(this);
    var url = $button.attr('href');

    swal({
      title: $button.data('title') || $('#ui-delete-title').html(),
      text: $button.data('text'),
      type: null,
      showCancelButton: true,
      confirmButtonColor: "#AB162B",
      confirmButtonText: $('#ui-delete').html(),
      cancelButtonText: $('#ui-cancel').html(),
      closeOnConfirm: true
    }, function () {
      blockUI();

      $.ajax({
        url: url,
        type: 'DELETE',
        success: function (data) {
          unblockUI();

          var $tr = $button.closest('tr');

          $tr.slideUp('fast', function () {
            $tr.remove();
            reload();
          });

        },
        error(xhr) {
          unblockUI();
          var errorMessage = $button.data('error-message') || "There was an error while trying to delete data.";
          swal("Error!", errorMessage, "error");
        }
      });
    });

  });

  $('.custom-file-input').on('change', function(event) {
    var inputFile = event.currentTarget;
    $(inputFile).parent()
      .find('.custom-file-label')
      .html(inputFile.files[0].name);
  });

  function initAdmin() {
    $('[data-toggle="tooltip"]').tooltip();
  }

  initAdmin();
});
