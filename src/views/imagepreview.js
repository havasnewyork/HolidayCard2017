var $ = require("jquery");
var cardConfig = require('../config/card.config');
var imageData = {};
var EXIF = require('exif-js');
var canvas,ctx;


function getOverlayId(){
	var overlayCount = $('#overlay').data('count');
	let overlayId = Math.floor(Math.random()*overlayCount) + 1;
	return overlayId;
}
function base64ToArrayBuffer (base64) {
    base64 = base64.replace(/^data\:([^\;]+)\;base64,/gmi, '');
    var binaryString = atob(base64);
    var len = binaryString.length;
    var bytes = new Uint8Array(len);
    for (var i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
}

export default {
		init: ()=> {
			canvas = document.getElementById('previewCanvas');
			ctx = canvas.getContext('2d');
			// cardConfig.config.overlayID = document.getElementById('overlay').dataset.id;
		},
		waiting: waiting,
		hideWaiting: hideWaiting,
		resetOverlay:()=>{
			$('#overlay').css('background-image',$('#overlay').data('preview'));
		},
		fullPreview: data =>{
			window.stopAudio();
	 		// playSong(data.audio_url);
			$('.imgPreview').show();
			$('.imgPreview .overlay').css('background-image','url(/images/overlays/overlay' + JSON.parse(data.overlay).id + '.gif)').show();
		},
		getImageData: file => {
			return new Promise(function (resolve, reject) {
			    loadImage(
			        file,
			        function (img,data) {
			        	// var orientation = data.exif.get('Orientation');
				        if(img.type === "error") {
				        	reject("ERROR LOADING IMAGE");
				        }else{
			        	$(img).attr({
			        		'id':'previewCanvas',
			        	});
			        	$('#previewCanvas').replaceWith(img);
					 	resolve(img);
			        	// console.log(orientation,img);
			            // document.body.appendChild(img);
				        }
				    },
				    {
				        maxWidth: 608,
				        maxHeight: 1080,
				        canvas: true,
				        orientation:true,
				        crop:true,
				        cover:true
				        } // Options
				 );
			});
		},
	 	showPreview:res=>{
			return new Promise((resolve, reject) => {
				canvas = res;
			  resolve({data:canvas.toDataURL("image/jpeg", .6)});
			});
	 	},
		uploadImage:(imageData)=>{
			imageData.overlayID = cardConfig.config.overlayID = getOverlayId();
			return $.ajax({
				url:'/api/upload',
				type: "POST",
				data: imageData,
			});
		},
		createCard: (data) => {
			return $.ajax({
				url:'/api/createcard',
				type: "POST",
				data: data
			});
		},
		createVideo: (data) => {
			console.log('createvideo',data);
			return $.ajax({
				url:'/api/createvideo/' +cardConfig.config.id,
				type: "POST"
			});
		},
		updateStatus: (status) => {
			// console.log('createvideo',data);
			return $.ajax({
				url:'/api/updateStatus/' +cardConfig.config.id + '/' + status,
				type: "POST"
			});
		},
    };

function playSong(url){
    var audioElement = document.createElement('audio');
    audioElement.setAttribute('src', url);
    audioElement.setAttribute('autoplay', 'autoplay');
    audioElement.addEventListener("load", function() {
    audioElement.play();
    }, true);
    audioElement.addEventListener("ended", function(){
	     audioElement.currentTime = 0;
	     console.log("AUDIO CLIP ENDED");
	     window.openLoveHatePanel();
	});
}

function waiting(status){
    window.showLoader(status);
}
function hideWaiting(status){
    window.hideLoader(true);
}
