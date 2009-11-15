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
//  CORE
// **********************************************************


// returns an object
document.getObj = function(argument)
{
  return typeof(argument) == "object" ? argument : typeof(argument) == "string" ? document.getElementById(argument) : null;
}


// checks if a document based feature exist
document.checkFeature = function(feature)
{
  return (eval("document." + feature)) ? true : false;
}


// smart unction to delete an entry
// and move all entries after nr to new position
Array.prototype.delEntry = function(nr)
{
  for (var i=nr; i<this.length; i++)
    this[i] = this[i+1]

  this.pop();
}

// Calculates Size of 1em
document.getEmSize = function()
{
  var obj = document.createElement("div");
  obj.style.width = "1em";
  obj.style.height = "1em";
  obj.style.position = "absolute";
  document.body.appendChild(obj);
  var emsize = obj.offsetWidth;
  document.body.removeChild(document.body.lastChild);

  return emsize;
}

// retrieves the value  of a cookie
// (returns null if it doesn't exist)
document.getValue = function(name, d)
{
  if (!d) var d = null;
  var arg = name + "=";
  var alen = arg.length;
  var clen = document.cookie.length;
  var i = 0;
  while (i < clen) {
    var j = i + alen;
    if (document.cookie.substring(i, j) == arg) {
      var endstr = document.cookie.indexOf (";", j);
      if (endstr == -1) endstr = document.cookie.length;
      return unescape(document.cookie.substring(j, endstr));
     }
    i = document.cookie.indexOf(" ", i) + 1;
    if (i == 0) break;
  }
  return d;
}


// sets the cookie name with value
// takes optional argument expires which
// is the time in hours till it expires)
document.setValue = function(name, value, expires)
{
  if (expires) {
    var exp = new Date();
    exp.setTime(exp.getTime() + (expires*60*60*1000));
    expires = exp;
  }
  document.cookie = name + "=" + escape(value) + ((expires == null) ? "" : ("; expires=" + expires.toGMTString()));
}


// removes the cookie "name"
document.delValue = function(name)
{
  var exp = new Date();
  exp.setTime (exp.getTime() - 1000);
  var cval = GetCookie (name);
  document.cookie = name + "=" + cval + "; expires=" + exp.toGMTString();
}


document.removeFocusBorders = function()
{
  var tags = [ "img", "input", "a" ];
  for (var i=0; i<tags.length; i++) {
    var nodes = document.getElementsByTagName(tags[i]);
    for (var j=0; j<nodes.length; j++)
      if (tags[i] != "input" || nodes[j].getAttribute("type") != "text")
        nodes[j].onfocus = function() { if(this.blur) this.blur(); }
  }
}

String.prototype.contains = function(string)
{
  return this.indexOf(string) == -1 ? false : true;
}



document.setActiveStyleSheet = function(title)
{
  var styles = document.getElementsByTagName("link");

  for(var i=0; i < styles.length; i++)
    if(styles[i].getAttribute("rel").contains("style") && styles[i].getAttribute("title"))
      styles[i].disabled = styles[i].getAttribute("title") == title ? false : true;

  document.storeActiveStyle();
}


document.getActiveStyleSheet = function()
{
  var styles = document.getElementsByTagName("link");

  for(var i=0; i < styles.length; i++)
    with(styles[i])
      if(getAttribute("rel").contains("style") && getAttribute("title") && !disabled)
        return getAttribute("title");

  return null;
}


document.getPreferredStyleSheet = function()
{
  var styles = document.getElementsByTagName("link");

  for(var i=0; i < styles.length; i++)
    with(styles[i])
      if(getAttribute("rel").contains("style") && !getAttribute("rel").contains("alt") && getAttribute("title"))
        return getAttribute("title");

  return null;
}

document.setActiveStyle = function()
{
  var cookie = document.getValue("style");
  var title = cookie ? cookie : document.getPreferredStyleSheet();

  document.setActiveStyleSheet(title);
}

document.storeActiveStyle = function()
{
  var title = document.getActiveStyleSheet();

  document.setValue("style", title, 365*24);
}

window.onload = document.setActiveStyle
window.onunload = document.storeActiveStyle

