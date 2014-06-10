/*
 * scripts for plugin
 */
(function ($) {
  $(document).ready(function(){
    $('#purge_method').click(function() {
      var wildcard = $('#ban-wildcard');

      if ($(this).val() === 'purge') {
        wildcard.css('visibility', 'hidden');
      } else if ($(this).val() === 'ban') {
        wildcard.css('visibility', 'visible');
      }
    });
  });

  $(document).ready(function(){
    $('input[name="purge_url"]').each(function() {
      var t = $(this).val();
      $(this).val('').focus().val(t);
    });
  });

  $(document).ready(function() {
    function selectAll() {
      $(this).select();
    }
    $('#node_name').click(selectAll);
    $('#node_host').click(selectAll);
  });
})(window.jQuery);