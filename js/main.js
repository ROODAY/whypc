$(window).load(function() {
	setTimeout(function() {
		$("#loading").fadeOut('slow');
	}, 1500);
});

var menuOn = true;

var Menu = {
  
  el: {
    ham: $('.menu'),
    menuTop: $('.menu-top'),
    menuMiddle: $('.menu-middle'),
    menuBottom: $('.menu-bottom')
  },
  
  init: function() {
    Menu.bindUIactions();
  },
  
  bindUIactions: function() {
    Menu.el.ham
        .on(
          'click',
        function(event) {
        Menu.activateMenu(event);
        event.preventDefault();
      }
    );
  },
  
  activateMenu: function() {
    Menu.el.menuTop.toggleClass('menu-top-click');
    Menu.el.menuMiddle.toggleClass('menu-middle-click');
    Menu.el.menuBottom.toggleClass('menu-bottom-click'); 
  }
};

Menu.init();

var currentSect = "#sect1";
function toggleMenu() {
	if(!menuOn) {
		$("#links").css("margin-left", "75vw");
		$("main").css("margin-right", "20vw");
		$(".menu-global").css("border-top", "7px solid #fff");
		$(".navarrow").css("color", "#fff");
		$("#navfix").css({
			"-webkit-transform": 'rotate(0deg)',
			"-moz-transform": 'rotate(0deg)',
			"-o-transform": 'rotate(0deg)',
			"-ms-transform": 'rotate(0deg)',
			"transform": 'rotate(0deg)'
		});
		$("#navfix").addClass('animated');
		$("#navfix").addClass('rubberBand');
		$("#navfix").one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
			$("#navfix").removeClass('animated');
			$("#navfix").removeClass('rubberBand');
		});
		menuOn = true;
	} else {
		$("#links").css("margin-left", "100vw");
		$("main").css("margin-right", "0");
		$(".menu-global").css("border-top", "7px solid #333");
		$(".navarrow").css("color", "#333");
		$("#navfix").css({
			"-webkit-transform": 'rotate(0deg)',
			"-moz-transform": 'rotate(0deg)',
			"-o-transform": 'rotate(0deg)',
			"-ms-transform": 'rotate(0deg)',
			"transform": 'rotate(0deg)'
		});
		$("#navfix").addClass('animated');
		$("#navfix").addClass('rubberBand');
		$("#navfix").one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
			$("#navfix").removeClass('animated');
			$("#navfix").removeClass('rubberBand');
		});
		menuOn = false;
	}
}

function showNav() {
    if ($(window).scrollTop() >= $(window).height()) {
        $('.navtool').stop().animate({"opacity": '1.0'});
    } else {
        $('.navtool').stop().animate({"opacity": '0.0'});
    }
    if ($(window).scrollTop() >= 3 * $(window).height()) {
        $('.navtool2').stop().animate({"opacity": '1.0'});
    } else {
        $('.navtool2').stop().animate({"opacity": '0.0'});
    }
}
$(document).ready(function(){
	$("#coverheader").lettering();
	toggleMenu();
	$("#switchpage").tooltip();
	$(".navtool").tooltip();
	$(".navtool2").tooltip();
	$("main").scrollspy({ target: '#listentries' })
	/*for (var i = 1; i <= 7; i++) {
		var object = ".char" + i;
		$(object).addClass('animated');
		$(object).addClass('bounce');
		$(object).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
			$(object).removeClass('animated');
			$(object).removeClass('bounce');
		});
	};*/

	var lastScrollTop = 0;
	$(window).scroll(function(event){
		showNav();
	   	var st = $(this).scrollTop();
	   	if (st > lastScrollTop){
	    	if ($(window).scrollTop() > $(currentSect).next().offset().top) {
	    		currentSect = $(currentSect).next();
	    	}
	    } else {
	       if ($(window).scrollTop() < $(currentSect).prev().offset().top) {
	    		currentSect = $(currentSect).prev();
	    	}
	    }
	    lastScrollTop = st;
	});

	$(".menu").click(function() {
		toggleMenu();
	});

	$("#switchpage").mouseenter(function() {
		$(this).css("opacity", "1.0");
	});
	$("#switchpage").mouseleave(function() {
		$(this).css("opacity", "0.25");
	});

	$(function() {
		$('a[href*=#]:not([href=#])').click(function() {
			if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
				var target = $(this.hash);
				target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
				if (target.length) {
					$('html,body').animate({
					scrollTop: target.offset().top
					}, 1000);
					var tester = target.selector.slice(1,5);
					if(tester === "sect") {
						currentSect = target.selector;
					};
					return false;
				}
			}
		});
		$("#navup").click(function() {
			if($(currentSect).prev() !== undefined) {
				$('html,body').animate({
				scrollTop: $(currentSect).prev().offset().top
				}, 1000);
				currentSect = $(currentSect).prev();
			}
		});
		$("#navdown").click(function() {
			if($(currentSect).next() !== undefined) {
				$('html,body').animate({
				scrollTop: $(currentSect).next().offset().top
				}, 1000);
				currentSect = $(currentSect).next();
			}
		});
		$("#navfix").click(function() {
			$('html,body').animate({
			scrollTop: $(currentSect).offset().top
			}, 1000);
			$("#navfix").css({
				"-webkit-transform": 'rotate(360deg)',
				"-moz-transform": 'rotate(360deg)',
				"-o-transform": 'rotate(360deg)',
				"-ms-transform": 'rotate(360deg)',
				"transform": 'rotate(360deg)'
			});
		});
	});
});
