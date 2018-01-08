"use strict";

var express = require('express');
var router = express.Router();
var models  = require('../models');
var shortId = require('shortid');
var fs = require("fs-extra");
var vision = require('../services/googleVision');
var s3 = require('../services/s3upload');
var rhymes = require('../services/rhymes');
var mary = require('../services/marytts')('localhost', 59125);
var audio = require('../services/audio');

var shortId = require('shortid');
var audio_config = require('../config/audio.config');
var uniqid = require('uniqid');


var shuffle = require('knuth-shuffle').knuthShuffle;


router.post('/createcard', function(req, res, next) {
	var overlay = req.body.overlayID;
	var userID = req.body.userID;
	var photo_url = req.body.imgURL;

	// let overlay = {"id": Math.floor(Math.random()*overlayNum) + 1,"type":"covered_scaled"};
	let audio_parts = shuffle(audio_config.slice(0))[0];
	audio.createAudio(req, res, next).then(values=>{
		audio_parts.voices[0].url = values[0].url;
		audio_parts.voices[0].word = values[0].word.toUpperCase();
		if(audio_parts.voices[1]){
			audio_parts.voices[1].url = values[1].url;
			audio_parts.voices[1].word = values[1].word.toUpperCase();
		}
		models.Card.create({
			photo_url:photo_url,
			caption:'test', 
			audio_url:'', 
			video_url:'',
			uid:userID,
			status:110,//pending, completed, processing
			created_at:new Date(),
			updated_at:new Date(),
			audio_parts:JSON.stringify(audio_parts), 
			overlay:JSON.stringify({id:overlay, type:'covered_scaled'})
			})
			.then(function(newitem){
				res.json(newitem);								
			}); 
	})
});

router.get('/cardstatus/:id', function(req, res, next) {
	var _uid = req.params.id;
	// _uid ='SyG_4vEZz';
	if  ((_uid == null) ||  (_uid == '')) res.json('Id is required');
	models.Card.findOne({where: {uid:_uid}})
		.then(function(item){
			res.json(item);								
		}); 
});

router.post('/createvideo/:id', function(req, res, next) {
	var _uid = req.params.id;
	// _uid ='SyG_4vEZz';
	if  ((_uid == null) ||  (_uid == '')) res.json({status:'failed', message:'Id is required'});
	var found = models.Card.findOne({where: {uid:_uid}}); 

	found.then(item=>{
		if (item != null)
			models.Card.update({status:210}, {where:{uid:_uid}}).then(data=>{
				res.json({status:'success'});
			});
		else
			res.json({status:'failed', message:'card not found'});
	});

		
});

router.post('/updateStatus/:id/:status', function(req, res, next) {
	var _uid = req.params.id;
	var status = req.params.status;
	// _uid ='SyG_4vEZz';
	if  ((_uid == null) ||  (_uid == '')) res.json({status:'failed', message:'Id is required'});
	var found = models.Card.findOne({where: {uid:_uid}}); 

	found.then(item=>{
		if (item != null)
			models.Card.update({status:status}, {where:{uid:_uid}}).then(data=>{
				res.json({status:'success'});
			});
		else
			res.json({status:'failed', message:'card not found'});
	});
});

router.get('/', function(req, res, next) {
	res.redirect('/');
})



router.post('/upload', function(req, res) {
	let id =  shortId.generate();


	let buf = new Buffer(req.body.data.replace(/^data:image\/\w+;base64,/, ""),'base64');
	console.log('BUFFER LENGTH:', buf.byteLength )
	var s3Upload = s3.s3image(buf,id);

 	vision.visionPromise(buf).then(data=>{
 		var Promises = [s3Upload];
 		data.labels.forEach(e => {
 			var word = e.label;
			Promises.push(
				rhymes.rhymes(word)
			);
 		});

		Promise.all(Promises).then(values=>{
 		var imgURL = values[0].imgURL;


 		values.splice(0, 1);// remvoe image data from array

		let filteredArray = values.filter(obj => (obj)); //remove null objects from array
		let word = filteredArray[0];

		let firstWord = filteredArray[0];
		let fisrstRhyme = shuffle(firstWord.rhymes.slice(0))[0];
		let randomWord = shuffle(filteredArray.slice(0))[0];
		let randomRhyme = shuffle(randomWord.rhymes.slice(0))[0];
		
		if(filteredArray.length < 1)resolve(null);
			res.send({
				id:id,
				imgURL:imgURL,
				data:filteredArray,
				word:firstWord.word,
				rhyme:fisrstRhyme.word,
				rndWord:randomWord.word,
				rndRhyme:randomRhyme.word,
				isSafe: data.isSafe
			});
		}).catch(err=>{
			res.sendStatus(500);
			console.log("ERROR=",err);
		});
 	});
});

router.post('/googleVision', function(req, res) {
	let buf = new Buffer(req.body.data.replace(/^data:image\/\w+;base64,/, ""),'base64')
	vision.visionPromise(buf).then(labels=>{
		res.send({responses:labels});
	});
});

router.get('/rhyme/:word', (req, res, next) => {
	var word = req.params.word;
	rhymes.rhymes(word).then((response) => {
		res.send(response);
		});
});

router.get('/speak/:text',(req,res,next)=>{
    var text = req.params.text;

    mary.process(text,{
        // voice:voice,
        // locale:locale
    }, audio => {
		        res.type('audio/wav');
		        res.send(audio);
    });
})

module.exports = router;
