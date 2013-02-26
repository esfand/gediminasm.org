(function ($) {

  var el;
  var postId;

  var outOfStock = false; // no more comments in database
  var limit = 10; // 10 comments per fetching
  var offset = 0;

  var commentSection;

  var validationRules = {
    'comment': {
      'subject': {
        'Subject should be specified': /.+/
      },
      'content': {
        'Comment should have body': /.+/
      }
    }
  };

  function drawComment(comment, topMost) {
    var scrolling = false;
    if (commentSection === undefined) {
      el.append('<h3>Comments</h3>');
      el.append(commentSection = $('<div class="row">'));
      // when scrolled to page bottom grab more comments
      $(window).scroll(function() {
        // load comments
        if (!scrolling && !outOfStock && $(window).scrollTop() + $(window).height() == $(document).height()) {
          scrolling = true;
          grabNextGroupOfComments(function(comments) {
            scrolling = false;
            $.each(comments, function() {
              drawComment(this);
            });
          });
        }
      });
    }
    var entry = $('<div class="row comment">').append(
      $('<div class="comment-title">').append(
        $('<span class="number">').append(comment.created),
        $('<span class="subject">').append(comment.subject),
        $('<span class="author">').append(comment.author),
        $('<span class="comment-reply">').append(
          $('<a href="#" class="reply">').append('[reply]').data('subject', comment.subject).on('click', function (e) {
            e.preventDefault();
            el.find('input[name="comment[subject]"]').val('Re: ' + $(this).data('subject')).focus();
          })
        )
      ),
      $('<div class="separator">'),
      $('<div class="comment-body">').append(comment.content)
    );
    if (topMost !== undefined) {
      commentSection.prepend(entry);
    } else {
      commentSection.append(entry);
    }
  };

  function grabNextGroupOfComments(callback) {
    var query = {
      "offset": offset,
      "limit": limit
    };
    var loader = $('<div class="row comment-loader loading">');
    el.append(loader);
    $.ajax({
      dataType: 'json',
      url: '/posts/' + postId + '/comments.json',
      data: query,
      success: function(comments) {
        offset += limit;
        outOfStock = comments.length !== limit;
        loader.remove();
        callback(comments);
      }
    });
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
      url: '/posts/' + postId + '/comment.json',
      data: $(':input', form),
      success: function(comment) {
        drawComment(comment, true);
        $(':input', form)
          .not(':button, :submit, :reset, :hidden')
          .val('')
          .removeAttr('checked')
          .removeAttr('selected')
        ;
        form.find('.alert')
          .removeClass('alert-error')
          .addClass('alert-success')
          .html('<p><strong>' + comment.subject + '</strong> comment was added.</p>')
          .show('slow')
        ;
      },
      error: function(json) {
        form.find('.alert')
          .removeClass('alert-succes')
          .addClass('alert-error')
          .html('<p><strong>Oops, server error, please try again later.</p>')
          .show('slow')
        ;
      }
    });
  };

  var onSubmit = function(e) {
    e.preventDefault();
    $(this).find('.control-group').removeClass('error');
    $(this).find('.help-inline').remove();
    $(this).validate_form(validationRules, onInputError, onValidated);
  };

  $.fn.commentable = function(id) {
    el = this;
    postId = id;
    grabNextGroupOfComments(function(comments) {
      $.each(comments, function() {
        drawComment(this);
      });
    });
    el.find('form').on('submit', onSubmit);
    return this;
  };

}(window.jQuery));

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

