"use strict";

module.exports = function(sequelize, DataTypes) {
  var Card = sequelize.define("Card", {

    photo_url: DataTypes.STRING,
    audio_url: DataTypes.STRING,
    audio_parts: DataTypes.STRING,
    overlay : DataTypes.STRING,    
    video_url : DataTypes.STRING,
    uid: DataTypes.STRING,
    created_at: DataTypes.DATE,
    updated_at: DataTypes.DATE,
    caption : DataTypes.STRING,
    video_render_time: DataTypes.FLOAT,
    audio_render_time: DataTypes.FLOAT,
    status: DataTypes.STRING,

    //status values in DB
    //AUDIO_JOB_FAILED      = 100;
    //AUDIO_JOB_PENDING     = 110;
    //AUDIO_JOB_IN_PROGRESS = 120;
    //AUDIO_JOB_COMPLETED   = 150; 
    //VIDEO_JOB_FAILED      = 200;
    //VIDEO_JOB_PENDING     = 210;
    //VIDEO_JOB_IN_PROGRESS = 220;
    //VIDEO_JOB_COMPLETED   = 250;

    retries: DataTypes.INTEGER

  }, {
    classMethods: {
      
    }
  ,tableName: 'card'
	,underscored: true
  });

  return Card;
};
