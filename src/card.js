import utils from "./js/utils";
import 'src/css/main.css';
import 'src/css/card.css';

// window.console.log = function() {};

var $ = require("jquery");

var lastW, lastH, lastS = 1;
function resize(isResp, respDim, isScale, scaleType, container){
	var container = $(container);
    var w = 608, h = 1080;
    var iw = window.innerWidth * .95, ih = window.innerHeight * .95;
    var pRatio = window.devicePixelRatio || 1, xRatio = iw / w, yRatio = ih / h, sRatio = 1;
    
            sRatio = Math.min(xRatio, yRatio);
    // container.width(w * pRatio * sRatio);
    // container.height(h * pRatio * sRatio);

    container.width(w * sRatio + 'px');
    container.height(h * sRatio + 'px');
}


function init(){

	  var instances = plyr.setup('#vidPlayer',{
	    showPosterOnEnd:true,
	    replay:false,
	    autoplay:true
	  });
	  var vidPlayer = instances[0];
	  vidPlayer.on('ready', function(event) {
		  	console.log("READY");
		  var instance = event.detail.plyr;
		}).on('play',event=>{
			$('.buttons.share').addClass('hide');
			console.log('play');
		}).on('ended',event=>{
			vidPlayer.stop();
			$('.buttons.share').removeClass('hide');
			console.log('ended');
		}).on('pause',event=>{
			$('.buttons.share').removeClass('hide');
			console.log('ended');
		});

	utils.overlayInit();
	// utils.videojs();
	utils.copyToClipoard();

	$('a.twitter').on('click', e =>{
		e.preventDefault();
		utils.shareTW(e);
		ga('send', 'event', 'share module', 'click', 'twitter');
	})
	$('a.facebook').on('click', e =>{
		e.preventDefault();
		utils.shareFB(e);
		ga('send', 'event', 'share module', 'click', 'facebook');
	})
	
	$('a.downlaod').on('click', e =>{
		ga('send', 'event', 'share module', 'click', 'downlaod');
		// e.preventDefault();
		// utils.checkAPP(e);
	})
	$('a.email').on('click', e =>{
		utils.mailTo();
		ga('send', 'event', 'share module', 'click', 'email');
	})
}

$(()=>{
	init();
	$(window).on('resize',()=>{
		resize(true, 'both', true, 1, '.imgPreview');
	}).resize();
});