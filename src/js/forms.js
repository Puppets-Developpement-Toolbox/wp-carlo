//recaptcha
//prod 6LcViu8UAAAAAPMqcSPkfys4z7RDEf0vvrbpneaU
//regaloeb 6LdRt_EUAAAAAAYvawHW4ExIVWYYGd72s6sEQc_t
var renderRecaptcha = function () {  
	grecaptcha.render('recaptcha-container', {  
		'sitekey': '6LdRt_EUAAAAAAYvawHW4ExIVWYYGd72s6sEQc_t',  
		'callback': reCaptchaCallback,  
		theme: 'light', //light or dark    
		//type: 'image',// image or audio    
		size: 'normal'//normal or compact    
	});  
};  

var reCaptchaCallback = function (response) {  
	if (response !== '') {  
		var elt = document.querySelector('.captcha');
		elt.classList.remove('error');
		var errorP = elt.querySelector('.error');
		if(errorP){
			errorP.classList.remove('error');
			errorP.innerHTML = '';
		} 
	}  
};

(function () {
	////////// FORMS
	//is mobile
	var isMobile = { 
		Android: function() { return navigator.userAgent.match(/Android/i); }, 
		BlackBerry: function() { return navigator.userAgent.match(/BlackBerry/i); }, 
		iOS: function() { return navigator.userAgent.match(/iPhone|iPad|iPod/i); }, 
		Opera: function() { return navigator.userAgent.match(/Opera Mini/i); }, 
		Windows: function() { return navigator.userAgent.match(/IEMobile/i); }, 
		any: function() { return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows()); } 
	};
	//valid forms
	function validateEmail(email) {
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}
	var regExpressions = {
		'pwd': /^[a-zA-Z0-9]{5,}$/,      /* minimum 5 caractères alphanumériques */
		'zipcode' : /^[0-9]{5}$/,      /* 5 chiffres */
		'phoneNumber' : /^[0-9]{10,}$/,      /* minimum 10 chiffres */
		'integer': /^\d+$/,      /* pas de caractères alpha */
		'email': /\S+@\S+\.\S+/,      /* e-mail simplifié */
		'empty':/^\s*$/,
		//'date': /^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/	/* dd/mm/yyy 01 à 31/01 à 12/ 1900 à 2099*/,
		'date': /^^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/	/* yyyy-mm-dd */
	}
	var isEmail = function(string){
		//returns true if string is email
		//return regExpressions.email.test(string);
		return validateEmail(string);
	}
	var isEmpty = function(string){
		//returns true if string is empty
		return regExpressions.empty.test(string);
	}
	var isNumber = function(string){
		//returns true if string is a number
		return regExpressions.integer.test(string);
	}
	var isDate = function(string){
		//returns true if string is a number
		return regExpressions.date.test(string);
	}
  if(isMobile.iOS()){
     document.body.classList.add('device-ios');
  }
  if(isMobile.Android()){
     document.body.classList.add('device-android');
  }
  
	function detectIE() {
		var ua = window.navigator.userAgent;
		// Test values; Uncomment to check result …
		// IE 10
		// ua = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)';
		// IE 11
		// ua = 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko';
		// Edge 12 (Spartan)
		// ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36 Edge/12.0';
		// Edge 13
		// ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586';
    // Modern Edge
    // ua = Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36 Edg/90.0.818.66
		var msie = ua.indexOf('MSIE ');
		if (msie > 0) {
			// IE 10 or older => return version number
			return parseInt(ua.substring(msie + 5, ua.indexOf('.', msie)), 10);
		}

		var trident = ua.indexOf('Trident/');
		if (trident > 0) {
			// IE 11 => return version number
			var rv = ua.indexOf('rv:');
			return parseInt(ua.substring(rv + 3, ua.indexOf('.', rv)), 10);
		}

		var edge = ua.indexOf('Edge/');
		if (edge > 0) {
			// Edge (IE 12+) => return version number
			return parseInt(ua.substring(edge + 5, ua.indexOf('.', edge)), 10);
		}

		var edg = ua.indexOf('Edg/');
		if (edg > 0) {
			// Edge => return version number
			return parseInt(ua.substring(edg + 4, ua.indexOf('.', edg)), 10);
		}
		// other browser
		return false;
	}
	var detectAutofill = function (scope) {
		// match the filter on autofilled elements in Firefox
		var mozFilterMatch = /^grayscale\(.+\) brightness\((1)?.*\) contrast\(.+\) invert\(.+\) sepia\(.+\) saturate\(.+\)$/;
		scope.addEventListener('animationstart', onAnimationStart);
		scope.addEventListener('input', onInput);
		scope.addEventListener('transitionstart', onTransitionStart);

		function onAnimationStart(event) {
			// detect autofills in Chrome and Safari by:
			//   - assigning animations to :-webkit-autofill, only available in Chrome and Safari
			//   - listening to animationstart for those specific animations
			if (event.animationName === 'onautofillstart') {
				// during autofill, the animation name is onautofillstart
				autofill(event.target);
			} 
			else if (event.animationName === 'onautofillcancel') {
				// during autofill cancel, the animation name is onautofillcancel
				cancelAutofill(event.target);
			}
		}

		function onInput(event) {
			if ('data' in event) {
				// input events with data may cancel autofills
				cancelAutofill(event.target);
			} 
			else {
				// input events without data are autofills
				autofill(event.target);
			}
		}

		function onTransitionStart(event) {
			// detect autofills in Firefox by:
			//   - listening to transitionstart, only available in Edge, Firefox, and Internet Explorer
			//   - checking filter style, which is only changed in Firefox
			var mozFilterTransition = event.propertyName === 'filter' && getComputedStyle(event.target).filter.match(mozFilterMatch);

			if (mozFilterTransition) {
				if (mozFilterTransition[1]) {
					// during autofill, brightness is 1
					autofill(event.target);
				} 
				else {
					// during autofill cancel, brightness is not 1
					cancelAutofill(event.target);
				}
			}
		}

		function autofill(target) {
			if (!target.isAutofilled) {
				target.isAutofilled = true;
				target.setAttribute('autofilled', '');
				if(target.closest('.form-type-textfield')){
          target.closest('.form-type-textfield').classList.add('filled');
        }
				target.dispatchEvent(new CustomEvent('autofill', {
					bubbles: true
				}));
			}
		}

		function cancelAutofill(target) {
			if (target.isAutofilled) {
				target.isAutofilled = false;
				target.removeAttribute('autofilled');
				if(target.closest('.form-type-textfield')){
          target.closest('.form-type-textfield').classList.remove('filled');
        }
				target.dispatchEvent(new CustomEvent('autofillcancel', {
					bubbles: true
				}));
			}
		}

		return function () {
			scope.removeEventListener('animationstart', onAnimationStart);
			scope.removeEventListener('input', onInput);
			scope.removeEventListener('transitionstart', onTransitionStart);
		};
	};
	document.addEventListener("DOMContentLoaded", function () {
		
		/////////////////////////////// à appeler après chaque modif de DOM 
		//detect autofill
		document.querySelectorAll('input').forEach(function(elt){
			detectAutofill(elt);
		});
		
		//input focus
		document.querySelectorAll('.form-type-textfield input').forEach(function(elt){
			elt.addEventListener('focus', function(e){
				var that = e.currentTarget;
				that.parentElement.classList.add('focus');
				that.parentElement.classList.remove('filled');
				that.parentElement.classList.remove('error');
				var errorP = that.parentElement.querySelector('.error');
				if(errorP){
					errorP.innerHTML = '';
				}
			});
		});
		//input blur
		document.querySelectorAll('.form-type-textfield input').forEach(function(elt){
			elt.addEventListener('blur', function(e){
				var that = e.currentTarget;
				if(isEmpty(that.value)){
					that.parentElement.classList.remove('focus');
				}
				else{
					that.parentElement.classList.remove('focus');
					that.parentElement.classList.add('filled');
				}
				//email
				if(that.getAttribute('type') === 'email'){
					if(!isEmpty(that.value) && !isEmail(that.value)){
						//if not empty must be a valid email
						that.parentElement.classList.add('error');
						var errorP = that.parentElement.querySelector('.error');
						if(errorP){
							errorP.innerHTML = that.parentElement.querySelector('.error').getAttribute('data-text');
						}
					}
				}
			});
		});
		//input default state
		document.querySelectorAll('.form-type-textfield input').forEach(function(elt){
			if(!isEmpty(elt.value)){
				elt.parentElement.classList.add('filled');
			}
		});
		
    ////// TODO en fonction simple select ou js CustomSelect, commenter zone 
    /*
    //js select
		//customized select => /javascripts/plugins.js (vanilla-js-dropdown)
		document.querySelectorAll('select').forEach(function(elt){
			new RegalCustomSelect({elem:elt});
		});
		//select 
		document.querySelectorAll('.form-type-select .js-Dropdown-title').forEach(function(elt){
			elt.addEventListener('click', function(e){
				var that = e.currentTarget;
				that.parentElement.parentElement.classList.remove('error');
				var errorP = that.parentElement.parentElement.querySelector('.error');
				if(errorP){
					errorP.innerHTML = '';
				}
			});
		});
    //fin js select
    */
    
    //default state
    //select state
		document.querySelectorAll('.form-type-select select').forEach(function(elt){
			if(!isEmpty(elt.value)){
				elt.parentElement.classList.add('filled');
			}
      else{
        elt.parentElement.classList.remove('error');
        elt.parentElement.classList.remove('selected');
        var errorP = elt.parentElement.querySelector('.error');
        if(errorP){
          errorP.innerHTML = '';
        }
      }
		}); 
    //focus
    document.querySelectorAll('.form-type-select select').forEach(function(elt){
			elt.addEventListener('focus', function(e){
				var that = e.currentTarget;
				that.parentElement.classList.add('focus');
				that.parentElement.classList.remove('filled');
				that.parentElement.classList.remove('error');
			});
		});
    //onchange for all select
    function changeBlurSelect(e){
      var that = e.currentTarget;
      if(isEmpty(that.value)){
        that.parentElement.classList.remove('focus');
        that.parentElement.classList.remove('filled');
        if(that.classList.contains('required')){
          that.parentElement.classList.add('error');
          var errorP = that.parentElement.querySelector('.error');
          if(errorP){
            errorP.innerHTML = errorP.getAttribute('data-text');
          }
        }
      }
      else{
        that.parentElement.classList.remove('error');
        that.parentElement.classList.remove('focus');
        that.parentElement.classList.add('filled');
        var errorP = that.parentElement.querySelector('.error');
        if(errorP){
          errorP.innerHTML = '';
        }
      }
    }
    document.querySelectorAll('.form-type-select select').forEach(function(elt){
			elt.addEventListener('change', changeBlurSelect);
			elt.addEventListener('blur', changeBlurSelect);
		});
    
		//allow only numbers in input[type=number] because alpha is displayed but value is empty
		document.querySelectorAll('.form-type-textfield input[type=number]').forEach(function(elt){
      elt.addEventListener('input', function(e){
        //.replace(/[^0-9.,]/g, '');
        e.currentTarget.value = e.currentTarget.value; //yes it looks strange but it's necessary to visually empty field
        var that = e.currentTarget;
        if(that.classList.contains('js-mandatory') && isEmpty(that.value)){
          that.parentElement.classList.remove('filled');
          that.parentElement.classList.remove('focus');
          that.parentElement.classList.add('error');
					var errorP = that.parentElement.querySelector('.error');
					if(errorP){
						errorP.innerHTML = errorP.getAttribute('data-text');
					}
        }
      });
		});
		//check if browser recognize correctly input-date
		function checkDateInput() {
			var input = document.createElement('input');
			input.setAttribute('type','date');
			var notADateValue = 'not-a-date';
			input.setAttribute('value', notADateValue); 
			return (input.value !== notADateValue);
		}
		//IE Edge input-date doesn't have reset button, so we create one for him!
		if(detectIE() && checkDateInput()){
			document.querySelectorAll('.input-date input[type=date]').forEach(function(elt){
				var resetBtn = document.createElement('a');
				resetBtn.setAttribute('class', 'reset');
				resetBtn.setAttribute('href', '#');
				elt.parentElement.appendChild(resetBtn);
			});
			document.querySelectorAll('.input-date .reset').forEach(function(elt){
				elt.onclick = function(e){
					e.preventDefault();
          e.currentTarget.parentElement.classList.remove('filled');
          var input = e.currentTarget.parentElement.querySelector('input');
          input.value = '';
          if(input.classList.contains('js-mandatory')){
            e.currentTarget.parentElement.classList.add('error');
          }
				};
			});
			document.querySelectorAll('.input-time input[type=time]').forEach(function(elt){
				var resetBtn = document.createElement('a');
				resetBtn.setAttribute('class', 'reset');
				resetBtn.setAttribute('href', '#');
				elt.parentElement.appendChild(resetBtn);
			});
			document.querySelectorAll('.input-time .reset').forEach(function(elt){
				elt.onclick = function(e){
					e.preventDefault();
          e.currentTarget.parentElement.classList.remove('filled');
          var input = e.currentTarget.parentElement.querySelector('input');
          input.value = '';
          if(input.classList.contains('js-mandatory')){
            e.currentTarget.parentElement.classList.add('error');
          }
				};
			});
		}
		//ShowError
		function showError(that){
			that.parentElement.classList.remove('filled');
			that.parentElement.classList.remove('focus');
			that.parentElement.classList.add('error');
			var errorP = that.parentElement.querySelector('.infos.error');
			if(errorP){
				errorP.innerHTML = errorP.getAttribute('data-text');
			}
		}
		//hideError
		function hideError(that){
			that.parentElement.classList.add('focus');
			that.parentElement.classList.remove('error');
			var errorP = that.parentElement.querySelector('.error');
			if(errorP){
				errorP.innerHTML = '';
			}
		}
		//verif mandatory form-type-textfield onblur
		document.querySelectorAll('.form-type-textfield input.required').forEach(function(elt){
			elt.addEventListener('blur', function(e){
				var that = e.currentTarget;
				//text
				if(that.getAttribute('type') === 'text' || 'number' || 'tel' || 'password'){
					if(isEmpty(that.value)){
						showError(that);
					}
				}
				//email
				if(that.getAttribute('type') === 'email'){
					if(isEmpty(that.value)){
						showError(that);
					}
					else if(!isEmail(that.value)){
						showError(that);
						that.parentElement.classList.add('focus');
					}
				}
				//date
				if(that.getAttribute('type') === 'date'){
					if(isEmpty(that.value)){
						showError(that);
					}
					else if(!isDate(that.value)) {
						showError(that);
					}
					else{
						that.parentElement.classList.remove('focus');
						if(isMobile.any()){
							that.parentElement.classList.add('filled');
						}
					}
				}
			});
		});
    
		//verif mandatory input-date onchange
		if(checkDateInput()){
			document.querySelectorAll('.input-date input.required').forEach(function(elt){
				elt.addEventListener('change', function(e){
					var that = e.currentTarget || e.target;
					if(!isMobile.iOS()){
						that.blur();
					}
					/*if(isEmpty(that.value)){
						showError(that);
					}
					else if(!isDate(that.value)) {
						showError(that);
					}
					else{
						that.parentElement.classList.remove('focus');
						if(isMobile.any()){
							that.parentElement.classList.add('filled');
						}
					}*/
				});
			});
			document.querySelectorAll('.input-date input:not(.required)').forEach(function(elt){
				elt.addEventListener('change', function(e){
					var that = e.currentTarget || e.target;
					if(!isMobile.iOS()){
						that.blur();
					}
				});
			});
		}
		
		//focus input text tout type
		document.querySelectorAll('.form-type-textfield input').forEach(function(elt){
			elt.addEventListener('focus', function(e){
				var that = e.currentTarget;
				that.parentElement.classList.add('focus');
				that.parentElement.classList.remove('error');
				var errorP = that.parentElement.querySelector('.error');
				if(errorP){
					errorP.innerHTML = '';
				}
			});
		});
		
		//verif mandatory checked-box onmouseup
		document.querySelectorAll('.form-type-checkbox').forEach(function(elt){
			elt.addEventListener('mouseup', function(e){
				var that = e.currentTarget;
        if(that.classList.contains('required')){
          if(!that.querySelectorAll('input:checked').length){ 
            // condition inverted because checkbox hasn't yet changed its state when mouseup targeted...
            that.classList.remove('error');
            var errorP = that.querySelector('.error');
            if(errorP){
              errorP.innerHTML = '';
            }
          }
          else{
            that.classList.add('error');
            var errorP = that.querySelector('.error');
            if(errorP){
              errorP.innerHTML = errorP.getAttribute('data-text');
            }
          }
        }
			});
		});
		
		//textarea focus
		document.querySelectorAll('.form-type-textarea textarea').forEach(function(elt){
			elt.addEventListener('focus', function(e){
				var that = e.currentTarget;
				that.parentElement.classList.add('focus');
				that.parentElement.classList.remove('error');
				var errorP = that.parentElement.querySelector('.error');
				if(errorP){
					errorP.innerHTML = '';
				}
			});
		});
		//textarea blur
		document.querySelectorAll('.form-type-textarea textarea:not(.required)').forEach(function(elt){
			elt.addEventListener('blur', function(e){
				var that = e.currentTarget;
				if(isEmpty(that.value)){
					that.parentElement.classList.remove('focus');
				}
				else {
					that.parentElement.classList.remove('focus');
					that.parentElement.classList.add('filled');
				}
			});
		});
		document.querySelectorAll('.form-type-textarea textarea.required').forEach(function(elt){
			elt.addEventListener('blur', function(e){
				var that = e.currentTarget;
				if(isEmpty(that.value)){
					showError(that);
				}
				else {
					that.parentElement.classList.remove('focus');
					that.parentElement.classList.add('filled');
				}
			});
		});
		//textarea default state
		document.querySelectorAll('.form-type-textarea textarea').forEach(function(elt){
			if(!isEmpty(elt.value)){
				elt.parentElement.classList.add('filled');
			}
		});
		
		//radio 
		document.querySelectorAll('.js-form-type-radios input').forEach(function(elt){
			elt.addEventListener('change', function(e){
				var that = e.currentTarget;
				that.parentElement.classList.remove('error');
				var errorP = that.parentElement.querySelector('.error');
				if(errorP){
					errorP.innerHTML = '';
				}
			});
		});
		
		
	});
	////////// END FORMS
})();

