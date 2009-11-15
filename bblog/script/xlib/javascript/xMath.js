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
//  MATH
//  Some functions are build with scripts from 13parallel.org
// **********************************************************


// Coordinate constructor
function xCoord(x,y)
{
	if(!x) var x=0; 
  if(!y) var y=0; 
  
  return {x: x, y: y};
}

function xMath()
{
	// Bezier functions:
	this.b1 = function(t) { return (t*t*t); }
	this.b2 = function(t) { return (3*t*t*(1-t)); }
	this.b3 = function(t) { return (3*t*(1-t)*(1-t)); }
	this.b4 = function(t) { return ((1-t)*(1-t)*(1-t)); }
	
	// Finds the coordinates of a point at a certain stage through a bezier curve
	this.getBezier = function(percent, startPos, endPos, control1, control2) 
  {
		// If there aren't any extra control points plot a straight line, if there is only 1
		// make 2nd point same as 1st
		if(!control2 && !control1) var control2 = new xCoord(startPos.x + 3*(endPos.x-startPos.x)/4, startPos.y + 3*(endPos.y-startPos.y)/4);
		if(!control2) var control2 = control1;
		if(!control1) var control1 = new xCoord(startPos.x + (endPos.x-startPos.x)/4, startPos.y + (endPos.y-startPos.y)/4);
				
		var pos = new xCoord();
		pos.x = startPos.x * this.b1(percent) + control1.x * this.b2(percent) + control2.x * this.b3(percent) + endPos.x * this.b4(percent);
		pos.y = startPos.y * this.b1(percent) + control1.y * this.b2(percent) + control2.y * this.b3(percent) + endPos.y * this.b4(percent);
		
		return pos;
	}  
}

var _math = new xMath();
