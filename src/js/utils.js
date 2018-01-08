var $ = require("jquery");

var Clipboard = require('clipboard');


function copyToClipoard(btn){
		var btn = btn || '.btn'
		var clipboard = new Clipboard(btn);
		clipboard.on('success', function(e) {
		console.info('Action:', e.action);
		console.info('Text:', e.text);
		console.info('Trigger:', e.trigger);
		ga('send', 'event', 'share module', 'click', 'copy url');
		e.clearSelection();
	});
		clipboard.on('error', function(e) {
		console.error('Action:', e.action);
		console.error('Trigger:', e.trigger);
	});
}

function checkAPP(el){
	var url = el.currentTarget.href;
	console.log("TESTING",url);
	$.get(url).then((res,err)=>{

		console.log("GET URL",res,err)

	},(err,stat)=>{
		console.log("ERROR",err,stat);
	});


};

function sharePopup(e){
	console.log(e.currentTarget);
	window.open(e.currentTarget.href, 'ShareWindow', 'height=450, width=550, top=' + ($(window).height() / 2 - 275) + ', left=' + ($(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');

}

function mailTo(){
	 window.location.href = "mailto:?subject=Holiday that Sh*t&body=" + window.location.href;
}

function shareFB(e){
	sharePopup(e);
	 // window.location.href = "mailto:?subject=Holiday that Sh*t&body=" + window.location.href;
}

function shareTW(e){
	sharePopup(e);
	 // window.location.href = "mailto:?subject=Holiday that Sh*t&body=" + window.location.href;
}
function copyShareUrl(el){
	var url = el.currentTarget.href;
	var UrlText = document.getElementById("shareurl");
	  if(!UrlText){
	  	 UrlText = document.createElement("textarea");
		  UrlText.id = "shareurl";
		  document.body.appendChild(UrlText);
	  }
	  UrlText.innerHTML = window.location.href;
	  UrlText.select();
	  document.execCommand("copy");
}
function getShareModule(id){
	return $.get('/card/share/' + id);
}

function toggleOverlay(el){
	el.preventDefault();
	console.log(el.currentTarget);
	var el = $(el.currentTarget);
	console.log(el,el.hasClass('close'));
	if(el.hasClass('close')){
		ga('send', 'event', 'info module', 'click', 'close');
		el.removeClass('close');
		$('.infoOverlay').hide();
	}else{
		ga('send', 'event', 'info module', 'click', 'open');
		el.addClass('close');
		$('.infoOverlay').show();
	}
}


function overlayInit(){
	$('.infoBtn').on('click',toggleOverlay);
}

function videoJSInit(id){
		console.log('READY');
	var id = id || "my-video";
	videojs(id).ready(function(){
		console.log('READY');
	  var myPlayer = this;
	  myPlayer.on("ended", function(){
	  	console.log("ENDED");
	    myPlayer.bigPlayButton.show();
	  });
	});
}

export default {
	copyShareUrl:copyShareUrl,
	mailTo:mailTo,
	shareFB:shareFB,
	shareTW:shareTW,
	toggleOverlay:toggleOverlay,
	overlayInit:overlayInit,
	getShareModule:getShareModule,
	copyToClipoard,copyToClipoard,
	videoJSInit,videoJSInit,
	checkAPP:checkAPP
}