$(window).ready(function(){

$(".notify").one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend',
  function(e) {
  alert("hola");
});

});
