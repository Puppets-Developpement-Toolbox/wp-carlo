/**
 * Like jQuery's slideDown/slideUp function - uses CSS3 transitions
 * @param  {Node} elem Element to show and hide
 */
export var slideDown = function (elem, delta) {
	var h = (delta) ? parseInt(delta) : 0;
	elem.children.forEach(function(elt){
   var margins = parseInt(window.getComputedStyle(elt).getPropertyValue('margin-top')) + parseInt(window.getComputedStyle(elt).getPropertyValue('margin-bottom'))
		h = h + elt.getBoundingClientRect().height + margins;
	});
	elem.style.maxHeight = h + 'px';
}
export var slideUp = function (elem, delta) {
	var h = (delta) ? parseInt(delta) : 0;
	elem.style.maxHeight = delta + 'px';
}

//animation and transition end event
export function whichAnimationEvent(){var n,i=document.createElement("fakeelement"),t={animation:"animationend",OAnimation:"oAnimationEnd",MozAnimation:"animationend",WebkitAnimation:"webkitAnimationEnd"};for(n in t)if(void 0!==i.style[n])return t[n]}
export function whichTransitionEvent(){var n,i=document.createElement("fakeelement"),t={transition:"transitionend",OTransition:"oTransitionEnd",MozTransition:"transitionend",WebkitTransition:"webkitTransitionEnd"};for(n in t)if(void 0!==i.style[n])return t[n]}
export var animationEvent=whichAnimationEvent(),transitionEvent=whichTransitionEvent();

//forEach NodeList & HTMLCollection polyfill  for IE
NodeList.prototype.forEach||(NodeList.prototype.forEach=Array.prototype.forEach),HTMLCollection.prototype.forEach||(HTMLCollection.prototype.forEach=Array.prototype.forEach);