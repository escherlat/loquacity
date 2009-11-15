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
//  BROWSER
// **********************************************************

function xBrowser()
{
  // Features
  this.all = document.all ? true : false;
  this.layers = document.layers ? true : false;
  this.dom = document.getElementById ? true : false;
  this.dom2events = (document.addEventListener && document.removeEventListener) ? true : false;
  this.dom2core = (document.createTextNode && document.firstChild && document.getElementsByTagName && document.createElement && document.createComment) ? true : false;
  this.dom2 = (this.dom2events && this.dom2core) ? true : false;

  // Indetifikation
  this.vendor = navigator.vendor ? navigator.vendor.toLowerCase() : false;
  this.product = navigator.product ? navigator.product.toLowerCase() : false;

  // Modus der Darstellung
  this.stdmode = document.compatMode == "CSS1Compat" ? true : false;

  // Browser
  this.ic = (window.ScriptEngine && ScriptEngine().indexOf( 'InScript' ) + 1) ? true : false;

  this.op = window.opera ? true : false;
  this.op7 = this.op && this.dom2;
  this.op6 = this.op && !this.op7

  this.kq = (this.vendor == "kde") ? true : false;
  this.sf = (this.mac && this.vendor == "kde") ? true : false;
  this.gk = (this.dom2 && document.defaultView && this.product == "gecko") ? true : false;

  this.ie = (document.all && !this.kq && !this.op) ? true : false;
  this.ie6 = (this.ie && this.dom2 && document.fireEvent && document.createComment) ? true : false;
  this.ie55 = (this.ie && document.fireEvent && !this.dom2) ? true : false;
  this.ie5 = (this.ie && !document.fireEvent) ? true : false
  // last expression seems to create errors
  // this.ie5mac = (this.ie && this.dom && !document.mimeType) ? true : false;
  this.ie4 = (this.ie && !this.ie6 && !this.ie55 && !this.ie5) ? true : false;


  _debug.init("Initialisiere xBrowser");
}

_browser = new xBrowser();


