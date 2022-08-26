/*

Browser Zoom support

*/

var browserZoomLevel = 100;

jQuery("#font-smaller").click(function(){
    if (browserZoomLevel > 50) {
        browserZoomLevel -= 10;
        document.body.style.zoom = browserZoomLevel + "%";
    }
});

jQuery("#font-larger").click(function(){
    if (browserZoomLevel < 500) {
        browserZoomLevel += 10;
        document.body.style.zoom = browserZoomLevel + "%";
    }
});