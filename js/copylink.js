(function( $ ){
	$.fn.myfunction = function() {
		 return new ZeroClipboard(this);
	};
})( jQuery );

$(document).ready(function(){
	var buttons = $("body").find(".copylink");
	console.log(buttons);

	buttons.on( "ready", function( readyEvent ) {
	  // alert( "ZeroClipboard SWF is ready!" );

	  buttons.on( "aftercopy", function( event ) {
	    // `this` === `client`
	    // `event.target` === the element that was clicked
	    event.target.style.display = "none";
	    alert("Copied text to clipboard: " + event.data["text/plain"] );
	  } );
	} );
});