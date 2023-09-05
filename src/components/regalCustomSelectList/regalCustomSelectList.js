const CustomSelectList = function(options) {
  var elem =
    typeof options.elem === 'string' ? document.getElementById(options.elem) : options.elem,
    titleClass = 'js-Dropdown-title',
    openClass = 'is-open',
    openTopClass = 'is-open-top',
    index = 0,
    selectContainer = elem,
    button = elem.querySelector('button'),
    ul = elem.querySelector('.js-Dropdown-list');
  var clickOk = true;
  //var clickevent = (XXL.isMobileContext && XXL.isMobile.any()) ? 'touchstart' : 'click';
  selectContainer.addEventListener('click', onClick);

  /**
   * Closes the current select on any click outside of it.
   *
   */
  function outsideClick(e) {
		if (!selectContainer.contains(e.target)){
			close();
		}
	};
  

  /**
   * Handles the clicks on current select.
   *
   * @param {object} e - The item the click occured on.
   */
  function onClick(e) {
    e.preventDefault();
    var t = e.target; // || e.srcElement; - uncomment for IE8
    //console.log(" ////////onClick(), t: ", t); 
    //console.log(" ////////onClick(), t.className: ", t.className); 
    //console.log(" ////////onClick(), t.nodeName: ", t.nodeName); 
      
    if (t.className === titleClass) {
      e.preventDefault();
      toggle();
    }
    else{
      if(clickOk && !t.classList.contains('inactive')){
        clickOk = false;
        if(t.querySelector('span')){
          selectContainer.querySelector('.' + titleClass).innerHTML = t.querySelector('span').innerHTML;
        }
        if(t.nodeName == 'BUTTON'){
          selectContainer.querySelector('.' + titleClass).innerHTML = t.innerHTML;
        }
        close();
        setTimeout(function(){
          clickOk = true;
        }, 1000);
      }
    }
  }
  
  //animation and transition end event
  function whichAnimationEvent(){var n,i=document.createElement("fakeelement"),t={animation:"animationend",OAnimation:"oAnimationEnd",MozAnimation:"animationend",WebkitAnimation:"webkitAnimationEnd"};for(n in t)if(void 0!==i.style[n])return t[n]}function whichTransitionEvent(){var n,i=document.createElement("fakeelement"),t={transition:"transitionend",OTransition:"oTransitionEnd",MozTransition:"transitionend",WebkitTransition:"webkitTransitionEnd"};for(n in t)if(void 0!==i.style[n])return t[n]}var animationEvent=whichAnimationEvent(),transitionEvent=whichTransitionEvent();
  
  /**
   * Toggles the open/close state of the select on title's clicks.
   *
   * @public
   */
  function toggle() {
		var windowHeight = window.innerHeight||e.clientHeight||g.clientHeight;
		var menutop = button.getBoundingClientRect().top;
		if(ul.classList.contains(openTopClass) || ul.classList.contains(openClass)){
			close();
		}
		else{
			open();
		}
	}

  /**
   * Opens the select.
   *
   * @public
   */
  function open() {
    var windowHeight = window.innerHeight||e.clientHeight||g.clientHeight;
    var menutop = button.getBoundingClientRect().top;
    if(menutop > windowHeight - 250){
      ul.classList.add(openTopClass);
    }
    else{
      ul.classList.add(openClass);
    }
    button.classList.add('is-open');
    document.addEventListener('click', outsideClick);
    elem.classList.add('is-open');
  }

  /**
   * Closes the select.
   *
   * @public
   */
  function close() {
		ul.addEventListener(transitionEvent, closeEnd);
		ul.classList.add('closing');
    button.classList.remove('is-open');
    elem.classList.remove('is-open');
    document.removeEventListener('click', outsideClick);
	};
	function closeEnd(event) {
		ul.removeEventListener(transitionEvent, closeEnd);
		ul.classList.remove('closing');
		if(ul.classList.contains(openTopClass)){
			ul.classList.remove(openTopClass);
		}
		else{
			ul.classList.remove(openClass);
		}
		button.classList.remove('is-open');
	};

  return {
    toggle: toggle,
    close: close,
    open: open,
  };
  
  /* structure HTML des elt:
  <div class="js-Dropdown">
		<button class="js-Dropdown-title">Titre</button>
		<ul class="js-Dropdown-list">
			<li><a href="https://www.qwant.com/">Option 1</a></li>
			
		</ul>
	</div>
  */
};

export default CustomSelectList; 