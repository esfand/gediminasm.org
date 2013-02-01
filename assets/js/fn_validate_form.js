(function ($) {

  /**
   * rules = {
   *   comment: {
   *     email: {
   *       'Field cannot be empty': /.+/,
   *       'Should be valid email address': /emailmatchregex/
   *     },
   *     content: {
   *       'Field cannot be empty': /.+/
   *     }
   *   }
   * }
   *
   * onInputError will be invoked like onInputError($(inputElement), errorMessage)
   * so an error message can be attached to actual input
   * Note: do not forget to clean previous error messages before validating again
   */
  $.fn.validate_form = function(rules, onInputError, onFormValidated) {
    var hadAnyError = false;
    $(':input', $(this)).each(function() {
      var input = $(this), name;
      if (name = input.attr('name')) {
        name = name.split('[');
        var i, target = rules;
        for (i = 0; i < name.length; i++) {
          if (!(target = target[name[i].replace(/\]$/, '')])) {
            break;
          }
        }
        if (target) {
          $.each(target, function(msg, regex) {
            if (!input.val().match(regex)) {
              hadAnyError = true;
              onInputError.call(input, msg);
              return false; // exit loop on first error for input
            }
          });
        }
      }
    });
    hadAnyError || onFormValidated.call(this);
    return this;
  };

}(window.jQuery));

