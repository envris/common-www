/**
 * --------------------------------------------------------------------
 * jQuery collapsible plugin
 * Author: Scott Jehl, scott@filamentgroup.com
 * Copyright (c) 2009 Filament Group 
 * licensed under MIT (filamentgroup.com/examples/mit-license.txt)
 * --------------------------------------------------------------------
 */
 
$(function(){	
	$('*.details').collapsible();

}); 

$.fn.readLoad = function(){
/*		$(document).ready(function() {
          $('div.#instruction').html('<p>Click on the heading text to expand or collapse each item or use the links below.<br><a href="javascript:collapsible()" id="collapse-all">Collapse all</a> | <a href="#all" id="expand-all">Expand all</a> </p>');
   alert($('div.#instruction').html());
   
});*/

};



$.fn.collapsible = function(){
//add collase all event
    $('a#collapse-all').click(function(){
		//detect the heading tag
		var heading = jQuery.trim($('.message').html().toLowerCase()).charAt(1) + jQuery.trim($('.message').html().toLowerCase()).charAt(2);
		$(''+heading+'[class = details collapsible-heading]').trigger('collapse');				   
	});
//add expand all event	
	 $('a#expand-all').click(function(){
		 var heading = jQuery.trim($('.message').html().toLowerCase()).charAt(1) + jQuery.trim($('.message').html().toLowerCase()).charAt(2);
		$(''+heading+'[class = details collapsible-heading collapsible-heading-collapsed]').trigger('expand');
});

//add the event for opening certain deatail
   $("a[class~=clickExpand]").each(function (){
       $(this).click(function (){
		    var heading = jQuery.trim($('.message').html().toLowerCase()).charAt(1) + jQuery.trim($('.message').html().toLowerCase()).charAt(2);
			$(""+heading+"[id ='"+$(this).attr('class').split(' ')[1]+"']").trigger('expand');
		});

	});

	return $(this).each(function(){
								 
			//define
		var collapsibleHeading = $(this);
		var collapsibleContent = collapsibleHeading.next();
		var aName = $(this).text().replace(/\s+/g, '-').toLowerCase();
			//modify markup & attributes
		collapsibleHeading.addClass('collapsible-heading')
			.prepend('<span class="collapsible-heading-status"></span>')
			.wrapInner('<a href="#" id="'+aName+'" name="'+aName+'" class="collapsible-heading-toggle"></a>');
			
		collapsibleContent.addClass('collapsible-content');

		//events
		collapsibleHeading	
			.bind('collapse', function(){
				$(this)
					.addClass('collapsible-heading-collapsed')
					.find('.collapsible-heading-status').text('Show Details');
										
				collapsibleContent.slideUp(function(){
					$(this).addClass('collapsible-content-collapsed').removeAttr('style').attr('aria-hidden',true);
				});
			})
			.bind('expand', function(){
				$(this)
					.removeClass('collapsible-heading-collapsed')
					.find('.collapsible-heading-status').text('Hide Details');
										
				collapsibleContent
					.slideDown(function(){
						$(this).removeClass('collapsible-content-collapsed').removeAttr('style').attr('aria-hidden',false);
					});
			})
			.click(function(){ 
							
				if( $(this).is('.collapsible-heading-collapsed') ){
					$(this).trigger('expand'); 
				}	
				else {
					$(this).trigger('collapse'); 
				}
				return false;
			})
			.trigger('collapse');
		
	});	

};	