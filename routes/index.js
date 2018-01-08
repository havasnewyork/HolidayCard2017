"use strict";

var express = require('express');
var router = express.Router();
var glob = require( 'glob' );  
var MobileDetect = require('mobile-detect');


var mary = require('../services/marytts')('localhost', 59125);

let overlayCount = 1;

var overlayDir= "public/images/overlays/*.gif";
glob(overlayDir, function (er, files) {
	overlayCount = files.length;
})

/* GET home page. */
router.get('/', function(req, res, next) {
		//  check for Android Browser
	var md = new MobileDetect(req.headers['user-agent']);
	var isAndroid = md.is('AndroidOS');
	var bodyClass = isAndroid ? '' : 'moveBg';

	var protocol = 'https';
	if(req.headers['x-forwarded-proto'] && req.headers['x-forwarded-proto'] === "http")protocol='http';
	var rootURL = protocol + '://' + req.get('host');

	mary.voices(function(voices){
		if(voices.error)console.log(voices);
	  res.render('index', {
	  	title: 'Holiday that Sh*t',
	  	voices: voices,
	  	rootURL:rootURL,
	  	overlayCount:overlayCount,
	  	isAndroid:isAndroid,
	  	bodyClass:bodyClass
	  });
	});
});

/* GET home page. */
router.get('/imageUpload', function(req, res, next) {
	mary.voices(function(voices){
		if(voices.error)console.log(voices);
	  res.render('imageUpload', {
	  	title: 'Upload Image and get Vision Results',
	  	voices: voices
	  });
	});
});


module.exports = router;
