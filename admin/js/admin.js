jQuery(document).ready( function($) {

//Change default behavior and bound to ajax action
$('.woocommerce-page form#post').on('submit', function(e){
  e.preventDefault();
  
  //serialize data
  var formData = new FormData();
  var formData = $(this).serialize();

  /* Add preload status */
  $('#major-publishing-actions input[type="submit"]').attr('class', 'submit-btn ajax-loading').attr('disabled', true);

    // request
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        action     : 'save_post',
        formData   : formData,
      },
      success:function(status){
        status = $.parseJSON( status );
        setTimeout(() => { // set delay for see a very beautiful animation :-)
          $('#major-publishing-actions input[type="submit"]').removeClass('ajax-loading').attr('disabled', false).attr('value', status.btn); // active button, remove loading animation and insert new button value
          $('#post-status-display').html(status.status); // return "publiched" status
          wp.data.dispatch("core/notices").createNotice( // push snackbar alert of success
            "success",
            status.success_msg, // return success status
            {
              type: "snackbar",
              isDismissible: true,
            }
          );
        }, 500);
      },
      error: function(err){
        alert('Что-то пошло не так :-(');
      }
    });
  });

  // Change default behavior preview button
  $('body').on('click', '.woocommerce-page #post-preview', function(e){
    e.preventDefault();
    var href = $(this).attr('href');
    var win = window.open(href, '_blank');
    if (win) {
        //Browser has allowed it to be opened
        win.focus();
    } else {
        //Browser has blocked it
        alert('Please allow popups for this website');
    }
  })
})