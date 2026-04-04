/***************************/
//@Author: Adrian "yEnS" Mato Gondelle
//@website: www.yensdesign.com
//@email: yensamg@gmail.com
//@license: Feel free to use it, but keep this credits please!
/***************************/

var popupStatus = 0;

function loadPopup() {
	if (popupStatus == 0) {
		jQuery("#backgroundPopup").css({
			opacity: "0.7",
			"background-color": "#FAFAFA"
		});
		jQuery("#backgroundPopup").fadeIn("slow");
		jQuery("#panelInner").fadeIn("slow");
		popupStatus = 1;
	}
}

function disablePopup() {
	if (popupStatus == 1) {
		jQuery("#backgroundPopup").fadeOut("slow");
		jQuery("#panelInner").fadeOut("slow");
		popupStatus = 0;
	}
}

function centerPopup() {
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = jQuery("#panelInner").height();
	var popupWidth = jQuery("#panelInner").width();
	jQuery("#panelInner").css({
		position: "absolute",
		top: windowHeight / 2 - popupHeight / 2,
		left: windowWidth / 2 - popupWidth / 2
	});
	jQuery("#backgroundPopup").css({
		height: windowHeight
	});
}

jQuery(document).ready(function() {
	jQuery("#open").click(function() {
		centerPopup();
		loadPopup();
	});
	jQuery(".close").click(function() {
		disablePopup();
	});
	jQuery("#backgroundPopup").click(function() {
		disablePopup();
	});
	jQuery(document).keypress(function(e) {
		if (e.keyCode == 27 && popupStatus == 1) {
			disablePopup();
		}
	});
});
