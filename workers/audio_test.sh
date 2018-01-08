#!/usr/bin/env bash

ffmpeg -hide_banner -i assets/background_audio/music-and-voice.mp3 \
    -itsoffset 00:00:5.5 -i assets/voices/wallpaper.wav \
    -itsoffset 00:00:12.85 -i assets/voices/wrapper.wav \
    -filter_complex "[0]aformat=sample_fmts=fltp:sample_rates=44100:channel_layouts=stereo,volume=1:precision=fixed[a0]; \
        [1]aformat=sample_fmts=fltp:sample_rates=16000:channel_layouts=mono,volume=6:precision=fixed[a1]; \
        [2]aformat=sample_fmts=fltp:sample_rates=16000:channel_layouts=mono,volume=6:precision=fixed[a2]; \
        [a0][a1][a2]amix=inputs=3:duration=longest[a]" \
    -map "[a]" \
    -async 1 \
    -c:a aac -ac 1 -b:a 64k \
    -y assets/merged_audio/audio_test.m4a

#    -filter_complex "amix=inputs=3:duration=longest" \


#ffmpeg -i input \
#    -filter_complex \
#"[0:a:0]volume=0.3:precision=fixed[a0]; \
# [0:a:1]volume=0.5:precision=fixed[a1]; \
# [0:a:2]volume=0.7:precision=fixed[a2]; \
# [a0][a1][a2]amerge=inputs=3,pan=stereo:FL<c0+c2+c4:FR<c1+c3+c5[a]" \
#-map 0:v -map "[a]" -c:v libx264 -preset slow -crf 23 output.mp4