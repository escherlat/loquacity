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
//  DEBUG
// **********************************************************

function xDebug()
{
  this.open = function()
  {
    this.win = window.open("", "debugwin", "height=400,width=400,scrollbars=yes,menubar=yes")
    this.print("<html><head><title>xLib Debug Window</title><style type='text/css'>body{font-size: 0.8em;}</style></head><body>")
    this.header("Aktiviere Debug-Fenster");
  }

  this.print = function(value) {
    if (this.active) this.win.document.write(value)
  }

  this.header = function(value) {
    this.print("<h3>" + value + "</h3>")
    this.win.focus();
  }

  this.msg = function(value) {
    var d = new Date();
    this.print("<li><b>" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "." + d.getMilliseconds() + " : </b>" + value + "</li>")
  }

  this.init = function(value) {
    var d = new Date();
    this.print("<li style='color: #0000AA'><b>" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "." + d.getMilliseconds() + " : </b>" + value + "</li>")
  }

  this.warn = function(value) {
    var d = new Date();
    this.print("<li style='color: #AA0000'><b>" + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds() + "." + d.getMilliseconds() + " : </b>" + value + "</li>")
  }

  this.start = function() {
    this.print("<ul>")
  }

  this.stop = function() {
    this.print("</ul>")
  }

  this.enable = function()
  {
    this.active=true;
    if (!this.win) this.open();
  }

  this.disable = function()
  {
    if (this.win) this.win.close();
    this.active=false;
  }

  this.init("Initialisiere xDebug");
  this.active = false;
  this.win = null;
}

_debug = new xDebug();
_debug.disable();

