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
//  OBJECT
// **********************************************************

function xObject(config)
{
  this.__initcore = function(config)
  {
    this.config = new xHash();
    if (config) this.config.merge(config);

    this.depend = new Array();
    this.conflict = new Array();

    this.conflictTable = new xHash();
    this.conflictTable.set("show", "hide");
    this.conflictTable.set("hide", "show");
    this.conflictTable.set("setClass", "resetClass");
  }

  this.__initobj = function()
  {
    // Find Object...
    if (this.config.get("object"))
    {
      this.obj = this.config.get("object");
    }
    if (this.config.get("name"))
    {
      this.obj = document.getElementById(this.config.get("name"));
    }
    else if (this.config.get("tagname"))
    {
      this.obj = document.createElement(this.config.get("tagname"));
    }
    else
    {
      this.obj = document.createElement("div");
    }
  }

  this.__initcss = function()
  {
    // Map style to parent for easier access
    this.css = this.obj.style;
  }

  this.__initproperties = function()
  {

  }

  // ************************
  // MANAGE OBJECT
  // ************************

  this.map = function(dest)
  {
    if (dest == null || typeof(dest) == "undefined")
    {
      document.body.appendChild(this.obj)
    }
    else if (typeof(dest) == "object")
    {
      dest.obj.appendChild(this.obj)
    }
  }
  
  
  this.append = function(obj)
  {
    if (typeof(obj) != "object") return 1;
    
    if (obj.append) obj=obj.obj;
    this.obj.appendChild(obj);
  }


  // ************************
  // MANAGE DEPENDANCIES... 
  //   ...AND CONFLICTS
  // ************************

  this.addDepend = function(xobj, event)
  {
    var pos, isin;  
  
    if (typeof(xobj.depend[event]) == "undefined")
      xobj.depend[event] = new Array();

    pos = xobj.depend[event].length;
    isin = false;
    
    for (var i=0; i<xobj.depend[event].length; i++)
      if (xobj.depend[event][i] == this) isin = true;

    if (!isin) xobj.depend[event][pos] = this;
  }


  this.addConflict = function(xobj, event)
  {
    var pos, isin;
  
    if (typeof(xobj.conflict[event]) == "undefined")
      xobj.conflict[event] = new Array();

    pos = xobj.conflict[event].length;
    isin = false;
    
    for (var i=0; i<xobj.conflict[event].length; i++)
      if (xobj.conflict[event][i] == this) isin = true;

    if (!isin)
    {
      //_debug.msg("add conflict: event[" + event + "]; pos[" + pos + "]")
      xobj.conflict[event][pos] = this;
    }  
  }

  
  this.checkDepend = function(event, options)
  {
    var optstring, cmd;
    
    if (typeof(this.depend[event]) == "undefined") return 1;
    
    optstring=""
    for (var i=0; i<options.length; i++)
    {
      if (typeof(options[i]) == "string")
        optstring = optstring + "'" + options[i] + "'";
      else
        optstring = optstring + options[i];

      if ((i+1) < options.length)
        optstring = optstring + ", "
    }

    for (var i=0; i<this.depend[event].length; i++)
    {
      cmd="this.depend[event][i]." + event + "(" + optstring + ")"
      eval(cmd);
    }
  }


  this.checkConflict = function(event, options)
  {
    var opposite, cmd, optstring;
     
    if (typeof(this.conflict[event]) == "undefined") return 1;
    
    opposite = this.conflictTable.get(event);    
    if (!opposite) return 1;
    
    optstring=""
    for (var i=0; i<options.length; i++)
    {
      if (typeof(options[i]) == "string")
        optstring = optstring + "'" + options[i] + "'";
      else
        optstring = optstring + options[i];

      if ((i+1) < options.length)
        optstring = optstring + ", "
    }

    for (var i=0; i<this.conflict[event].length; i++)
    {
      cmd="this.conflict[event][i]." + opposite + "(" + optstring + ")"
      //_debug.msg("conflict cmd: " + cmd)
      eval(cmd);
    }
  }
  
  
  this.checkTracer = function(event)
  {
    // Optionen (außer event) in neues Array kopieren
    var options = new Array();
    for (var i=1; i<arguments.length; i++) options[i-1] = arguments[i];
    
    //_debug.msg("check tracer start: " + event)
    
    this.checkConflict(event, options);
    this.checkDepend(event, options);
    
    //_debug.msg("check tracer finish: " + event)    
  }


  // ************************
  // MANAGE VISIBILITY
  // ************************

  this.hide = function()
  {
    this.css.visibility = "hidden";
    this.checkTracer("hide");
  }

  this.show = function()
  {
    this.css.visibility = "visible";
    this.checkTracer("show");
  }

  this.toggle = function()
  {
    if (this.css.visibility == "visible" || this.css.visibility == "")
      this.hide();
    else
      this.show();
  }

  this.setClass = function(value)
  {
    this.obj.className = String(value);
    this.checkTracer("setClass", value);    
  }


  this.resetClass = function()
  {
    this.obj.className = "";
  }

  // ************************
  // MANAGE OBJECT
  // ************************

  this.getStyle = function(key)
  {
    var value;

    value = this.css[key];

    if (typeof(value) == "undefined" || value == "")
      if (document.defaultView && document.defaultView.getComputedStyle)
        value = document.defaultView.getComputedStyle(this.obj, "").getPropertyValue(key);
      else if (this.obj.currentStyle)
      	value = this.obj.currentStyle[key];

    return value;
  }

  this.getVisHeight = function()
  {
    return this.obj.offsetHeight;
  }

  this.getVisWidth = function()
  {
    return this.obj.offsetWidth;
  }

  this.getVisX = function()
  {
    var value=0;
    var copy = this.obj;

    while(copy.offsetParent)
    {
      value += copy.offsetLeft;
      copy = copy.offsetParent;
    }

    return value;
  }

  this.getVisY = function()
  {
    var value=0;
    var copy = this.obj;

    while(copy.offsetParent)
    {
      value += copy.offsetTop;
      copy = copy.offsetParent;
    }

    return value;
  }
  
  
  this.removeChilds = function()
  {
    // schöner ist das ja so... tut aber nicht so richtig
    // for(var j=0; j<this.obj.childNodes.length; j++)
    //   this.obj.removeChild(this.obj.childNodes[j]);              
 
    this.obj.innerHTML = "";  
  }

  
  
  
  this.cssCheckSet = function(attribute, list, value)
  {
    var ok = false;
    for (var i=0; i<list.length; i++)
    {
      if (list[i] == value) 
      {
        ok = true;
        break;
      }  
    }
    
    if (!ok) return 1;
    
    this.css[attribute] = value;    
  }
  
  
  
  this.setPosition = function(value)
  {
    var valid = [ "absolute", "relative", "fixed" ];
    this.cssCheckSet("position", valid, value);
  }
  
  this.placeAbsolute = function()
  {
    this.setPosition("absolute");
  }
  
  this.placeRelative = function()
  {
    this.setPosition("relative");
  }

  this.placeFixed = function()
  {
    this.setPosition("fixed");
  }
  

  this.setDisplay = function(value)
  {
    var valid = [ "block", "none", "inline" ];
    this.cssCheckSet("display", valid, value); 
  }
  
  this.displayBlock = function()
  {
    this.setDisplay("block");
  }
  
  this.displayNone = function()
  {
    this.setDisplay("none");
  }

  this.displayInline = function()
  {
    this.setDisplay("inline");
  }
  
  // ************************
  // INIT XOBJECT
  // ************************

  this.type = "xObject";

  this.__initcore(config);
  this.__initobj();
  this.__initcss();
  this.__initproperties();
}

