$(document).ready(function() {
	/*var tapcount = 0;
	var myElement = document.getElementById('cover');
	var mc = new Hammer(myElement);
	mc.get('pinch').set({ enable: true });
	mc.get('rotate').set({ enable: true });
	var tap = new Hammer.Tap();
	var doubleTap = new Hammer.Tap({event: 'doubleTap', taps: 2 });
	var tripleTap = new Hammer.Tap({event: 'tripleTap', taps: 3 });

	mc.add([tripleTap, doubleTap, tap]);

	tripleTap.recognizeWith([doubleTap, tap]);
	doubleTap.recognizeWith(tap);

	doubleTap.requireFailure(tripleTap);
	tap.requireFailure([tripleTap, doubleTap]);

	mc.on('pan', function(ev) {
		console.log(ev)
	});
	mc.on('tripleTap', function(ev) {
		tapcount += 1;
		console.log("tapped");
	});
	if(tapcount >= 3) {
		$("#easter").css("margin-top", "0");
	};*/

	var listener = new window.keypress.Listener();
	listener.sequence_combo("up up down down left right left right b a enter", function() {
	    $("#easter").css("margin-top", "0");
	}, true);
	listener.sequence_combo("p c m a s t e r r a c e", function() {
	    $("#easter").css("margin-top", "0");
	});
});