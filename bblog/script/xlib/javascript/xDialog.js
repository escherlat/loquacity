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
//  DIALOG
// **********************************************************

function xDialog(config)
{
  this.addText = function(text)
  {
    this.append(document.createTextNode(text));
  }

  this.addSelect = function(hash)
  {
    var sel = document.createElement("select");
    var selopt = new Array();

    for (var i=0; i<hash.keys.length; i++)
    {
      selopt[i] = new Array();
      selopt[i][0] = document.createElement("option");
      selopt[i][1] = document.createTextNode(hash.values[i]);

      selopt[i][0].setAttribute("value", hash.keys[i])
      selopt[i][0].appendChild(selopt[i][1]);
      sel.appendChild(selopt[i][0]);
    }

    this.append(sel);
  }

  this.addBreak = function()
  {
    this.append(document.createElement("br"));
  }



/*

    var sbox = document.createElement("select");

    sbox.setAttribute("id", "theme");

    var sboxh = document.createTextNode("Farbschema: ");

    var styles = document.getElementsByTagName("link");
    var stcache = new Array();
    var j=0;
    for(var i=0; i < styles.length; i++)
    {
      stcache[j] = new Array();
      stcache[j][0] = document.createElement("option");
      stcache[j][1] = document.createTextNode(styles[i].title);

      stcache[j][0].setAttribute("value", styles[i].title)
      stcache[j][0].appendChild(stcache[j][1]);
      sbox.appendChild(stcache[j][0]);

      j++;
    }


    this.setupFrame.append(sboxh);
    this.setupFrame.append(sbox);

    var btnstore = document.createElement("div");
    btnstore.className = "button";
    btnstore.innerHTML = "Speichern";
    btnstore.onclick = function()
    {
      var obj = document.getElementById("theme");
      document.setActiveStyleSheet(obj.value);
    }

   this.setupFrame.append(btnstore);



*/


  // ************************
  // INIT XDIALOG
  // ************************

  this.type = "xDialog";

  this.__initcore(config);
  this.__initobj();
  this.__initcss();
  this.__initproperties();
  this.__initfunc();
  this.__initx();
  this.__inity();
  this.__initalpha();
}

xDialog.prototype = new xLayer();
