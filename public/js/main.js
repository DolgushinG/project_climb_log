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
* Template Name: NiceAdmin
* Updated: Nov 17 2023 with Bootstrap v5.3.2
* Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
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
    if (all) {
      select(el, all).forEach(function (e) {
        return e.addEventListener(type, listener);
      });
    } else {
      select(el, all).addEventListener(type, listener);
    }
  };

  /**
   * Easy on scroll event listener
   */
  var onscroll = function onscroll(el, listener) {
    el.addEventListener('scroll', listener);
  };

  /**
   * Sidebar toggle
   */
  if (select('.toggle-sidebar-btn')) {
    on('click', '.toggle-sidebar-btn', function (e) {
      select('body').classList.toggle('toggle-sidebar');
    });
  }

  /**
   * Search bar toggle
   */
  if (select('.search-bar-toggle')) {
    on('click', '.search-bar-toggle', function (e) {
      select('.search-bar').classList.toggle('search-bar-show');
    });
  }

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
   * Toggle .header-scrolled class to #header when page is scrolled
   */
  var selectHeader = select('#header');
  if (selectHeader) {
    var headerScrolled = function headerScrolled() {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled');
      } else {
        selectHeader.classList.remove('header-scrolled');
      }
    };
    window.addEventListener('load', headerScrolled);
    onscroll(document, headerScrolled);
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
   * Initiate tooltips
   */
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  /**
   * Initiate quill editors
   */
  if (select('.quill-editor-default')) {
    new Quill('.quill-editor-default', {
      theme: 'snow'
    });
  }
  if (select('.quill-editor-bubble')) {
    new Quill('.quill-editor-bubble', {
      theme: 'bubble'
    });
  }
  if (select('.quill-editor-full')) {
    new Quill(".quill-editor-full", {
      modules: {
        toolbar: [[{
          font: []
        }, {
          size: []
        }], ["bold", "italic", "underline", "strike"], [{
          color: []
        }, {
          background: []
        }], [{
          script: "super"
        }, {
          script: "sub"
        }], [{
          list: "ordered"
        }, {
          list: "bullet"
        }, {
          indent: "-1"
        }, {
          indent: "+1"
        }], ["direction", {
          align: []
        }], ["link", "image", "video"], ["clean"]]
      },
      theme: "snow"
    });
  }

  /**
   * Initiate TinyMCE Editor
   */
  var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
  var isSmallScreen = window.matchMedia('(max-width: 1023.5px)').matches;
  tinymce.init({
    selector: 'textarea.tinymce-editor',
    plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap quickbars emoticons',
    editimage_cors_hosts: ['picsum.photos'],
    menubar: 'file edit view insert format tools table help',
    toolbar: 'undo redo | bold italic underline strikethrough | fontfamily fontsize blocks | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
    toolbar_sticky: true,
    toolbar_sticky_offset: isSmallScreen ? 102 : 108,
    autosave_ask_before_unload: true,
    autosave_interval: '30s',
    autosave_prefix: '{path}{query}-{id}-',
    autosave_restore_when_empty: false,
    autosave_retention: '2m',
    image_advtab: true,
    link_list: [{
      title: 'My page 1',
      value: 'https://www.tiny.cloud'
    }, {
      title: 'My page 2',
      value: 'http://www.moxiecode.com'
    }],
    image_list: [{
      title: 'My page 1',
      value: 'https://www.tiny.cloud'
    }, {
      title: 'My page 2',
      value: 'http://www.moxiecode.com'
    }],
    image_class_list: [{
      title: 'None',
      value: ''
    }, {
      title: 'Some class',
      value: 'class-name'
    }],
    importcss_append: true,
    file_picker_callback: function file_picker_callback(callback, value, meta) {
      /* Provide file and text for the link dialog */
      if (meta.filetype === 'file') {
        callback('https://www.google.com/logos/google.jpg', {
          text: 'My text'
        });
      }

      /* Provide image and alt text for the image dialog */
      if (meta.filetype === 'image') {
        callback('https://www.google.com/logos/google.jpg', {
          alt: 'My alt text'
        });
      }

      /* Provide alternative source and posted for the media dialog */
      if (meta.filetype === 'media') {
        callback('movie.mp4', {
          source2: 'alt.ogg',
          poster: 'https://www.google.com/logos/google.jpg'
        });
      }
    },
    templates: [{
      title: 'New Table',
      description: 'creates a new table',
      content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>'
    }, {
      title: 'Starting my story',
      description: 'A cure for writers block',
      content: 'Once upon a time...'
    }, {
      title: 'New list with dates',
      description: 'New List with dates',
      content: '<div class="mceTmpl"><span class="cdate">cdate</span><br><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>'
    }],
    template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
    template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
    height: 600,
    image_caption: true,
    quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
    noneditable_class: 'mceNonEditable',
    toolbar_mode: 'sliding',
    contextmenu: 'link image table',
    skin: useDarkMode ? 'oxide-dark' : 'oxide',
    content_css: useDarkMode ? 'dark' : 'default',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
  });

  /**
   * Initiate Bootstrap validation check
   */
  var needsValidation = document.querySelectorAll('.needs-validation');
  Array.prototype.slice.call(needsValidation).forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });

  /**
   * Initiate Datatables
   */
  var datatables = select('.datatable', true);
  datatables.forEach(function (datatable) {
    new simpleDatatables.DataTable(datatable, {
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ru.json'
      },
      perPageSelect: [5, 10, 15, ["All", -1]],
      columns: [{
        select: 2,
        sortSequence: ["desc", "asc"]
      }, {
        select: 3,
        sortSequence: ["desc"]
      }, {
        select: 4,
        cellClass: "green",
        headerClass: "red"
      }]
    });
  });

  /**
   * Autoresize echart charts
   */
  var mainContainer = select('#main');
  if (mainContainer) {
    setTimeout(function () {
      new ResizeObserver(function () {
        select('.echart', true).forEach(function (getEchart) {
          echarts.getInstanceByDom(getEchart).resize();
        });
      }).observe(mainContainer);
    }, 200);
  }
})();
/******/ })()
;