function lightBox() {
  // Use this example, or...
  $('a[rel*="lightbox"]').lightBox(); // Select all links that contains lightbox in the attribute rel
}

$(window).on('load', function() {
    if($('a[rel*="lightbox"]').length > 0) lightBox();
});