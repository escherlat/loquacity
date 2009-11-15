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
//  SIGNALS (and SLOTS)
// **********************************************************

function xSignalObj(obj)
{
  this.obj = obj;
}

function xSlotObj(obj)
{
  this.obj = obj;
}

function xSignals()
{
  this.slots = new Array();
  this.signals = new Array();

  this.emit = function(func, params)
  {
    _debug.init("signal-emit");

    func(params[0], params[1], params[2], params[3], params[4]);

    for (var i=0; i<this.signals.length; i++)
    {
      if (this.signals[i].obj == func)
      {
        _debug.msg("running slot: " + i);

        this.emit(this.slots[i].obj, params);



      }
    }
  }

  this.connect = function(signal, slot)
  {
    _debug.init("signal-connect");

    var pos = this.signals.length;

    this.signals[pos] = new xSignalObj(signal);
    this.slots[pos] = new xSlotObj(slot);
    
    
  

  }



  this.addCoreEvents = function()
  {
    window.onresize = function() {
      return _signals.emit(_signals_resizeWin, []);
    }

    window.onload = function() {
      return _signals.emit(_signals_loadDoc, []);
    }

    window.onunload = function() {
      return _signals.emit(_signals_unloadDoc, []);
    }

    if (document.body)
    {
      document.body.onclick = function() {
        return _signals.emit(_signals_clickBody, []);
      }
    }
  }
}

function _signals_resizeWin() { return; }
function _signals_loadDoc() { return; }
function _signals_unloadDoc() { return; }
function _signals_clickBody() { return; }
function _signals_clickObject() { return; }
function _signals_message(msg) { window.status = msg; }

_signals = new xSignals();


