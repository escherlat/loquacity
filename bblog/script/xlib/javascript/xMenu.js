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
//  MENU
// **********************************************************

function xMenu(config)
{
  this.__initproperties = function()
  {
    this.obj.className = this.config.get("class");
    this.map()

    this.css.position = "absolute";
    this.css.width = "auto";
    this.css.height = "auto";
    this.css.zIndex = "1";

    this.frame = new xObject(new xHash("tagname", "ul"));
    this.append(this.frame)

    this.content = new Array();

    this.config.set("arrowdistance", 18);
  }


  // Funktion zum Hinzufügen eines Seperators
  this.addSeperator = function()
  {
    // Preparing new entry
    var pos = this.content.length;

    this.content[pos] = new Array();

    this.content[pos]["type"] = "seperator";

    this.content[pos]["tag"] = new xObject(new xHash("tagname", "li"));

    this.content[pos]["line"] = new xObject(new xHash("tagname", "div"));

    // Klasse anwenden
    this.content[pos]["tag"].setClass("seperator");
    this.content[pos]["line"].setClass("seperator");

    // Linie und Tag kombinieren
    this.content[pos]["line"].map(this.content[pos]["tag"])

    // Element in Frame integrieren
    this.frame.append(this.content[pos]["tag"])
  }


  // Funktion zum Hinzufügen eines neuen Eintrages
  this.addEntry = function(config, submenu)
  {
    // Preparing new entry
    var pos = this.content.length;

    this.content[pos] = new Array();

    this.content[pos].config = config;

    _debug.msg("AddEntry " + pos + ": " + this.content[pos].config.get("text"))

    this.content[pos]["type"] = "entry";

    // Create Elements
    this.content[pos]["tag"] = new xObject(new xHash("tagname", "li"));
    this.content[pos]["link"] = new xObject(new xHash("tagname", "a"));
    this.content[pos]["menu"] = submenu;

    // Disable focus border
    this.content[pos]["link"].obj.onfocus = function(event) { if (this.blur) this.blur(); }

    // Link und Tag kombinieren
    this.content[pos]["link"].map(this.content[pos]["tag"])

    // Set cursor to default
    this.content[pos]["link"].css.cursor = "default";

    // this.content[pos]["link"].obj.href="javascript://";
    this.content[pos]["link"].obj.href=this.content[pos].config.get("href");

    // Element in Frame integrieren
    this.frame.append(this.content[pos]["tag"])

    // Text vorbereiten
    this.content[pos]["text"] = new xObject(new xHash("tagname", "span"));
    this.content[pos]["text"].obj.paddingRight = "4px";
    this.content[pos]["text"].append(document.createTextNode(config.get("text")));

    if (this.content[pos]["menu"] != null && typeof(this.content[pos]["menu"]) != "undefined")
    {
      // Bei Event Submenü öffnen
      var copy = this.content[pos]["menu"]
      with(this.content[pos]["link"])
      {
        obj.onmouseover = function(event)
        {
          setClass("mark");
          copy.open()
        }
      }

      // Text ersetzen
      this.content[pos]["link"].append(this.content[pos]["text"])


      // Wenn vertikal, dann für Submenüs Pfeil einfügen
      if (this.config.get("orientation") == "vertical")
      {
        // Pfeil erzeugen
        this.content[pos]["arrow"] = new xObject(new xHash("tagname", "span"));

        // Unterschiedliche Browser, unterschiedliche Pfeile...
        if (_browser.ie || _browser.gk)
        {
          with (this.content[pos]["arrow"])
          {
            append(document.createTextNode(""));
            css.fontFamily = "Webdings";
            obj.firstChild.nodeValue = "4";
          }
        }
        else
        {
          this.content[pos]["arrow"].append(document.createTextNode(String.fromCharCode(9654)));
        }

        // Link um erzeugten Pfeil erweitern
        this.content[pos]["link"].append(this.content[pos]["arrow"])
      }

      // Da dies ein Untermenü ist, wird es per default versteckt
      this.content[pos]["menu"].hide();

      // Menü soll als den Text des aufrufenden Punktes wissen
      this.content[pos]["menu"].title = config.get("text");
      this.content[pos]["menu"].parent = this.content[pos];

      // Abhängigkeit zu Eltern-Teil einstellen
      this.content[pos]["menu"].addDepend(this, "hide");
      this.content[pos]["menu"].addDepend(this, "setX");
      this.content[pos]["menu"].addDepend(this, "setY");
    }
    else
    {
      var copy = this
      with(this.content[pos]["link"])
      {
        obj.onmouseover = function(event)
        {
          setClass("mark");
          copy.closeChilds();
        }
      }

      // Link um Text erweitern
      this.content[pos]["link"].append(this.content[pos]["text"])
    }

    this.render();
  }


  // Erstellt automatisch die für ein Menü nötigen Konfliktdaten.
  // Einträge auf gleicher Ebene können nicht gleichzeitig
  // geöffnet sein. Es wird weiterhin nur einer dieser Punkte
   // als aktiv markiert werden.
  this.conflictCache = function()
  {
    for (var i=0; i<this.content.length; i++)
    {
      for (var j=0; j<this.content.length; j++)
      {
        // Wenn er nicht ich ist :) und beides Entries sind
        if (j!=i && this.content[i]["type"] == "entry" && this.content[j]["type"] == "entry")
        {
          if (this.content[i]["menu"] && this.content[j]["menu"])
            this.content[i]["menu"].addConflict(this.content[j]["menu"], "open");

          this.content[i]["link"].addConflict(this.content[j]["link"], "setClass");
        }
      }
    }
  }


  // Führt diverse Operationen zur Optimierung des Designs durch
  // Zur Zeit:
  // - Richtet die Pfeile für Links mit Submenüs aus
  this.updateMenuStyle = function()
  {
    var framewidth, maxwidth, mywidth, diff, calc;

    // Pfeil-Optimierung nur in vertikalen Menüs...
    if (this.config.get("orientation") == "vertical")
    {
      maxwidth = 0;
      framewidth = this.frame.getVisWidth()

      // Wichtig... evtl. vorher gesetztes Padding zurücksetzen
      for (var i=0; i<this.content.length; i++)
        if (this.content[i]["type"] == "entry" && this.content[i]["arrow"])
          this.content[i]["arrow"].css.paddingLeft = "0"

      // Support Rerender Bug in Moz (Tested with v1.3)
      // Rerender Frame after reset padding values...
      this.frame.css.display = "none";
      this.frame.css.display = "block";

      // Find max width
      for (var i=0; i<this.content.length; i++)
      {
        if (this.content[i]["type"] == "entry")
        {
          // Text-Breite ermitteln
          mywidth = this.content[i]["text"].getVisWidth()

          // Wenn mit Pfeil, dann diesen mitberechnen
          if (this.content[i]["arrow"])
            mywidth += this.content[i]["arrow"].getVisWidth()

          // Neuer Höchstwert?
          if (mywidth > maxwidth) maxwidth=mywidth;
        }
      }

      // Wenn keine Breite ermittelt werden konnte, dann fehlerhaft beenden
      if (maxwidth == 0)
      {
        _debug.warn("Die maximale Breite konnte nicht ermittelt werden!");
        return 1;
      }

      diff = framewidth - maxwidth;
      for (var i=0; i<this.content.length; i++)
      {
        if (this.content[i]["arrow"])
        {
          calc = ((framewidth + this.config.get("arrowdistance")) - diff - this.content[i]["text"].getVisWidth() - this.content[i]["arrow"].getVisWidth());
          this.content[i]["arrow"].css.paddingLeft = parseInt(calc) + "px"
        }
      }
    }
  }


  // Positioniert alle Submenüs neu.
  // Ruft vorher updateMenuStyle() auf, damit das
  // eigene Layout korrekt ist, bevor die Objekte
  // in Abhängigkeit platziert werden.
  this.updateMenuPosition = function()
  {
    for (var i=0; i<this.content.length; i++)
    {
      if (this.content[i]["menu"])
      {
        // Menü am aufrufenden Element ausrichten
        if (this.config.get("orientation") == "vertical")
        {
          this.content[i]["menu"].placeToX(this, "after")
          this.content[i]["menu"].placeToY(this.content[i]["tag"], "same")
        }
        else if (this.config.get("orientation") == "horizontal")
        {
          this.content[i]["menu"].placeToX(this.content[i]["tag"], "same")
          this.content[i]["menu"].placeToY(this, "under")
        }

        // Z-Index des Menüs einen höher setzen als vom Elternelement
        this.content[i]["menu"].css.zIndex = parseInt(this.css.zIndex) + 1
      }
    }
  }


  this.open = function()
  {
    this.show();
    this.checkTracer("open");
  }


  this.close = function()
  {
    this.closeChilds();
    this.hide();
    this.checkTracer("close");
  }

  this.closeChilds = function()
  {
    for (var i=0; i<this.content.length; i++)
    {
      if (this.content[i]["menu"])
        this.content[i]["menu"].close();

      if (this.content[i]["type"] == "entry")
        this.content[i]["link"].resetClass();
    }
  }

  this.rerender = function(depth)
  {
    if (typeof(this.depth) == "undefined")
      this.depth = typeof(depth) == "undefined" ? 0 : depth;

    this.rendered = true;

    _debug.msg("ReRender: " + this.depth + ": " + this.title);

    this.updateMenuStyle();
    this.updateMenuPosition();
    this.conflictCache();

    for (var i=0; i<this.content.length; i++)
      if (this.content[i]["menu"])
        this.content[i]["menu"].rerender(this.depth+1);

  }

  this.render = function(param)
  {
    // Wenn Menü schon mal gerendert wurde, dann dies hier updaten...
    if (this.rendered || param)
    {
      _debug.warn("EventNeuRendern");
      this.rerender();
    }
  }

  this.delEntry = function(nr)
  {
    if (nr >= this.content.length) return;

    _debug.msg("DeleteEntry: " + nr)

    if (this.content[nr]["menu"])
      this.content[nr]["menu"].close();

    this.frame.obj.removeChild(this.content[nr]["tag"].obj);
    this.content.delEntry(nr);
    this.render();
  }

  this.setupAsMain = function()
  {
    var copy = this;
    document.body.onclick = function() { copy.closeChilds(); }

    //_signals.connect(_signals_clickBody, function() { copy.closeChilds(); });
  }

  // ************************
  // INIT XMENU
  // ************************

  this.type = "xMenu";
  this.rendered = false;

  this.__initcore(config);
  this.__initobj();
  this.__initcss();
  this.__initproperties();
  this.__initfunc();
  this.__initx();
  this.__inity();
  this.__initalpha();

  this.conflictTable.set("open", "close");
  this.conflictTable.set("close", "open");
}

xMenu.prototype = new xLayer();
