"use strict";
const datamuse = require('datamuse');

function rhymes(text){
	var word = text;
	return new Promise((resolve,reject)=>{
		datamuse.request('words?rel_rhy=' + word + '&md=sp&qe=rel_rhy&max=50')
		.then((json) => {
			let rhymes = json;
			let filteredArray = rhymes.filter(obj => (obj.numSyllables > 1 && obj.numSyllables < 5 && obj.numSyllables ));
			if(filteredArray.length < 2){
				resolve(null);
			}else{
				rhymes = filteredArray;
				resolve({word:word,rhymes:filteredArray});
			}
		}).catch(err=>{
			reject(err);
		});
	});
}

exports.rhymes = rhymes;