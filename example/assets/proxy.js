$(document).ready(function () {

    $('.social').click(function (e) {
        e.preventDefault();
        var h = 480;
        var w = 640;
        var d = document.documentElement;
        var url = $(this).attr('href');
        var windowName = $(this).attr("name");
        var windowSize = 'height=' + Math.min(h, screen.availHeight) +
            ',width=' + Math.min(w, screen.availWidth) +
            ',left=' + Math.max(0, ((d.clientWidth - w) / 2 + window.screenX)) +
            ',top=' + Math.max(0, ((d.clientHeight - h) / 2 + window.screenY));
        window.open(url, windowName, windowSize);
    });

});
