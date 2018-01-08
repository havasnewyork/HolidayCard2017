"use strict";

var AWS = require('aws-sdk');
// var S3 = require('aws-sdk/clients/s3');
AWS.config.loadFromPath('./aws.config.json');
let albumBucket = "havas-holiday-2017";
const awsURL = 'https://s3.amazonaws.com/havas-holiday-2017/';

var s3 = new AWS.S3({
  apiVersion: '2006-03-01',
  params: {Bucket: albumBucket}
});


var s3audio = function(buf,filename){
	let filePath = 'audio/' + filename + '.wav';
	let s3Obj = {
		Key: filePath, 
		Body: buf,
		ContentEncoding: 'base64',
		ContentType: 'audio/wav'
	};

	return new Promise((resolve, reject) => {
	  s3.putObject(s3Obj, function(err, data){
	      if (err) { 
	        console.log(err);
	        console.log('Error uploading data: ', data); 
	        reject('reject');
	      } else {
	        console.log('succesfully uploaded the audio!',filePath);
	        resolve(filePath);
	      }
	  });
	});
}

var s3image = function(buf,imgName){
	let filePath = 'images/' + imgName + '.jpg';
	let s3Obj = {
		Key: filePath, 
		Body: buf,
		ContentEncoding: 'base64',
		ContentType: 'image/png'
	};

	return new Promise((resolve, reject) => {
	  s3.putObject(s3Obj, function(err, data){
	      if (err) { 
	        console.log(err);
	        console.log('Error uploading data: ', data); 
	        reject('reject');
	      } else {
	        console.log('succesfully uploaded the image!');
	        resolve({
	        	imgURL:awsURL + filePath,
	        	id:imgName
	        });
	      }
	  });
	});
}

exports.s3audio = s3audio;
exports.s3image = s3image;