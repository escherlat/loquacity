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
//  HASH
// **********************************************************

function xHash()
{
  this.set = function(key, value)
  {
    if (typeof(value) != "undefined" && value != null)
    {
      var epos = this.findPos(key);
      var pos = epos != -1 ? epos : this.length;

    	this.keys[pos] = key;
    	this.values[pos] = value;

      if (pos == this.length) this.length++;
    }
  }

  this.findPos = function(key)
  {
    for (var i=0; i<this.keys.length; i++)
      if (this.keys[i] == key)
        return i;

    return -1;
  }


  this.get = function(key)
  {
    var pos = this.findPos(key);
    return pos == -1 ? null : this.values[pos];
  }

  this.remove = function(key)
  {
  	// to do...

  }

  // converts an array to a xHash
  this.convert = function(list)
  {
    for (var i=0; i<list.length; i+=2)
      this.set(list[i], list[i+1])
  }

  // return current size of
  this.size = function()
  {
    return this.length
  }

  // merges two xHash's
  this.merge = function(ohash)
  {
  	if (typeof(ohash) != "object") return 1;

    for (var i=0; i<ohash.keys.length; i++)
      this.set(ohash.keys[i], ohash.values[i])
  }
  

  this.store = function()
  {
    for (var i=0; i<this.keys.length; i++)    
      document.setValue(this.keys[i], this.values[i], 8640);    
  }
  
  
  this.load = function(name)
  {
    this.set(name, document.getValue(name));    
  } 


  this.length = 0;

  this.values = new Array();
  this.keys = new Array();

  this.convert(arguments)
}

