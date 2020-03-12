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

$(document).ready(function () {
  var $deleteButton = $('.btn-delete');

  $deleteButton.on('click', function (e) {
    e.preventDefault();
    var $button = $(this);
    var url = $button.attr('href');

    swal({
      title: $('#ui-delete-title').html(),
      text: $('#ui-delete-text').html(),
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#AB162B",
      confirmButtonText: $('#ui-yes').html(),
      cancelButtonText: $('#ui-no').html(),
      closeOnConfirm: true
    }, function () {
      $.ajax({
        url: url,
        type: 'DELETE',
        success: function (data) {
          var $tr = $button.closest('tr');

          $tr.slideUp('fast', function () {
            $tr.remove();
          });

        },
        error(xhr) {
          var errorMessage = $button.data('error-message') || "There was an error while trying to delete data.";
          swal("Error!", errorMessage, "error");
        }
      });
    });

  });

  function initAdmin() {

  }

  initAdmin();
});
