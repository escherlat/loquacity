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
//  LAYER
// **********************************************************

function xLayer(config)
{
  // Initialisiere Objekt-Eigenschaften
  this.__initproperties = function()
  {
    this.css.position = "absolute";
    this.css.width = "auto";
    this.css.height = "auto";
  }

  // Initialisiere x-Koordinate
  this.__initx = function()
  {
    var x;

    x = this.getStyle("left")
    if (x=="auto" || x=="undefined") x=this.obj.offsetLeft
    x = String(x);
    if (x.indexOf("px") != -1) x = parseInt(x.replace("px"));
    this.x = parseInt(x);
  }

  // Initialisiere y-Koordinate
  this.__inity = function()
  {
    var y;

    y = this.getStyle("top")
    if (y=="auto" || y=="undefined") y=this.obj.offsetTop
    y = String(y)
    if (y.indexOf("px") != -1) y = parseInt(y.replace("px"));
    this.y = parseInt(y);
  }

  // Initialisiere Transparenz-Funktionalität
  this.__initalpha = function()
  {
    if (_browser.ie)
      this.obj.style.filter += "alpha(opacity=100)";
    else
      this.setAlpha(100);
  }

  this.__initfunc = function()
  {
    this.faderTimer = null;

    this.sliderJobs = new Array();
    this.sliderRun = false;
    this.sliderCancelAll = false;    
  }


  // ************************
  // MANAGE POSITION
  // ************************

  this.getX = function()
  {
    return this.x;
  }

  this.getY = function()
  {
    return this.y;
  }

  this.setX = function(value, byvalue)
  {
    var diff;
    
    if (typeof(value) == "boolean" && value == false)
    {
      diff = byvalue;
      value = this.getX() + parseFloat(byvalue);
    }
    else
    {
      diff = value - this.getX();
    }  

    if (typeof(value) == "undefined" || !isFinite(value)) return 1;    
    
    this.css.left = String(parseInt(value) + "px");
    this.x = value;
    
    this.checkTracer("setX", false, diff);    
  }
  
  
  this.setXBy = function(value)
  {
    this.setX(false, value);
  }

  
  this.setY = function(value, byvalue)
  {
    var diff;
    
    if (typeof(value) == "boolean" && value == false)
    {
      diff = byvalue;
      value = this.getY() + parseFloat(byvalue);
    }
    else
    {
      diff = value - this.getY();
    }  
    
    if (typeof(value) == "undefined" || !isFinite(value)) return 1;    

    this.css.top = String(parseInt(value) + "px");
    this.y = value;
    
    this.checkTracer("setY", false, diff);    
  }

  
  this.setYBy = function(value)
  {
    this.setY(false, value);
  }
  
  
  this.moveTo = function(x, y)
  {
    this.setX(x);
    this.setY(y);
  }
  
  
  this.placeToY = function(xobj, where)
  {
    var value;

    if (xobj == null) return 1;

    if (typeof(where) == "undefined" || where == "same")
      value = xobj.getVisY()
    else if (where == "under")
      value = xobj.getVisHeight() + xobj.getVisY();

      
    var offset = this.config.get("offsetY");
    
    if (offset && isFinite(offset))
      value += parseFloat(offset);      
    
    this.setY(value);
  }
  

  this.placeToX = function(xobj, where)
  {
    var value, mywidth, mydist;

    if (xobj == null) return 1;

    if (typeof(where) == "undefined" || where == "same")
      value = xobj.getVisX()
    else if (where == "after")
      value = xobj.getVisX() + xobj.getVisWidth();
    else if (where == "middle")
      value = xobj.getVisX() + ((xobj.getVisWidth()/2) - (this.getVisWidth()/2))
    else if (value == "inright")
      value = xobj.getVisX() + xobj.getVisWidth() - this.getVisWidth()
    else if (value == "outright")
      value = xobj.getVisX() + xobj.getVisWidth()

    var offset = this.config.get("offsetX");

    if (offset && isFinite(offset))
      value += parseFloat(offset);      
      
    this.setX(value);
  }
  
  
  
  this.setWidth = function(value)
  {
    if (isFinite(value))    
      this.css.width = parseInt(value) + "px";
  }
  
  this.setHeight = function(value)
  {
    if (isFinite(value))      
      this.css.height = parseInt(value) + "px";
  }
  
  this.setSize = function(x, y)
  {
    this.setWidth(x);
    this.setHeight(y);
  }
  
  this.getWidth = function()
  {
    return parseInt(this.css.width.replace("px", ""));
  }
  
  this.getHeight = function()
  {
    return parseInt(this.css.height.replace("px", ""));
  }  
  
  
  this.setVisWidth = function(value)
  {
    if (!isFinite(value)) return;
    
    this.setWidth(value);
    
    if (this.getVisWidth() < value) return;
    
    for (var i=value; this.getVisWidth() != value; i--)
      this.setWidth(i);
    
    return i;
  }
  
  
  this.setVisHeight = function(value)
  {
    if (!isFinite(value)) return;  
  
    this.setHeight(value);
    
    if (this.getVisHeight() < value) return;    

    for (var i=value; this.getVisHeight() != value; i--)
      this.setHeight(i);

    return i;
  }


  this.scaleToWidth = function(xobj, mod)
  {
    var value = xobj.getVisWidth();

    if (typeof(mod) != "undefined")
      value += parseInt(mod);

    this.setVisWidth(value);
  }

  this.scaleToHeight = function(xobj, mod)
  {
    this.setVisHeight(xobj.getVisHeight());
  }

  this.centerToScreen = function()
  {
    this.setX((_window.getWidth() / 2) - (this.getVisWidth() / 2));
    this.setY((_window.getHeight() / 2) - (this.getVisHeight() / 2));
  }


  // ************************
  // MANAGE ALPHA
  // ************************

  this.setAlpha = function(value)
  {
    // fix malformed value
    var myvalue = value > 100 ? 100 : value < 0 ? 0 : value;
    
    if (typeof(this.obj.filters) == 'object')
      this.obj.filters.alpha.opacity = myvalue;
    else if (this.obj.style.setProperty)
      this.css.setProperty('-moz-opacity', myvalue / 100, '');
  }

  this.getAlpha = function()
  {
    return _browser.gk ? parseFloat(this.css.getPropertyValue("-moz-opacity").replace(",", ".")) * 100 : _browser.ie ? this.obj.filters.alpha.opacity : null
  }

  this.alpha = function(value)
  {
    return typeof(value) == "undefined" ? this.getAlpha() : this.setAlpha(value);
  }


  
  // ************************
  // MANAGE TRANSFORMATIONS
  // ************************  

  this.t_jobs = new Array();  
  this.t_active = false;
  this.t_cancel = false;  
  
  this.transform = function(path)
  {
    var config = new xHash();

    config.set("cancel", false);
    config.set("func", null);
    config.set("timeout", 10);
    config.set("acc", 0);

    config.set("x", this.x);
    config.set("y", this.y);
    config.set("width", this.getWidth());
    config.set("height", this.getHeight());
    config.set("alpha", this.getAlpha());    
   
    config.merge(path);		  
    
    config.set("start_x", this.x);
    config.set("start_y", this.y);
    config.set("start_width", this.getWidth());
    config.set("start_height", this.getHeight());
    config.set("start_alpha", this.getAlpha());        
    
    config.set("starttime", new Date().valueOf());
    config.set("endtime", config.get("starttime") + config.get("duration"));
  
  
    // object isn't already moving so start animation
		if(!this.t_active) 
    {	
      _debug.msg("add job and run it");
      
			this.t_active = true;
			this.t_sub(config);
		} 
    else 
    {
      _debug.msg("add job to list");
    
      // cancel other moves
			if(config.get("cancel")) 
      {		
				this.t_cancel = true;
				this.t_jobs = new Array();
			}

			//add this movement to array of movements
      this.t_jobs.push(config);
		}
  }
  
  
  
  this.t_sub = function(path)
  {
    var now = new Date().valueOf();
    var start = path.get("starttime");
    var end = path.get("endtime")
    
    if (this.t_cancel)
    {
      this.t_active = false;
      this.t_cancel = false;
      
      if (this.t_jobs[0]) this.transform(this.t_jobs.shift());    
    }
    else if (now >= end)
    {
      with(this)
      {
        setX(path.get("x"));
        setY(path.get("y"));
        setWidth(path.get("width"));
        setHeight(path.get("height"));
        setAlpha(path.get("alpha"));
        
        t_active = false;  
      }
      
      var func = path.get("func");
			if(func) eval(func);
      
      if (this.t_jobs[0]) this.transform(this.t_jobs.shift());
    }
    else
    {
	    var percent = (now - start) / (end - start); 
      var acc = path.get("acc");
      var bez = acc != 0 ? new xCoord(0.5+(acc/2),0.5-(acc/2)) : null;
      var stage = _math.getBezier(percent, new xCoord(1,1), new xCoord(0,0), bez).y;
      
      with(this)
      {
        setX(t_value(path, "x", stage));
        setY(t_value(path, "y", stage));
        setWidth(t_value(path, "width", stage));
        setHeight(t_value(path, "height", stage));
        setAlpha(t_value(path, "alpha", stage));    
      }  
    
      var copy = this;
      this.sliderTimer = setTimeout(function() { copy.t_sub(path); }, path.get("timeout"));    
    }
  }

  
  this.t_value = function(path, name, stage)
  {
    var start = path.get("start_" + name);
    var end = path.get(name);
    
    _debug.msg("start: " + start);
    _debug.msg("end: " + end);
    
    return ((end - start) * stage) + start;  
  }
  
  
  
  // ************************
  // INIT XLAYER
  // ************************

  this.type = "xLayer";

  this.__initcore(config);
  this.__initobj();
  this.__initcss();
  this.__initproperties();
  this.__initfunc();
  this.__initx();
  this.__inity();
  this.__initalpha();


}

xLayer.prototype = new xObject();

