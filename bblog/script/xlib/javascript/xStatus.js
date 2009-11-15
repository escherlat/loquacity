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
//  STATUS
// **********************************************************

function xStatus(config)
{
  this.set = function(value)
  {
    this.value = parseFloat(value);
    this.render();
  }
  
  this.reset = function()
  {
    this.value = 0;
    this.render();    
  }
  
  this.render = function()
  {
    //this.setter.css.width = parseInt(this.value) + "%"; 
    window.status = "Rendere Menü: " + this.value;
  }  

  this.__initproperties = function()
  {
    /*
    this.css.position = "absolute";
    this.css.width = "300px";
    this.css.height = "10px";
    this.css.fontSize = "1px";
    this.css.padding = "1px";
    this.css.border = "1px solid #000000";    
    
    this.setX(100);
    this.setY(50);
    
    this.setter = new xObject(new xHash("tagname", "div"));
    this.setter.css.height = "100%";
    this.setter.css.width = "0";
    this.setter.css.backgroundColor = "#990000";
    
    this.append(this.setter)    
    */
  }

  // ************************
  // INIT XSTATUS
  // ************************

  this.type = "xStatus";
  
  this.__initcore(config);
  this.__initobj();
  this.__initcss();
//  this.__initproperties();
//  this.__initfunc();
//  this.__initx();
//  this.__inity();
//  this.__initalpha();

  this.map();  
  this.reset();


}

xStatus.prototype = new xObject();