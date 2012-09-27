function showModusContainer(){
    var toolbar = document.getElementById('zoolu-modus-toolbar');
    var show_toolbar = document.getElementById('zoolu-show-modus-toolbar');
    toolbar.style.top = '0px';
    show_toolbar.style.top = '-40px';
};

function hideModusContainer(){
    var toolbar = document.getElementById('zoolu-modus-toolbar');
    var show_toolbar = document.getElementById('zoolu-show-modus-toolbar');
    toolbar.style.top = '-40px';
    show_toolbar.style.top = '0px';
};