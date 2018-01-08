'use strict';

let Status = {
	AUDIO_JOB_FAILED:100,
    AUDIO_JOB_PENDING:110,
    AUDIO_JOB_IN_PROGRESS:120,
    AUDIO_JOB_COMPLETED:150,
    VIDEO_JOB_FAILED:200,
    VIDEO_JOB_PENDING:210,
    VIDEO_JOB_IN_PROGRESS:220,
    VIDEO_JOB_COMPLETED:250
};
let readable = {};
for (var key in Status) {
    if (Status.hasOwnProperty(key)) {
        readable[Status[key]] = key;
    }
}


exports.Status = Status;
exports.readable = readable;