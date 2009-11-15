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
//  COLUMNS
// **********************************************************

function xColumns(config)
{
  this.__initproperties = function()
  {
    this.splitable = new xHash();  
    this.splitable.set("span", true)    
    this.splitable.set("table", true)
    this.splitable.set("tbody", true)    
    this.splitable.set("td", true)
    this.splitable.set("ul", true)
    this.splitable.set("ol", true)
    this.splitable.set("div", true)

    this.topelements = new xHash();
    this.topelements.set("h1", true);
    this.topelements.set("h2", true);
    this.topelements.set("h3", true);
    this.topelements.set("h4", true);
    this.topelements.set("h5", true);
    this.topelements.set("h6", true);                        
    
    this.splittext = new xHash();
    this.splittext.set("p", true);
    
    // This holds all columns created later
    this.columns = new Array();
  
    // Hide original content
    this.css.display = "none";
 
    this.prozsum = 0;
    this.prozcount = 0;
    
    this.pos = 0;
  }
  


  
  // **********************************************
  //  INTERNAL - FUNCTIONS
  // **********************************************    
  
  // Internal ... to get a tagname of a given node
  this.tagname = function(node)
  {
    return node.tagName.toLowerCase();
  }
  
  // Internal ... to get the handling for a adv-setting
  this.flag = function(name, tag)
  {
    var value = eval("this." + name + ".get('" + tag + "')");
    if (typeof(value) == "undefined") value = false;    
    
    return value;
  }

  
  
  
  // **********************************************
  //  MAIN-NODE-SPLITTER - FUNCTION
  // **********************************************    
  
  this.sub = function(parents)
  {
    var tag, backup, last, lastobj;
    var splitable, topelem, splittext;
    
    var current = parents[parents.length-1];
    var childs = current.childNodes;    

    var last = -1;
    var count = 0;
    var i = 0;
    
    
    // rebuild parents in helplayer
    lastobj = this.rebuildParents(parents)

    // Moving childs to columns     
    for (i=0; i<childs.length; i++)
    {
      // only handle node if type is equal to tag
      if (childs[i].nodeType != 1) continue;
      
      // increment counter
      count++;

      // get tagname
      tag = this.tagname(childs[i]);
      
      // advanced handling data
      splitable = this.flag("splitable", tag);
      splittext = this.flag("splittext", tag);
      topelem = this.flag("topelements", tag) 
      topelem = last == -1 ? topelem : topelem && !this.flag("topelements", this.tagname(childs[last]));
      
      // if this is a topelement
      if (topelem && childs.length > (i+1))
      {
        // first try to copy full node with all childs...
        lastobj.appendChild(childs[i].cloneNode(true));

        // find next child and append
        var foundhead=false;
        for (var j=i+1; j<childs.length; j++)
          if (childs[j].nodeType == 1)
          {
            foundhead=true;
            break;
          }

        if (foundhead)
          lastobj.appendChild(childs[j].cloneNode(true));
      }
      else
      {      
        // first try to copy full node with all childs
        lastobj.appendChild(childs[i].cloneNode(true));  
      }  

      // check if the helpframe is ok in size        
      if (this.recalcHeight() > this.config.get("realheight"))
      {
        if (splitable)
        {
          // remove the whole created node-set
          this.removeLastChild(1)
          
          // build new parents array
          var data = new Array();

          // copy parents from this call       
          for (var n=0; n<parents.length; n++)
            data[n] = parents[n];          
          
          // and add current child to list
          data[data.length] = childs[i]
          
          // call myself with new params
          this.sub(data);
          
          // goto next child
          continue;
        }
        else if (splittext)
        {
          // holds nodes for the next column
          var remain = new Array();
          var allchilds = lastobj.lastChild.childNodes;
          
          var text, words, cache, rest, tagobj;          

          // revert loop through the childs           
          for (var k=allchilds.length - 1; k>=0; k--)
          {
            // if it is a real subtag
            if (allchilds[k].nodeType == 1)
            {
              // copy to remaining list
              remain[remain.length] = allchilds[k].cloneNode(true);
              lastobj.lastChild.removeChild(allchilds[k]);      
              
              if (this.recalcHeight() <= this.config.get("realheight")) break;
            }

            // or if it is normal text
            else if(allchilds[k].nodeType == 3)
            {
              // some cache variables
              cache = allchilds[k].nodeValue
              words = cache.split(" ");
              
              // first check if it's small enough if all text will be removed
              allchilds[k].nodeValue = "";
              if (this.recalcHeight() > this.config.get("realheight"))
              {
                // copy to remaining list
                remain[remain.length] = document.createTextNode(cache);
                lastobj.lastChild.removeChild(allchilds[k]); 
              }
              else
              {
                // restore node data
                allchilds[k].nodeValue = cache;
                rest = "";
              
                // revert loop through words
                for (var l=words.length-1; l>=0; l--)
                {
                  // caching string
                  rest = words[l] + " " + rest;
                  
                  // build new text string
                  text = lastobj.lastChild.lastChild.nodeValue;
                  text = text.substr(0, text.length-words[l].length-1) 
                
                  // replacing nodeValue
                  lastobj.lastChild.lastChild.nodeValue = text;
                
                  if (this.recalcHeight() <= this.config.get("realheight"))
                    break;               
                }
                
                // put string to remaining list
                remain[remain.length] = document.createTextNode(rest);
              
                if (this.recalcHeight() <= this.config.get("realheight"))
                  break;
              } 
            }              
          }
          
          
          if (remain.length > 0)
          {
            // before delete content add values to the stat
            this.updateStat();
            this.publishHelpLayer();
            this.clearHelpLayer();               
            
            // rebuild parents in helplayer
            lastobj = this.rebuildParents(parents)               
 
            // creating parent-tag           
            tagobj = document.createElement(tag);
            
            // revert loop through cache to restore childs
            for (var m=remain.length-1; m>=0; m--)
              tagobj.appendChild(remain[m]);  
            
            // append temporary child to parent
            lastobj.appendChild(tagobj);
          }
        }        
        else
        {
          // remove last child          
          this.removeLastChild(parents.length);          
          
          // if a topelement we need to remove to elements
          if (topelem)
            this.removeLastChild(parents.length);          
      
          // before delete content add values to the stat
          this.updateStat();
          this.publishHelpLayer();
          this.clearHelpLayer();
          
          // rebuild parents in helplayer
          lastobj = this.rebuildParents(parents)          
          
          // copy full node with all childs to the fresh helplayer
          lastobj.appendChild(childs[i].cloneNode(true));
          
          // Setze Klasse für erstes Element in Spalte
          lastobj.lastChild.className = this.config.get("topclass");
            
          // is it always to big?
          if (this.recalcHeight() > this.config.get("realheight"))
          {
            _debug.warn("Element ist zu gross!");

            // goto next child
            continue;          
          }
  
        }
      }
      else if (topelem)
      {
        // remove last child          
        this.removeLastChild(parents.length);
      }
      
      // before goto next, store current position
      last = i;
    }
    

    // If this is the end: 
    // Clear help layer and publish last part of the document 
    // and don't create a new column :)
    if (parents.length == 1)
    {
      this.publishHelpLayer(false);
      this.clearHelpLayer();
    }  
  }
  
  

  // **********************************************
  //  RENDER - FUNCTIONS
  // **********************************************    
  
  // put structure of parents to columns
  this.rebuildParents = function(parents)
  {
    var last, lastobj, i;

    // Parents rekonstruieren, dabei den Allerersten
    // nicht beachten (das ist der source-frame :))
    last = "this.help.obj"
    for (i=1; i<parents.length; i++)
    {
      lastobj = eval(last);
      lastobj.appendChild(parents[i].cloneNode(false))
      last += ".lastChild"; 
    }
    
    lastobj = eval(last);
    
    return lastobj;
  }
  
  
  // remove last child by given length of parents
  this.removeLastChild = function(plength)
  {
    var last, lastobj, i;

    last = "this.help.obj";

    for (i=0; i<(plength-1); i++)
      last += ".lastChild"; 
          
    lastobj = eval(last);
    lastobj.removeChild(lastobj.lastChild);
  }
  

  // this updates the internal stats
  this.updateStat = function()
  {
    if (this.config.get("useStats"))
    {
      var cheight = this.recalcHeight();
      
      this.prozsum += ((cheight / this.config.get("realheight")) * 100);
      this.prozcount ++;
      this.prozmiddle = Math.round(this.prozsum / this.prozcount);
    }  
  }
 
  
  // publish to new column
  this.publishHelpLayer = function(param)
  {
    // Inhalt publizieren
    this.columns[this.columns.length-1].obj.innerHTML = this.help.obj.innerHTML;

    if (typeof(param) == "undefined" || param) 
      this.createColumn();    
  }
  
  
  // remove all content from the helplayer
  this.clearHelpLayer = function()
  {
    var backup;
    
    // reset old data from help layer
    this.help.removeChilds();
  }
  

  

  // Liefere aktuelle Höhe vom Hilfslayer
  this.recalcHeight = function()
  {
    var height;

    // only display in the moment to calculate dimensions
    // otherwise hide
    this.help.css.display = "block";
    height = this.help.getVisHeight();
    this.help.css.display = "none";
  
    return height;
  }
  
  
  
  
  // **********************************************
  //  HELPLAYER - FUNCTIONS
  // **********************************************  
  

  // Initialisiert den Hilfslayer
  this.createHelpLayer = function()
  {
    // create and map helplayer
    this.help = new xLayer();
    this.help.map();
  }
  
  
  this.renderHelpLayer = function()
  {
    // style it like a column...
    this.setupColumnStyle(this.help);    
    
    // ...but reset height to "auto" 
    // to support dynamic sizing...
    this.help.css.height = "auto";
    
    // and finally hide it
    this.help.css.display = "none";
    this.help.css.visibility = "hidden";
  }
  

  
  
  
  
  // **********************************************
  //  COLUMN - FUNCTIONS
  // **********************************************  
  
  this.setupColumnStyle = function(obj, pos)
  {
    with(obj)
    {
      setClass(this.config.get("columnclass"));
      setWidth(this.config.get("realwidth"));
      setHeight(this.config.get("realheight"));
      setY(0);
      
      if (typeof(pos) != "undefined")       
        setX((this.config.get("colWidth") + this.config.get("margin")) * pos);
    } 
  }
  
  
  // Erstellt eine neue Spalte
  this.createColumn = function()
  {
    var pos = this.columns.length;
    
    _debug.msg("Erstelle Spalte: " + pos);

    this.columns[pos] = new xLayer();

    if (pos > 0)
      this.columns[pos].addDepend(this.columns[pos-1], "setX");
    
    this.mover.append(this.columns[pos]);    
    this.setupColumnStyle(this.columns[pos], pos);
  }
  
  

  
  
  
  
  
  // **********************************************
  //  NAVIGATION - FUNCTIONS
  // **********************************************
  
  this.jumpto = function(value)
  {
    if (value == this.pos || value < 0 || value >= this.columns.length) return;

    var x = (this.config.get("colWidth") + this.config.get("margin")) * value * -1;
    var dotx = (this.config.get("dotWidth") + this.config.get("dotMargin")) * value;
   
    if (this.config.get("useSliding"))
    {
      var path = new xHash();
      path.set("x", x);
      path.set("duration", 2000);
      path.set("acc", -0.999);
      path.set("cancel", true);
  
      this.mover.transform(path);
      
      path.set("x", dotx);
      
      this.dotmarker.transform(path);      
    }  
    else  
    {
      this.mover.setX(x);   
      this.dotmarker.setX(dotx);  
    }

    this.pos = value;
  }
  
  this.prev = function()
  {
    this.jumpto(this.pos - 1);
  }

  this.next = function()
  {
    this.jumpto(this.pos + 1);
  }
 
  this.end = function()
  {
    this.jumpto(this.columns.length - this.config.get("columns"));  
  }
  
  this.start = function()
  {
    this.jumpto(0); 
  }
  
  
  
  // **********************************************
  //  NAVIGATION - COLUMN-DOT'S
  // **********************************************
 
  this.renderNavigation = function()
  {
    this.navi.placeToY(this.frame, "under");
    this.navi.placeToX(this.frame, "same");
    
    this.navi.scaleToWidth(this.frame);
    this.navi.setHeight(this.config.get("dotHeight") + (2 * this.config.get("dotMargin")));
    
    this.navi.removeChilds();

    var copy = this;

    // creating a dot for each column
    this.dots = new Array();
    for (var i=0; i<this.columns.length; i++)
    {
      this.dots[i] = new xLayer();

      this.dots[i].setSize(10,10);

      this.dots[i].displayInline();

      this.dots[i].css.margin = this.config.get("dotMargin") + "px";
      this.dots[i].css.left = (i * (this.config.get("dotWidth") + this.config.get("dotMargin"))) + "px";
      this.dots[i].setClass(this.config.get("dotClass"));

      // store position in object
      this.dots[i].obj.pos = i;

      this.navi.append(this.dots[i]);

      // make it clickable
      this.dots[i].obj.onclick = function() { copy.jumpto(this.pos) }
    }



    this.dotmarker = new xLayer();
    this.dotmarker.map();
    this.dotmarker.placeAbsolute();
    this.dotmarker.setSize(this.config.get("dotWidth"), this.config.get("dotHeight"));
    this.dotmarker.css.margin = this.config.get("dotMargin") + "px";
    this.dotmarker.css.zIndex = "1000";
    this.dotmarker.setClass(this.config.get("dotMarkerClass"));
    this.navi.append(this.dotmarker);

    this.btnnext = new xObject(new xHash("tagname", "span"));
    this.navi.append(this.btnnext);
    this.btnnext.setClass(this.config.get("tabClassRight"));
    this.btnnext.obj.innerHTML = "&#187;"
    this.btnnext.obj.onclick = function(e) { copy.next(); }

    this.printlink = new xObject(new xHash("tagname", "span"));
    this.navi.append(this.printlink);
    this.printlink.setClass(this.config.get("tabClassRight"));
    this.printlink.obj.innerHTML = this.config.get("printTitle");
    this.printlink.obj.onclick = function(e)
    {
      var p;

      p = window.open(window.location.href + '?print', 'printpreview','height=400,width=600,menubar=no,scrollbars=yes,status=no,statusbar=no,locationbar=no');
      p.focus();
      p.print();
    }

    this.btnback = new xObject(new xHash("tagname", "span"));
    this.navi.append(this.btnback);
    this.btnback.setClass(this.config.get("tabClassRight"));
    this.btnback.obj.innerHTML = "&#171;"
    this.btnback.obj.onclick = function(e) { copy.prev(); }



    var links = document.getElementsByTagName("link");
    this.btncss = new Array();
    var j=0;

    for (var i=0; i<links.length; i++)
    {
      if (links[i].getAttribute("rel").contains("stylesheet") && !links[i].getAttribute("media").contains("print"))
      {
        this.btncss[j] = new xObject(new xHash("tagname", "span"));
        this.navi.append(this.btncss[j]);

        if (j==0) this.btncss[j].css.marginRight = "25px";

        this.btncss[j].setClass(this.config.get("tabClassRight"));
        this.btncss[j].obj.innerHTML = links[i].getAttribute("title");
        this.btncss[j].obj.onclick = function(e) { document.setActiveStyleSheet(this.innerHTML); }

        j++;
      }
    }


  }


  this.createNavigation = function()
  {
    this.navi = new xLayer();
    this.navi.setClass(this.config.get("naviclass"));
    this.navi.placeAbsolute();
    this.navi.map();
  }

  


  // **********************************************
  //  UTIL - FUNCTIONS
  // **********************************************
  
  // mainrender funtion, mainly to easily call this
  this.renderColumns = function()
  {
    _debug.msg("RenderColumns");
    
    this.sub([this.obj]);
  }

  this.wheel = function()
  {
    if (event.wheelDelta < 0) this.prev();
    else this.next();
  }  

  


  
  // **********************************************
  //  LAYOUT - FUNCTIONS
  // **********************************************
  
  // create a dummy column and measure the real size needed to create
  // the real ones afterwards 
  this.recalcCache = function()
  {
    _debug.msg("RecalcCache");
    
    var dummy = new xLayer();
    dummy.map()
    dummy.setClass(this.config.get("columnclass"));

    var acolumns = this.config.get("columns");
    var emargin = this.config.get("margin");
    var amargin = emargin * (acolumns - 1);  
    var ewidth = Math.round((this.config.get("width") - amargin) / acolumns);    
    var eheight = this.config.get("height");  
  
    this.config.set("colHeight", eheight);
    this.config.set("colWidth", ewidth);

    this.config.set("realheight", dummy.setVisHeight(eheight));
    this.config.set("realwidth", dummy.setVisWidth(ewidth));
    
    _debug.msg("calculated height: " + this.config.get("realheight") + "; width: " + this.config.get("realwidth"));
  
    // remove created dummy... 
    document.body.removeChild(document.body.lastChild);
  }
  
  this.createLayout = function()
  {
    _debug.msg("CreateLayout");
  
    // create main content frame  
    this.frame = new xLayer();
    this.frame.map();
    this.frame.placeAbsolute();
    this.frame.css.overflow = "hidden";  
    this.frame.setClass(this.config.get("frameclass"));

    // create moveable inner layer
    this.mover = new xLayer();
    this.mover.placeRelative();
    this.mover.moveTo(0, 0);
    this.frame.append(this.mover)
  }

  this.renderLayout = function()
  {
    _debug.msg("RenderLayout");
  
    with(this)
    {
      frame.moveTo(config.get("offsetX"), config.get("offsetY"));
      frame.setSize(config.get("width"), config.get("height"));
    }  
  }
  
  this.renderMover = function()
  {
    _debug.msg("RenderMover");
  
    this.columns = new Array();
    this.mover.removeChilds();
    this.createColumn();
  }  
  
  
  this.create = function()
  {
    this.createLayout();
    this.createHelpLayer();  
    this.createNavigation();
  }
  
  this.render = function()
  {
    this.recalcCache(); 
    
    this.renderLayout();
    this.renderHelpLayer();
    this.renderMover();
    this.renderColumns();  
    this.renderNavigation();
  }
  
  
  // ************************
  // INIT XCOLUMNS
  // ************************

  this.type = "xColumns";

  this.__initcore(config);
  this.__initobj();
  this.__initcss();
  this.__initproperties();
  this.__initfunc();
  this.__initx();
  this.__inity();
  this.__initalpha();
  
  // Create Layers
  this.create();
  
  // Render all
  this.render();
}

xColumns.prototype = new xLayer();  
