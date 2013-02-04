(function($) {

  var validationRules = {
    message: {
      sender: {
        'Field cannot be empty': /.+/
      },
      email: {
        'Email must be entered': /.+/,
        'Email should be valid': /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/
      },
      content: {
        'Message cannot be empty': /.+/
      }
    }
  };

  var onInputError = function(msg) {
    $(this).parent().addClass('error');
    $('<span class="help-inline">' + msg + '</span>').insertAfter($(this));
  };

  var onValidated = function() {
    var form = $(this);
    $.ajax({
      dataType: 'json',
      type: 'POST',
      url: '/contact/message.json',
      data: $(':input', form),
      success: function(message) {
        $(':input', form)
          .not(':button, :submit, :reset, :hidden')
          .val('')
          .removeAttr('checked')
          .removeAttr('selected')
        ;
        form.find('.alert')
          .removeClass('alert-error')
          .addClass('alert-success')
          .html('<p><strong>Message was sent from ' + message.email + '</strong> successfuly. Hope I can answer to you soon ;)</p>')
          .show('slow')
        ;
      },
      error: function(json) {
        console.log('error oi');
        form.find('.alert')
          .removeClass('alert-succes')
          .addClass('alert-error')
          .html('<p><strong>Oops, server error, please try again later.</p>')
          .show('slow')
        ;
      }
    });
  };

  $.fn.contactable = function() {
    return $(this).on('submit', function(e) {
      e.preventDefault();
      $(this).find('.control-group').removeClass('error');
      $(this).find('.help-inline').remove();
      $(this).validate_form(validationRules, onInputError, onValidated);
    });
  };

}(window.jQuery));

