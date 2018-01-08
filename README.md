# Havas Holiday Card 2017

## Running Locally

[MaryTTS](https://github.com/marytts/marytts) needs to be running on default port (59125)  
npm install - get node_modules  
npm start - runs webpack watch and nodemon ( webpack and nodemon may need to be installed globally)  
npm run build - compiles front end code  ( webpack may need to be installed globally)  
node bin/www - starts express server


## FFMPEG Notes

time ffmpeg -hide_banner -threads 16 -loop 1 -i B10UTQ_-G.jpg -ignore_loop 0 -i overlay4.gif -i B10UTQ_-G.mp3 -filter_complex "[0:v]scale=608:1080[photo_out]; [1]scale=-1:1080:flags=neighbor[video_tmp1],[photo_out][video_tmp1]overlay=main_w/2-overlay_w/2:main_h/2-overlay_h/2[video_out1] " -c:v libx264 -preset faster -crf 25 -c:a aac -ac 1 -b:a 64k -map "[video_out1]" -map "2:a" -t 19 -y B10UTQ_-G.mp4

time ffmpeg -hide_banner -loop 1 -i /opt/assets/photos/images/B10UTQ_-G.jpg -ignore_loop 0 -i /opt/assets/overlays/overlay3.gif -i /opt/holiday-2017/public/tmp/merged_audio/B10UTQ_-G.mp3 -filter_complex "[0:v]scale=608:1080[photo_out]; [1]scale=-1:1080:flags=neighbor[video_tmp1],[photo_out][video_tmp1]overlay=main_w/2-overlay_w/2:main_h/2-overlay_h/2[video_out1] " -c:v libx264 -preset faster -crf 25 -c:a aac -ac 1 -b:a 64k -map "[video_out1]" -map "2:a" -t 19 -r 10 -y /opt/assets/videos/B10UTQ_-G.mp4