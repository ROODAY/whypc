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

function toggleMenu() {
	if(!menuOn) {
		$("#links").css("margin-left", "75vw");
		$("main").css("margin-right", "20vw");
		$(".menu-global").css("border-top", "7px solid #fff");
		menuOn = true;
	} else {
		$("#links").css("margin-left", "100vw");
		$("main").css("margin-right", "0");
		$(".menu-global").css("border-top", "7px solid #333");
		menuOn = false;
	}
}

function showNav() {
    if ($(window).scrollTop() >= $(window).height()) {
        $('.navtool').stop().animate({"opacity": '1.0'});
    } else {
        $('.navtool').stop().animate({"opacity": '0.0'});
    }
}
$(document).ready(function(){
	$("#coverheader").lettering();
	toggleMenu();

	$(window).scroll(function () {
		showNav();
	});

	$(".menu").click(function() {
		toggleMenu();
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
					return false;
				}
			}
		});
	});
});