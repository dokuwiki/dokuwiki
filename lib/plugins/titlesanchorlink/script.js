jQuery(function ($) {
	$(':header').each(function(){
		var id = $(this).attr('id');
		if (!id) {
           		// https://github.com/Dric/dokuwiki-titlesanchorlink/issues/1
            		return;
            	}
		var name = $(this).text();
		$(this).append('<a title="Link to '+name+'" id="anchor__'+id+'" class="__anchor" href="#'+id+'"><img src="'+DOKU_BASE+'lib/plugins/titlesanchorlink/images/anchor.png" class="__anchor_icon" /></a>');
	});

  $(':header').mouseover(function() {
    var id = $(this).attr('id');
    $('#anchor__'+id).show();
  }).mouseout(function(){
    var id = $(this).attr('id');
    $('#anchor__'+id).hide();
  });
});
