"use strict";
    var visionPromise = function(buf){

	let vision = require('@google-cloud/vision'),
	    visionClient = vision({
	      projectId: 'holidaycard-186620',
	      keyFilename: './google-vision.json'
	    });
		var requestsElement = {
	        'image': {
	            'content': buf
	        },
	        'features': [{
	            'type': 'LABEL_DETECTION',	            
	            'maxResults': 10
	        },
	        {
	            'type': 'SAFE_SEARCH_DETECTION'           
	           
	        }

	        ]
	    }
    var requests = [requestsElement];

    	return new Promise((resolve, reject) => {
	    	visionClient.batchAnnotateImages({requests: requests}).then(function(responses) {
		    var response = responses[0];
		  	var isSafe = true;
		    var safeSearchResults = response.responses[0].safeSearchAnnotation;
			console.log(safeSearchResults);

			if ((safeSearchResults.adult == 'VERY_LIKELY') ||
				(safeSearchResults.spoof == 'VERY_LIKELY') ||
				(safeSearchResults.medical == 'VERY_LIKELY') ||
				(safeSearchResults.violence == 'VERY_LIKELY')||
				(safeSearchResults.adult == 'LIKELY') ||
				(safeSearchResults.spoof == 'LIKELY') ||
				(safeSearchResults.medical == 'LIKELY') ||
				(safeSearchResults.violence == 'LIKELY'))
		    	isSafe = false;



		    var labels = [];
		        response.responses[0].labelAnnotations.forEach((e)=>{
		            let label = {
		                'label':e.description,
		                'score':parseFloat(e.score)
		            }
		            labels.push(label);
		        })
		        //response.responses[0] = labels;
		        // console.log(labels);
		        resolve({labels:labels,isSafe:isSafe});
		        // res.send(response);
			})
			.catch(function(err) {
			    console.error('error',err);
			    reject('reject')
			});
		});
    }




exports.visionPromise = visionPromise;