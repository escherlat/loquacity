// **********************************************************
//
// Copyright 2002-2003 Sebastian Werner
// http://sebastian-werner.net
// 
// Licensed under:
//
// Attribution-NonCommercial-ShareAlike License 1.0
// by CreativeCommon (http://creativecommons.org)
//
// Key License Terms:
// * Attribution. 
//   The licensor permits others to copy, distribute, 
//   display, and perform the work. In return, licensees 
//   must give the original author credit.
// * Noncommercial. 
//   The licensor permits others to copy, distribute, 
//   display, and perform the work. In return, licensees 
//   may not use the work for commercial purposes -- unless 
//   they get the licensor's permission.
// * Share Alike. 
//   The licensor permits others to distribute derivative 
//   works only under a license identical to the one that 
//   governs the licensor's work.
//
// for details visit:
// http://creativecommons.org/licenses/by-nc-sa/1.0/
//
// **********************************************************

// **********************************************************
//  WINDOW
// **********************************************************

function xWindow() {
  this.getValue = function(value)
  {
    var e1,e2,e3;

    e1 = eval("window.inner" + value)
    e2 = eval("document.documentElement.client" + value)
    e3 = eval("document.body.client" + value)

    return e1 ? e1 : e2 ? e2 : e3 ? e3 : none;
  }

  this.getWidth = function() {
    return this.getValue("Width")
  }

  this.getHeight = function() {
    return this.getValue("Height")
  }

  this.getScrollLeft = function()
  {
    var scrollX;

    if(document.documentElement)
      scrollX = document.documentElement.scrollLeft;
    else if(document.body)
      scrollX = document.body.scrollLeft;
    else
      scrollX = window.pageXOffset;

    return scrollX;
  }

  this.getScrollTop = function()
  {
    var scrollY;

    if(document.documentElement)
      scrollY = document.documentElement.scrollTop;
    else if(document.body)
      scrollY = document.body.scrollTop;
    else
      scrollY = window.pageYOffset;

    return scrollY;
  }



  _debug.init("Initialisiere xWindow");
}

_window = new xWindow();


