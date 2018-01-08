"use strict";

let mary = require('../services/marytts')('localhost', 59125);

let fs = require("fs-extra");

function createAudio(req, res, next) {
    let voice = req.body.voice || "cmu-bdl";
    let locale = req.body.locale || "en_US";
    let word = req.body.clip1;
    let rhyme = req.body.clip2;
	let siteDir = '/tmp/audio/';
	let fileDir = 'public' + siteDir;
	let Promises = [];
	[word,rhyme].forEach(clip=>{
	let fileName = clip.replace(/ /g, "_") + '_' +voice + '.wav';
	let fileDest = fileDir + fileName;
	let siteDest = siteDir + fileName;

		Promises.push(
			new Promise((resolve, reject) => {
				mary.process(clip,{
		        voice:voice,
		        locale:locale
		    }, audio => {
				fs.ensureDir(fileDir)
				.then(() => {
					fs.writeFile(fileDest, audio, function(err) {
						resolve({
							url:siteDest,
							word: clip
						})
					});
				})
				.catch(err => {
				  console.error(err)
				})

		    });
		}));
	})
	return Promise.all(Promises)
}


exports.createAudio = createAudio;