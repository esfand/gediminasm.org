(function ($) {

  var el;
  var postId;

  var finished = false; // no more comments in database
  var limit = 10; // 10 comments per fetching
  var offset = 0;

  function drawComment(comment) {

  };

  function grabNextGroupOfComments(onAny) {
    var query = {
      "offset": offset,
      "limit": limit
    };
    $.ajax({
      dataType: 'json',
      url: '/posts/' + postId + '/comments',
      data: query,
      success: function(comments) {
        console.log('got comments', comments);
        offset += limit;
        finished = comments.length !== limit;
        onAny(comments);
      }
    });
  };

  function addComment() {

  };

  $.fn.comments = function() {
    el = this;
    postId = el.find('input[name="post_id"]').val();
    grabNextGroupOfComments(function(comments) {
      console.log(comments);
    });
    el.find('form').on('submit', addComment);
    return this;
  };

}(window.jQuery));
