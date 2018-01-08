window.exportRoot = {};
window.stage = {};
var canvas, anim_container, dom_overlay_container, fnStartAnimation;
function init() {
    canvas = document.getElementById("canvas");
    anim_container = document.getElementById("animation_container");
    dom_overlay_container = document.getElementById("dom_overlay_container");
    var comp = AdobeAn.getComposition("D71954212C704D56B775165777A3BA0F");
    var lib = comp.getLibrary();
    var loader = new createjs.LoadQueue(false);
    loader.installPlugin(createjs.Sound);
    loader.addEventListener("fileload", function (evt) { handleFileLoad(evt, comp) });
    loader.addEventListener("complete", function (evt) { handleComplete(evt, comp) });
    var lib = comp.getLibrary();
    loader.loadManifest(lib.properties.manifest);
}
function handleFileLoad(evt, comp) {
    var images = comp.getImages();
    if (evt && (evt.item.type == "image")) { images[evt.item.id] = evt.result; }
}
function handleComplete(evt, comp) {
    //This function is always called, irrespective of the content. You can use the variable "stage" after it is created in token create_stage.
    var lib = comp.getLibrary();
    var ss = comp.getSpriteSheet();
    var queue = evt.target;
    var ssMetadata = lib.ssMetadata;
    for (var i = 0; i < ssMetadata.length; i++) {
        ss[ssMetadata[i].name] = new createjs.SpriteSheet({ "images": [queue.getResult(ssMetadata[i].name)], "frames": ssMetadata[i].frames })
    }
    exportRoot = new lib.animation();
    stage = new lib.Stage(canvas);
    stage.enableMouseOver();
    //Registers the "tick" event listener.
    fnStartAnimation = function () {
        stage.addChild(exportRoot);
        createjs.Ticker.setFPS(lib.properties.fps);
        createjs.Ticker.addEventListener("tick", stage);
    }
    //Code to support hidpi screens and responsive scaling.
    function makeResponsive(isResp, respDim, isScale, scaleType) {
        var lastW, lastH, lastS = 1;
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();
        function resizeCanvas() {
            var w = lib.properties.width, h = lib.properties.height;
            var iw = window.innerWidth * .95, ih = window.innerHeight * .95;
            var pRatio = window.devicePixelRatio || 1, xRatio = iw / w, yRatio = ih / h, sRatio = .5;
            if (isResp) {
                if ((respDim == 'width' && lastW == iw) || (respDim == 'height' && lastH == ih)) {
                    sRatio = lastS;
                }
                else if (!isScale) {
                    if (iw < w || ih < h)
                        sRatio = Math.min(xRatio, yRatio);
                }
                else if (scaleType == 1) {
                    sRatio = Math.min(xRatio, yRatio);
                }
                else if (scaleType == 2) {
                    sRatio = Math.max(xRatio, yRatio);
                }
            }
            canvas.width = w * pRatio * sRatio;
            canvas.height = h * pRatio * sRatio;
            canvas.style.width = dom_overlay_container.style.width = anim_container.style.width = w * sRatio + 'px';
            canvas.style.height = anim_container.style.height = dom_overlay_container.style.height = h * sRatio + 'px';
            stage.scaleX = pRatio * sRatio;
            stage.scaleY = pRatio * sRatio;
            lastW = iw; lastH = ih; lastS = sRatio;
            stage.tickOnUpdate = false;
            stage.update();
            stage.tickOnUpdate = true;
        }
    }
    function resize(isResp, respDim, isScale, scaleType, container){
        var container = document.getElementById(container);
        console.log(container);
        var w = 608, h = 1080;
        var iw = window.innerWidth * .95, ih = window.innerHeight * .95;
        var pRatio = window.devicePixelRatio || 1, xRatio = iw / w, yRatio = ih / h, sRatio = 1;
        
                sRatio = Math.min(xRatio, yRatio);
        // container.width(w * pRatio * sRatio);
        // container.height(h * pRatio * sRatio);

        container.style.width = w * sRatio + 'px';
        container.style.height = h * sRatio + 'px';
    }
    // resize(true, 'both', true, 1, 'animation_container');
    // window.addEventListener('resize', function(){
    //     resize(true, 'both', true, 1, 'animation_container');
    // });
    makeResponsive(true, 'both', true, 1);
    AdobeAn.compositionLoaded(lib.properties.id);
    fnStartAnimation();
}

window.uploadPic = function () {
    window.chooseImage();
}
window.stopAudio = function () {
    exportRoot.stopAudio();
}
window.openCreateCardPanel = function () {
}
window.openPanel = function (p_param) {
    // makeVideo,
                // <a href="#hate" class="button hate">hate</a>
                // <a href="#love" class="button love">love</a>
    // nsfwScreen,
                // <a href="#newimage" class="button love">love</a>
    // shareScreen
                // <a href="#downlaod" class="button downlaod">download</a>
                // <a href="#copy" class="button copy">copy</a>
                // <a href="#email" class="button email">email</a>
                // <a href="#twitter" class="button twitter">twitter</a>
                // <a href="#facebook" class="button facebook">facebook</a>
    // loadingScreen,
                // <a href="#restart" class="button restart">restart</a>
    // errorScreen
                // <a href="#restart" class="button restart">restart</a>


    exportRoot.openPanel(p_param);
}
window.openNSFWPanel = function() {
    exportRoot.openNSFWPanel();
    ga('send', 'event', 'create card', 'error', 'nsfw');
}
window.openErrorPanel = function() {
    exportRoot.openErrorPanel();
    ga('send', 'event', 'create card', 'error', 'generic');
}
window.openLoveHatePanel = function() {
    exportRoot.openLoveHatePanel();
    ga('send', 'event', 'create card', 'preview', 'complete');
}
window.hideLoader = function() {
    exportRoot.hideLoader();
    //console.log("clearPanel please!")
}
window.showLoader = function(p_param) {   
    //Steve, should be "loader1", "loader2", or "loader3"
    exportRoot.showLoader(p_param);
    //console.log("show loader: " + p_param);
}
window.animationStarted = function() {
    //console.log("Animation Started!!");
    document.getElementById("loading").style.visibility = "hidden";
    document.body.className += ' ' + 'ready';
}

exports.init = init;