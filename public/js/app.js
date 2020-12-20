/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 1);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/js/my.js":
/*!****************************!*\
  !*** ./resources/js/my.js ***!
  \****************************/
/*! no static exports found */
/***/ (function(module, exports) {

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

function exchange(select) {
  $('.' + select + '-ra').on('change', function (e) {
    var res = $('.' + select + '-ra').val() * $('.' + select + '-ca').val();
    $('.' + select + '-res').val(res);
    $('.' + select + '-prov').html($('.' + select + '-ca').val() * 0.9957);
  });
  $('.' + select + '-ca').on('change', function (e) {
    var res = $('.' + select + '-ra').val() * $('.' + select + '-ca').val();
    $('.' + select + '-res').val(res);
    $('.' + select + '-prov').html($('.' + select + '-ca').val() * 0.9957);
  });
  /*    $('.fa-lock').on('click', function () {
          $(this).removeClass('fa-lock');
          $(this).addClass('fa-lock-open');
      });
  
      $('.fa-lock-open').on('click', function () {
          $(this).removeClass('fa-lock-open');
          $(this).addClass('fa-lock');
      });*/
}

$(document).ready(function () {
  $(".confirm").click(function (e) {
    var txt = $(this).data("txt");
    txt = txt == undefined ? "Operacja wymaga potwierdzenia" : txt;
    var c = confirm(txt);

    if (c == true) {} else {
      return false;
    }
  });
  $("#uploadImage").on("change", function (e) {
    var oFReader = new FileReader();
    oFReader.readAsDataURL(document.getElementById("uploadImage").files[0]);

    oFReader.onload = function (oFREvent) {
      document.getElementById("uploadPreview").src = oFREvent.target.result;
    };
  });
  $(".form-autosubmit").on("change", function (e) {
    e.preventDefault();
    this.submit();
  });

  if ($('.select2').length > 0) {
    $('.select2').select2();
  }

  $('.toggleHiddenRow').on("click", function () {
    if ($('#offers-wrapper').hasClass('all-offers-visible')) {
      $('.toggleHiddenRow').toggleClass('hidden');
      $('.more-results').addClass('hidden');
      $('#offers-wrapper').removeClass('all-offers-visible');
    } else {
      $('.toggleHiddenRow').toggleClass('hidden');
      $('.more-results').removeClass('hidden');
      $('#offers-wrapper').addClass('all-offers-visible');
    }
  }); //exchange fields

  exchange('buy');
  exchange('sell'); ///exchange/offers/check

  setInterval(function () {
    $.get('/exchange/offers/check');
  }, 5000);
  setInterval(function () {
    $.get('/cron/stonks/maker');
  }, 60000 * 3);
});

/***/ }),

/***/ 1:
/*!**********************************!*\
  !*** multi ./resources/js/my.js ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! B:\Users\Michal\Desktop\STUDIA\Semestr VI\Inżynieria systemów informatycznych\kryptowaluty\resources\js\my.js */"./resources/js/my.js");


/***/ })

/******/ });