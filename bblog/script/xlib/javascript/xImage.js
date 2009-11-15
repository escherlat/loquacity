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
//  IMAGE
// **********************************************************

function xImage(config)
{

  this.__initobj = function()
  {
    this.obj = new Image();
    this.obj.src = this.config.get("source"); 
  }
  
  // ************************
  // INIT XIMAGE
  // ************************

  this.type = "xImage";

  this.__initcore(config);
  this.__initobj();
  this.__initcss();
  this.__initproperties();
}

xImage.prototype = new xObject();