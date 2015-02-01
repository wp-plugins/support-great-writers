// 
// $Id$
// 
// Copyright 2010-2014 - Loudlever, Inc.
// 
(function( sgw, $, undefined ) {
  /*
  ---------------------------------------------
      PRIVATE PROPERTIES
  ---------------------------------------------
  */
  
  var WIDGET_CNT = 0;
  var WIDGET_SET = {};

  /* 
  ---------------------------------------------
      PUBLIC FUNCTIONS
  --------------------------------------------- 
  */
  sgw.append_asin_block = function(val) {
    WIDGET_CNT++;  // this will increment each time this function is called, allowing us to create as many as we want
    if (val > 0) {
      var text = $('#sgw_add_new option[value="'+val+'"]').text();
      if (text.length > 40) {
        text = text.substring(0,40) + ' ...';
      }
      if (!WIDGET_SET[val]) {
        WIDGET_SET[val] = true;
        // append the content to the div
        var block = $('<br/><label class="sgw_label add_new" for="sgw_new['+val+']">'+ text +'</label><input type="text" name="sgw_opt[new]['+val+'][asin]" id="sgw_new_'+val+'" class="sgw_input" /><input type="hidden" name="sgw_opt[new]['+val+'][title]" value="'+text+'"/>');
        $('#newly_added_post_asins').append(block);
        
      } else {
        var txt = "#sgw_new_"+val;
        if (!$(txt).value) {
          $(txt).value = 'update me' 
        }
        $(txt).select();
      }
      $('sgw_add_new').value = 0;
    }
    return false;
  };
  
}( window.sgw = window.sgw || {}, jQuery ));
  