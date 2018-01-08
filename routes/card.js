var express = require('express');
var router = express.Router();
var fs = require("fs-extra");
var models  = require('../models');
var MobileDetect = require('mobile-detect');

function getOverlayURL(id){
	var url = '/images/overlays/overlay' + id + '.gif';
	return url;
}
const awsBucket = 'https://s3.amazonaws.com/havas-holiday-2017/videos/';


router.get('/:id', function(req, res, next) {

	var md = new MobileDetect(req.headers['user-agent']);
	var isIOS = md.is('iPhone');
	var _uid = req.params.id;
	var protocol = 'https';
	if(req.headers['x-forwarded-proto'] && req.headers['x-forwarded-proto'] === "http")protocol='http';
	var rootURL = protocol + '://' + req.get('host')
	var fullURL = rootURL + '/card/' + _uid;
	
	var fullURL = rootURL + '/card/' + _uid;
	var vidURL = awsBucket + _uid + '.mp4';
	// if(isIOS)vidURL = 'r' + vidURL;
	if  ((_uid == null) ||  (_uid == '')) res.json('Id is required');

	models.Card.findOne({where: {uid:_uid}})
		.then((item)=>{
			if(item){
				  res.render('card', {
				  	title:"Holiday that Sh*t",
				  	imgURL:item.photo_url,
				  	overlayURL:getOverlayURL(JSON.parse(item.overlay).id),
				  	audioURL:item.audio_url,
				  	videoURL:vidURL,
				  	rootURL:rootURL,
				  	fullURL:fullURL,
				  	uid:item.uid,
				  	isIOS:isIOS,
				  	shareCopy:"Here's a little something to celebrate the joy and weirdness of the holiday season!"
				  });	
			  }else{
				res.redirect('/');
			  }						
		},(err) => {
			console.log("ERROR!",err);
			res.send("NO DB CONNECTION");
		});

});

router.get('/share/:id', function(req, res, next) {
	var md = new MobileDetect(req.headers['user-agent']);
	var isIOS = md.is('iPhone');
	var _uid = req.params.id;
	var protocol = 'https';
	if(req.headers['x-forwarded-proto'] && req.headers['x-forwarded-proto'] === "http")protocol='http';
	var rootURL = protocol + '://' + req.get('host');
	var fullURL = rootURL + '/card/' + _uid;
	var vidURL = awsBucket + _uid + '.mp4';
	// if(isIOS)vidURL = 'r' + vidURL;
	if  ((_uid == null) ||  (_uid == '')) res.json('Id is required');
	models.Card.findOne({where: {uid:_uid}})
		.then((item)=>{
			if(item){
				// if(!item.video_url)res.redirect('/');
				// console.log("ITEM",item.video_url);	
				  res.render('partials/share', {
				  	imgURL:item.photo_url,
				  	audioURL:item.audio_url,
				  	videoURL:vidURL,
				  	fullURL:fullURL,
				  	isIOS:isIOS,
				  	title:"Holiday that Sh*t",
				  	shareCopy:"Here's a little something to celebrate the joy and weirdness of the holiday season!"
				  });	
			  }else{
				res.send({error:true,message:'id does not exist'});
			  }						
		},(err) => {
			console.log("ERROR!",err);
			res.send("NO DB CONNECTION");
		});
});

router.get('/player/:id', function(req, res, next) {
	var _uid = req.params.id;
	if  ((_uid == null) ||  (_uid == '')) res.json('Id is required');
	models.Card.findOne({where: {uid:_uid}})
		.then((item)=>{
			if(item){
				  res.render('./twitterPlayer', {
				  	videoURL:awsBucket + _uid + '.mp4'
				  });	
			  }else{
				res.send({error:true,message:'id does not exist'});
			  }						
		},(err) => {
			console.log("ERROR!",err);
			res.send("NO DB CONNECTION");
		});
});

module.exports = router;
