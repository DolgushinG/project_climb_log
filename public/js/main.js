/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!******************************!*\
  !*** ./resources/js/main.js ***!
  \******************************/
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }
function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }
function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
/**
* Template Name: BizLand
* Updated: Jan 29 2024 with Bootstrap v5.3.2
* Template URL: https://bootstrapmade.com/bizland-bootstrap-business-template/
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/
(function () {
  "use strict";

  /**
   * Easy selector helper function
   */
  var select = function select(el) {
    var all = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    el = el.trim();
    if (all) {
      return _toConsumableArray(document.querySelectorAll(el));
    } else {
      return document.querySelector(el);
    }
  };

  /**
   * Easy event listener function
   */
  var on = function on(type, el, listener) {
    var all = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
    var selectEl = select(el, all);
    if (selectEl) {
      if (all) {
        selectEl.forEach(function (e) {
          return e.addEventListener(type, listener);
        });
      } else {
        selectEl.addEventListener(type, listener);
      }
    }
  };

  /**
   * Easy on scroll event listener 
   */
  var onscroll = function onscroll(el, listener) {
    el.addEventListener('scroll', listener);
  };

  /**
   * Navbar links active state on scroll
   */
  var navbarlinks = select('#navbar .scrollto', true);
  var navbarlinksActive = function navbarlinksActive() {
    var position = window.scrollY + 200;
    navbarlinks.forEach(function (navbarlink) {
      if (!navbarlink.hash) return;
      var section = select(navbarlink.hash);
      if (!section) return;
      if (position >= section.offsetTop && position <= section.offsetTop + section.offsetHeight) {
        navbarlink.classList.add('active');
      } else {
        navbarlink.classList.remove('active');
      }
    });
  };
  window.addEventListener('load', navbarlinksActive);
  onscroll(document, navbarlinksActive);

  /**
   * Scrolls to an element with header offset
   */
  var scrollto = function scrollto(el) {
    var header = select('#header');
    var offset = header.offsetHeight;
    if (!header.classList.contains('header-scrolled')) {
      offset -= 16;
    }
    var elementPos = select(el).offsetTop;
    window.scrollTo({
      top: elementPos - offset,
      behavior: 'smooth'
    });
  };

  /**
   * Header fixed top on scroll
   */
  var selectHeader = select('#header');
  if (selectHeader) {
    var headerOffset = selectHeader.offsetTop;
    var nextElement = selectHeader.nextElementSibling;
    var headerFixed = function headerFixed() {
      if (headerOffset - window.scrollY <= 0) {
        selectHeader.classList.add('fixed-top');
        nextElement.classList.add('scrolled-offset');
      } else {
        selectHeader.classList.remove('fixed-top');
        nextElement.classList.remove('scrolled-offset');
      }
    };
    window.addEventListener('load', headerFixed);
    onscroll(document, headerFixed);
  }

  /**
   * Back to top button
   */
  var backtotop = select('.back-to-top');
  if (backtotop) {
    var toggleBacktotop = function toggleBacktotop() {
      if (window.scrollY > 100) {
        backtotop.classList.add('active');
      } else {
        backtotop.classList.remove('active');
      }
    };
    window.addEventListener('load', toggleBacktotop);
    onscroll(document, toggleBacktotop);
  }

  /**
   * Mobile nav toggle
   */
  on('click', '.mobile-nav-toggle', function (e) {
    select('#navbar').classList.toggle('navbar-mobile');
    this.classList.toggle('bi-list');
    this.classList.toggle('bi-x');
  });

  /**
   * Mobile nav dropdowns activate
   */
  on('click', '.navbar .dropdown > a', function (e) {
    if (select('#navbar').classList.contains('navbar-mobile')) {
      e.preventDefault();
      this.nextElementSibling.classList.toggle('dropdown-active');
    }
  }, true);

  /**
   * Scrool with ofset on links with a class name .scrollto
   */
  on('click', '.scrollto', function (e) {
    if (select(this.hash)) {
      e.preventDefault();
      var navbar = select('#navbar');
      if (navbar.classList.contains('navbar-mobile')) {
        navbar.classList.remove('navbar-mobile');
        var navbarToggle = select('.mobile-nav-toggle');
        navbarToggle.classList.toggle('bi-list');
        navbarToggle.classList.toggle('bi-x');
      }
      scrollto(this.hash);
    }
  }, true);

  /**
   * Scroll with ofset on page load with hash links in the url
   */
  window.addEventListener('load', function () {
    if (window.location.hash) {
      if (select(window.location.hash)) {
        scrollto(window.location.hash);
      }
    }
  });

  /**
   * Preloader
   */
  var preloader = select('#preloader');
  if (preloader) {
    window.addEventListener('load', function () {
      preloader.remove();
    });
  }

  /**
   * Initiate glightbox
   */
  var glightbox = GLightbox({
    selector: '.glightbox'
  });

  /**
   * Skills animation
   */
  var skilsContent = select('.skills-content');
  if (skilsContent) {
    new Waypoint({
      element: skilsContent,
      offset: '80%',
      handler: function handler(direction) {
        var progress = select('.progress .progress-bar', true);
        progress.forEach(function (el) {
          el.style.width = el.getAttribute('aria-valuenow') + '%';
        });
      }
    });
  }

  /**
   * Testimonials slider
   */
  new Swiper('.testimonials-slider', {
    speed: 600,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    slidesPerView: 'auto',
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    }
  });

  /**
   * Porfolio isotope and filter
   */
  window.addEventListener('load', function () {
    var portfolioContainer = select('.portfolio-container');
    if (portfolioContainer) {
      var portfolioIsotope = new Isotope(portfolioContainer, {
        itemSelector: '.portfolio-item'
      });
      var portfolioFilters = select('#portfolio-flters li', true);
      on('click', '#portfolio-flters li', function (e) {
        e.preventDefault();
        portfolioFilters.forEach(function (el) {
          el.classList.remove('filter-active');
        });
        this.classList.add('filter-active');
        portfolioIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
        portfolioIsotope.on('arrangeComplete', function () {
          AOS.refresh();
        });
      }, true);
    }
  });

  /**
   * Initiate portfolio lightbox 
   */
  var portfolioLightbox = GLightbox({
    selector: '.portfolio-lightbox'
  });

  /**
   * Portfolio details slider
   */
  new Swiper('.portfolio-details-slider', {
    speed: 400,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    pagination: {
      el: '.swiper-pagination',
      type: 'bullets',
      clickable: true
    }
  });

  /**
   * Animation on scroll
   */
  window.addEventListener('load', function () {
    AOS.init({
      duration: 1000,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  });

  /**
   * Initiate Pure Counter 
   */
  new PureCounter();
})();
/******/ })()
;