(function () {
	var w = window,
		d = document,
		e = d.documentElement,
		g = d.getElementsByTagName('body')[0];
 
	var windowHeight = w.innerHeight||e.clientHeight||g.clientHeight;
	var windowWidth = w.innerWidth||e.clientWidth||g.clientWidth;
	var mobileLimit = 650;
	var tabletLimit = 1000;
	var desktopLimit = 1024;
	var isMobileContext = (windowWidth < mobileLimit) ? true : false;
	var isMobileTabletContext = (windowWidth < tabletLimit) ? true : false;
	var isTabletContext = (windowWidth < tabletLimit) && (windowWidth > mobileLimit)? true : false;
	
	
	//------------DOMLOAD		
	document.addEventListener("DOMContentLoaded", function () {
    // No action on empty links :
    document.querySelectorAll('a[href="#"]').forEach(function(elt){
      elt.addEventListener('click',function(e) {
        e.preventDefault();
      });
    });
		//flag body for safari
		if(XXL.isSafari){
			document.body.classList.add('safari');
		}
    function updateTargetAttribute(){
      var externalRegex = new RegExp('/' + window.location.host + '/');
      document.querySelectorAll('a').forEach(function(el) {
        if(!externalRegex.test(el.href) && el.href.indexOf('http') === 0) {
          el.setAttribute('target', '_blank');
        }
      });
    };
    updateTargetAttribute();
    //add picto to external links
    document.querySelectorAll('a[target=_blank]').forEach(function(el) {
      var picto = document.createElement('span');
      picto.classList.add('picto-ext');
      picto.innerHTML = '<svg viewBox="0 0 14.97 15.13" fill="none"><use xlink:href="#svg-ext-link"></use></svg>';
      el.appendChild(picto);
    });
		//body scrolling state
		var docuLastScroll;
		function updateBody() {
			var currentScroll = w.pageYOffset;
			var limit = 10
			var mainHeader = document.querySelector('.main-header');
			if(currentScroll == 0){
				// nothing to do
			}
			else if(currentScroll > docuLastScroll){
				//We go down
				limit = 10;
				document.body.classList.remove('goingup');
				document.body.classList.add('goingdown');
			}
			else{
				// We go up
				limit = 10;
				document.body.classList.remove('goingdown');
				document.body.classList.add('goingup');
			}
			if(currentScroll <= limit){
				document.body.classList.remove('scrolled');
			}
			else{
				if(!document.body.classList.contains('scrolled')){
					document.body.classList.add('scrolled');
				}
			}
			docuLastScroll = currentScroll;
		}
		docuLastScroll = w.pageYOffset;
		updateBody();
		w.addEventListener('scroll', updateBody);
    
    //menu hamburger
    if(document.querySelector('.main-header .hamburger')) {
      document.querySelector('.main-header .hamburger').addEventListener('click', function (e) {
        e.preventDefault();
        e.currentTarget.classList.toggle('active');

        document.querySelector('.main-nav-mask').classList.toggle('open');
        document.querySelector('.main-nav-list').classList.toggle('open');
        document.querySelector('body').classList.toggle('no-overflow');
        document.querySelector('.page-content').classList.toggle('mobile-nav-active');
        document.querySelector('.main-header').classList.toggle('mobile-nav-active');
        //document.querySelector('.main-footer').classList.toggle('mobile-nav-active');
      });
		}
    
    //roll-bold
    var rollBolds = document.querySelectorAll('.js-roll-bold');
    if(rollBolds.length) {
      setTimeout(function() {
      rollBolds.forEach(function(elt) {
        var txt = elt.innerText;
        var curWidth = elt.querySelector('span').offsetWidth;
        var boldTxt = document.createElement('span');
        boldTxt.classList.add('js-bold-txt');
        boldTxt.innerText = txt;
        elt.appendChild(boldTxt);
        var boldWidth = boldTxt.offsetWidth;
        //console.log("boldWidth: ", boldWidth);
        //console.log("curWidth: ", curWidth);
        var padX = (boldWidth - curWidth) / 2;
        elt.setAttribute('data-padX', padX);
        elt.style.paddingLeft = padX + 'px';
        elt.style.paddingRight = padX + 'px';
        elt.addEventListener('mouseover', function(e) {
          e.currentTarget.style.paddingLeft = '';
          e.currentTarget.style.paddingRight = '';
        });
        elt.addEventListener('mouseout', function(e) {
          e.currentTarget.style.paddingLeft = e.currentTarget.getAttribute('data-padX') + 'px';
          e.currentTarget.style.paddingRight = e.currentTarget.getAttribute('data-padX') + 'px';
        });
      });
      }, 500);
    }
    
    //header-section strange behavior
    /*
    function resizeMainHeader() {
      var windowWidth = w.innerWidth||e.clientWidth||g.clientWidth;
      var mainHeader = document.querySelector('.main-header .header-section');
      var mainHeaderCalcul = document.querySelector('.main-header .header-section.js-calcul');
      //reset
      mainHeader.style.width = '';
      mainHeader.style.maxWidth = '';
      mainHeader.style.marginLeft = '';
      mainHeader.style.marginRight = '';
      //calculate
      var maxW = parseInt(window.getComputedStyle(mainHeaderCalcul).maxWidth, 10);
      if(windowWidth > maxW) {
        var mLeft = window.getComputedStyle(mainHeaderCalcul).marginLeft;
        var headerW = windowWidth - parseInt(mLeft, 10);
        //apply
        mainHeader.style.width = headerW + 'px';
        mainHeader.style.maxWidth = headerW + 'px';
        mainHeader.style.marginLeft = mLeft;
        mainHeader.style.marginRight = 0;
      }
    }
    //resizeMainHeader();
    //window.addEventListener('resize', resizeMainHeader);
    */
    
    function scrollToTop() {
      window.scroll({top: 0, left: 0, behavior: 'smooth'});
    }
    
    function calculateVhForMobiles() {
      //console.log("calculateVhForMobiles()!");
      var wh = w.innerHeight||e.clientHeight||g.clientHeight;
      var vh = wh * .01;
      setTimeout(function() {
      document.documentElement.style.setProperty('--vh', vh + 'px');
      //console.log("vh: ", vh);
      }, 0);
    }
    calculateVhForMobiles();
    
		//event delegation for video player && open popin
		document.addEventListener('click', docEvent);
		function docEvent(e) {
			//console.log("e.target: ", e.target);
			//playVideo
			if (e.target.closest('.play-video')) {
				e.preventDefault();
				XXL.playVideo(e.target.closest('.play-video'));
			}
			//openPopin
			if (e.target.closest('.open-popin')) {
				e.preventDefault();
				XXL.openPopin(e.target.closest('.open-popin').getAttribute('href') || e.target.closest('.open-popin').getAttribute('data-popin-id'));
			}
      //back-top
      if (e.target.closest('.perso-back-top')) {
        e.preventDefault();
        scrollToTop();
      }
		}
    
    //actu_list menu déroulant pour mobiles à la place filters
    function initActuFiltersMobile() {
      var actuFiltersMobile = document.querySelector('.open-filters-mobile');
      if(actuFiltersMobile){
        actuFiltersMobile.addEventListener('click', showFilters);
      }
    };
    initActuFiltersMobile();
    function showFilters(e) {
      e.preventDefault;
      var that = e.currentTarget;
      var filters = that.parentNode.querySelector('.filters');
      if(that.classList.contains('active')) {
        that.classList.remove('active');
        that.innerHTML = '<span>' + that.getAttribute('data-default') + '</span>';
        filters.classList.remove('active');
      }
      else {
        that.classList.add('active');
        that.innerHTML = '<span>' + that.getAttribute('data-active') + '</span>';
        filters.classList.add('active');
      }
    }
    //fin actu_list menu déroulant pour mobiles à la place filters
    
		//partners nav
    var partners = document.querySelectorAll('.partners');
    if(partners.length) {
      partners.forEach(function(partner) {
        var nav = partner.querySelector('.partners-nav');
        var desc = partner.querySelector('.partners-desc');
        var btns = nav.querySelectorAll('.partner-nav');
        if(btns.length) {
           btns.forEach(function(btn) {
             btn.addEventListener('click', clickPartnersNav);
           });
        }
        var scrollCont = partner.querySelector('.partners-scroll');
        scrollCont.style.width = partners.length * 100 + '%';
        //init slider for nav
        if(!nav.slider && nav.querySelectorAll('.partner-nav').length > 1){
          var obj = {};
          obj.container = nav;
          obj.items = 1;
          obj.slideBy = 1;
          //obj.autoHeight = true;
          obj.speed = 600;
          obj.gutter = 0;
          obj.loop = false;
          obj.controls = true;
          obj.nav = false;
          obj.autoplay = false;
          obj.mouseDrag = true;
          obj.swipeAngle = false;
          obj.autoplayTimeout = 4000;
          //obj.autoplayText = ["▶","❚❚"];
          obj.arrowKeys = true;
          obj.edgePadding = 0;
          obj.responsive = {
            1024:
            {
              "nav": false,
              items: 4,
              slideBy: 4
            },
            768:
            {
              "nav": false,
              items: 3,
              slideBy: 3
            }
          };
          nav.slider = tns(obj);

          //nav.slider.events.on('transitionEnd', sliderNoLoopTransitionEnd);
          nav.slider.events.on('transitionStart', updatePartner);
          //elt.addEventListener('wheel', sliderScrollNextPrev);
          //elt.addEventListener('wheel', noEvent);
        }
        //init first item
        triggerEvent(btns[0], 'click');
        
      });
      
    }
    function clickPartnersNav(e) {
      e.preventDefault();
      var that = e.currentTarget;
      var index = that.getAttribute('data-index');
      var desc = that.closest('.partners').querySelector('.partners-desc .partners-scroll');
      var prevBtn = that.closest('.partners').querySelector('.partners-nav .partner-nav.active');
      if (prevBtn) {
        prevBtn.classList.remove('active');
      }
      that.classList.toggle('active');
      desc.style.transform = 'translateX(' + index * -100 + '%)';
      
      setTimeout(function() {
        var bgColor = window.getComputedStyle(that).backgroundColor;
        var partnerDesc = that.closest('.partners').querySelector('.partners-desc');
        partnerDesc.style.setProperty('--bg-color-active', bgColor);
      }, 250);
      
      if(desc.querySelector('.partner.active')) {
        desc.querySelector('.partner.active').classList.remove('active');
      }
      desc.querySelector('.partner:nth-child(' + (parseInt(index)+1) + ')').classList.add('active');
    };
    function updatePartner(info, eventName) {
      if(eventName === 'transitionStart') {
        var btnToClick = info.container.querySelectorAll('.partner-nav')[info.index];
        triggerEvent(btnToClick, 'click');
      }
    };
    
    //sliders no-loop active / desactive buttons prev / next
    function sliderNoLoopTransitionEnd(info, eventName){
      if(info.index === 0){
        info.prevButton.classList.add('inactive');
      }
      else{
        info.prevButton.classList.remove('inactive');
      }
      if(info.index + info.items >= info.slideItems.length){
        info.nextButton.classList.add('inactive');
      }
      else{
        info.nextButton.classList.remove('inactive');
      }
    };

    //hero-slider
    function initHeroSliders(){
      var heroSliders = document.querySelectorAll('.hero-slider');
      
      if(heroSliders){
        heroSliders.forEach(function(elt){
          if(!elt.slider && elt.querySelectorAll('.slide').length > 1){
            var nbSlides = elt.querySelectorAll('.slide').length;
            var obj = {};
            obj.container = elt;
            obj.items = 1;
            obj.slideBy = 1;
            //obj.autoHeight = true;
            obj.speed = 600;
            obj.gutter = 0;
            obj.loop = true;
            obj.controls = true;
            obj.nav = false;
            obj.autoplay = false;
            obj.mouseDrag = true;
            obj.swipeAngle = false;
            obj.autoplayTimeout = 4000;
            //obj.autoplayText = ["▶","❚❚"];
            obj.arrowKeys = true;
            obj.edgePadding = 0;
            obj.responsive = {1024:
              {
                "nav": false
              }
            };
            elt.slider = tns(obj);

            //elt.slider.events.on('transitionEnd', sliderNoLoopTransitionEnd);
            elt.slider.events.on('transitionEnd', updateNumberView);
            //elt.addEventListener('wheel', sliderScrollNextPrev);
            //elt.addEventListener('wheel', noEvent);
            //add specifics elts for desktop
            setTimeout(function() {
              var specialCont = document.createElement('div');
              specialCont.classList.add('number-view');
              var specialContNumber = document.createElement('div');
              specialContNumber.classList.add('number');
              specialContNumber.innerText = '01';
              specialCont.appendChild(specialContNumber);
              var specialContView = document.createElement('div');
              specialContView.classList.add('view');
              var specialContViewPourcent = document.createElement('div');
              specialContViewPourcent.classList.add('view-pourcent');
              specialContView.appendChild(specialContViewPourcent);
              specialCont.appendChild(specialContView);
              var tnsControls = elt.closest('.slider-hero').querySelector('.tns-controls');
              tnsControls.insertBefore(specialCont, tnsControls.querySelector('button[data-controls=prev]'));
              //init width of view
              tnsControls.querySelector('.view-pourcent').style.transform = 'scaleX(' + (1/nbSlides) +')';
            }, 500);
          }
        });
      }
    };
    function updateNumberView(info, eventName) {
      var ratio = parseInt(info.displayIndex, 10) / parseInt(info.slideCount, 10);
      var tnsControls = info.container.closest('.slider-hero').querySelector('.tns-controls');
      tnsControls.querySelector('.number').innerText = (info.displayIndex.toString().length < 2) ? '0' + info.displayIndex : info.displayIndex;
      tnsControls.querySelector('.view-pourcent').style.transform = 'scaleX(' + ratio +')';
    }
    initHeroSliders();
    
    // line-slider
    function initLineSliders(){
      var lineSliders = document.querySelectorAll('.line-slider');
      
      if(lineSliders){
        lineSliders.forEach(function(elt){
          if(!elt.slider && elt.querySelectorAll('.slide').length > 1){
            var obj = {};
            obj.container = elt;
            obj.items = 1;
            obj.slideBy = 1;
            //obj.autoHeight = true;
            obj.speed = 600;
            obj.gutter = 0;
            obj.loop = true;
            obj.controls = false;
            obj.nav = true;
            obj.autoplay = false;
            obj.mouseDrag = true;
            obj.swipeAngle = false;
            obj.autoplayTimeout = 4000;
            //obj.autoplayText = ["▶","❚❚"];
            obj.arrowKeys = true;
            obj.edgePadding = 0;
            obj.responsive = {1056:
              {
                "nav": false,
                "items": 2,
                "slideBy": 2,
                "nav": false,
                "controls": true,
                "loop": true
              },
              651:
              {/*
                "nav": false,
                "items": 1,
                "slideBy": 1,
                "nav": false,
                "controls": true,
                "loop": false
              */}
            };
            elt.slider = tns(obj);

            //elt.slider.events.on('transitionEnd', sliderNoLoopTransitionEnd);
            //elt.slider.events.on('transitionEnd', updateNumberView);
            //elt.addEventListener('wheel', sliderScrollNextPrev);
            //elt.addEventListener('wheel', noEvent);
          }
        });
      }
    };
    initLineSliders();
    
    function sliderScrollNextPrev(e) {
      //molette souris
     /* if (e.deltaY < 0) {
        e.preventDefault();
        this.slider.goTo('prev');
        sliderScrollNextPrevOnce(this);
      }
      if (e.deltaY > 0) {
        e.preventDefault();
        this.slider.goTo('next');
        sliderScrollNextPrevOnce(this);
      }*/
      //deux doigts sur le pad d'un ordi portable
      if (e.deltaX < 0) {
        e.preventDefault();
        this.slider.goTo('prev');
        sliderScrollNextPrevOnce(this);
      }
      if (e.deltaX > 0) {
        e.preventDefault();
        this.slider.goTo('next');
        sliderScrollNextPrevOnce(this);
      }
    };
    function noEvent(e) {
      e.preventDefault();
    }
    function sliderScrollNextPrevOnce(that) {
      that.removeEventListener('wheel', sliderScrollNextPrev);
      setTimeout(function() {
        that.addEventListener('wheel', sliderScrollNextPrev);
      }, 500);
    };
    
		//cookies acceptGiveCookies
		var gipCookies = 'acceptGIPCookies';
		XXL.eraseCookie(gipCookies); //////////////////////////////// ligne à commenter quand process cookies validé
		var acceptCookies = XXL.readCookie(gipCookies);
		if(!acceptCookies){
			var cookieSection = document.getElementById('cookies');
      if(cookieSection){
        cookieSection.classList.add('visible');
        cookieSection.querySelector('#cookies-accept').addEventListener('click', function(e){
          e.preventDefault();
          XXL.createCookie(gipCookies, true, 365);
          cookieSection.classList.remove('visible');
        });
      }
		}
		
		//reveal intersectionObserver
		function animRevealCallback(elt, on, from){
			//on: booléen && from: 'top' ou 'bottom'
			if(on){
				elt.classList.add('revealed');	
			}
			else{
				elt.classList.remove('revealed');
				var movit = (from === 'top') ? 'bottom' : 'top';
				elt.classList.remove(movit);
				elt.classList.add(from);
			}
		}
		function animRevealInit(){
			var elReveal = document.querySelectorAll('.js-reveal');
			if(elReveal){
				var threshold = (windowWidth < 1025) ? [.1]: [.25];
				elReveal.forEach(function(elt){
					onlyOnce = (elt.getAttribute('data-reveal-once') === '1') ? true : false;
					XXL.XLObserver(elt, animRevealCallback, threshold, onlyOnce);
				});
			}
		}
		animRevealInit();
		
		//wait for page to be fully loaded 
		window.addEventListener("load", function () {
			//console.log("document loaded");
		});
		
		//resizeEnd
		window.addEventListener('resizeEnd', function() {
			windowHeight = w.innerHeight||e.clientHeight||g.clientHeight;
			windowWidth = w.innerWidth||e.clientWidth||g.clientWidth;
			isMobileContext = (windowWidth < mobileLimit) ? true : false;
			isMobileTabletContext = (windowWidth < tabletLimit) ? true : false;
			isTabletContext = (windowWidth < tabletLimit) && (windowWidth > mobileLimit)? true : false;
      calculateVhForMobiles();
		});
	});


})();
