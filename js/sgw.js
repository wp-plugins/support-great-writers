// 
// $Id$
// 
// Copyright 2010 - Loudlever, Inc.
// 
var cnt = 0;
var existing = {};
Element.addMethods("SELECT", (function() {
    function getSelectedOptionHTML(element) {
        if (!(element = $(element))) return;
        var index = element.selectedIndex;
        return index >= 0 ? element.options[index].innerHTML : undefined;
    }

    return {
        getSelectedOptionHTML: getSelectedOptionHTML
    };
})());


function AddDynamicChild(val) {
  cnt++;  // this will increment each time this function is called, allowing us to create as many as we want
  if (val > 0) {
    var text = $('sgw_add_new').getSelectedOptionHTML();
    if (text.length > 30) {
      text = text.substring(0,30) + ' ...';
    }
    if (!existing[val]) {
      existing[val] = true;
      // append the content to the div
      $('newly_added_post_asins').insert('<br/><label class="sgw_label" for="sgw_new['+val+']">'+ text +'</label><input type="text" name="sgw_opt[new]['+val+'][asin]" id="sgw_new_'+val+'" class="sgw_input" /><input type="hidden" name="sgw_opt[new]['+val+'][title]" value="'+text+'"/>');
    } else {
      alert('"'+text+'" is already listed below.');
      var foo = "sgw_new_"+val;
      if (!$(foo).value) {
        $(foo).value = 'update me' 
      }
      $(foo).select();
    }
    $('sgw_add_new').value = 0;
  }
  
  return false;
}
  
  