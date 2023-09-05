export var slideDown = function (elem, delta) {
	var h = (delta) ? parseInt(delta) : 0;
	elem.children.forEach(function(elt){
   var margins = parseInt(window.getComputedStyle(elt).getPropertyValue('margin-top')) + parseInt(window.getComputedStyle(elt).getPropertyValue('margin-bottom'));
		//h = h + elt.getBoundingClientRect().height + margins;
    //console.log("elt: ", elt);
    //console.log("margins: ", margins);
    //console.log("elt.offsetHeight: ", elt.offsetHeight);
		h = h + elt.offsetHeight + margins;
	});
	elem.style.maxHeight = h + 'px';
}
export var slideUp = function (elem, delta) {
	var h = (delta) ? parseInt(delta) : 0;
	elem.style.maxHeight = delta + 'px';
}
 
//animation and transition end event
function whichAnimationEvent(){var n,i=document.createElement("fakeelement"),t={animation:"animationend",OAnimation:"oAnimationEnd",MozAnimation:"animationend",WebkitAnimation:"webkitAnimationEnd"};for(n in t)if(void 0!==i.style[n])return t[n]};
function whichTransitionEvent(){var n,i=document.createElement("fakeelement"),t={transition:"transitionend",OTransition:"oTransitionEnd",MozTransition:"transitionend",WebkitTransition:"webkitTransitionEnd"};for(n in t)if(void 0!==i.style[n])return t[n]}
export var animationEvent=whichAnimationEvent();
export var transitionEvent=whichTransitionEvent();

//wait until scroll is done
window.addEventListener('scroll', function() {
  if(this.scrollTO) clearTimeout(this.scrollTO);
  this.scrollTO = setTimeout(function() {
    var event = new Event('scrollEnd');
    this.dispatchEvent(event);
  }, 500);
}, false);
/*
window.addEventListener('scrollEnd', function() {
  console.log("scrollEnd");
});
*/
//wait until resize is done
window.addEventListener('resize', function() {
  if(this.resizeTO) clearTimeout(this.resizeTO);
  this.resizeTO = setTimeout(function() { 
    //triggerEvent(this, 'resizeEnd');
    var event = new Event('resizeEnd');
    this.dispatchEvent(event);
  }, 500);
});
/*
window.addEventListener('resizeEnd', function() {
  //console.log("resizeEnd");
});
*/

//forEach NodeList & HTMLCollection polyfill
NodeList.prototype.forEach||(NodeList.prototype.forEach=Array.prototype.forEach),HTMLCollection.prototype.forEach||(HTMLCollection.prototype.forEach=Array.prototype.forEach);