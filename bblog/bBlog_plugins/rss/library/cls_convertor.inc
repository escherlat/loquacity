<?php /*
________________________________________________________________________________
 PROJECT convertor
  version 1.1
  czech encoding conversion class
  http://pavouk.net/tools/convertor
________________________________________________________________________________
 LICENCE
  
Copyright (C) 2002 Robert äevËÌk

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

Robert äevËÌk
robert.sevcik@pavouk.net

________________________________________________________________________________
 NOTE

  You cannot use one instance for multiple streaming data because it holds open
  entity information. On the other side it's usable with multiple closed data.
  To reset data contents call the constructor again

________________________________________________________________________________
 CHANGE LOG

  convertor 1.1 - the conversion tabs are now stored in class members
                    not as former globals
                - function conv_tab_set moved in class method

  convertor 1.0 - first official version (replaces old translator)
  
________________________________________________________________________________
 EXAMPLES

  to test the convertor class use for example:
   
      windows-1250:   ¯û - Ëeötina
      utf-8:          ≈ô≈æ - ƒçe≈°tina
      iso-8859-2:     ¯æ - Ëeπtina

  to test the examples just type / before {CR}{LF} on this line *
      
    //example A:________________________________________________________________
    // simple use: whole data-pack conversion

      echo "<hr>";
      echo "converting '¯û - Ëeötina' from windows-1250 to iso-8859-2";
      echo "<hr>";
      
      $t = new convertor('W1250','I88592','¯û - Ëeötina');
      echo $t->pull();
      
      // > '¯æ - Ëeπtina'
      
      unset($t);
      
    //example B:________________________________________________________________
    // advanced use: stream data conversion
    // (with respect to multichar encoded letters)

      echo "<hr>";
      echo "converting '≈ô≈æ - ƒçe≈°tina' from utf-8 to windows-1250";
      echo "<hr>";
          
      $t = new convertor('UTF8','W1250');

      $t->push('≈');
      
      echo $t->pull();
      
      // > ''
      
      $t->push('ô');
      $t->push('≈');
      $t->push('æ');
      $t->push(' - ƒ');
      echo $t->pull();
      
      // > '¯û - '
      
      $t->push('çe≈°tina');
      echo $t->pull();

      // > '¯û - Ëeötina'

      unset($t);

    //end of examples___________________________________________________________
      
/*
________________________________________________________________________________
 CODE */


class convertor{

  var $data;
  var $fe;
  var $te;
  
  var $NB_left;
  var $NB_char;
  var $may_be_e;  
  
  var $W1250_U;
  var $I88592_U;
  
  // INTERFACE *****************************************************************

  function convertor($f_encoding='', $t_encoding='', $data=null){
    foreach($this as $k=>$v) unset($this->$k);

    if(!ereg("(W1250)|(I88592)|(UTF8)|(UE)",$f_encoding)) return null;
    if(!ereg("(W1250)|(I88592)|(UTF8)|(UE)",$t_encoding)) return null;

    $this->fe=$f_encoding;
    $this->te=$t_encoding;
    $this->data = $data;
    
    //$this->NB_left is used to store the count of NB of actualy proccessed NB char
    //$this->NB_char is used to store the U code of actualy proccessed NB char
    $this->NB_left=0;
    $this->NB_char=0;
    //$this->may_be_e is used do parse entities
    $this->may_be_e = "";

    $this->conv_tab_set();

  }
  function push($data){
    $this->data .= $data;
  }
  function pull(){
    $fe = $this->fe;
    $te = $this->te;
    $data = $this->data;
    $this->data = null;
    return $this->convert($data, $fe, $te);
  }

  // IMPLEMENTENTATION *********************************************************

  function convert($stream, $from, $to){
  
    if($from == $to) return $stream;
  
    if($from == "W1250" && $to == "I88592" || $from == "I88592" && $to == "W1250"){
      $stream = $this->convert($stream, $from, "UTF8");
      $from = "UTF8";
    }
    if($from == "UTF8" && $to == "UE" || $from == "UE" && $to == "UTF8"){
      $stream = $this->convert($stream, $from, "W1250");
      $from = "W1250";
    }
  
    $conv_fce = "S_".$from."_".$to;
  
    return $this->$conv_fce($stream);
  }

  // conv_fce ------------------------------------------------------------------
  Function S_UE_W1250($stream){
    return $this->S_UE_X($stream, "C_U_W1250");
  }
  Function S_UE_I88592($stream){
    return $this->S_UE_X($stream, "C_U_I88592");
  }
  function S_W1250_UE($stream){
    return $this->S_X_UE($stream, "C_W1250_U");
  }
  function S_I88592_UE($stream){
    return $this->S_X_UE($stream, "C_I88592_U");
  }
  
  Function S_UTF8_W1250($stream){
    return $this->S_UTF8_X($stream, "C_U_W1250");
  }
  Function S_UTF8_I88592($stream){
    return $this->S_UTF8_X($stream, "C_U_I88592");
  }
  function S_W1250_UTF8($stream){
    return $this->S_X_UTF8($stream, "C_W1250_U");
  }
  function S_I88592_UTF8($stream){
    return $this->S_X_UTF8($stream, "C_I88592_U");
  }
  // ---------------------------------------------------------------------------
  
  // convert 1B encoded stream to Unicode Entities encoded
  function S_X_UE($stream, $C_X_U_function){
    /*
    C_X_U_function may be:
    C_W1250_U
    C_I88592_U
    */
    $strlen = strlen($stream);
    $buffer = "";
    for($i = 0; $i<$strlen; $i++){
      $char = $stream[$i];
      $ord = ord($char);
      $U = $this->$C_X_U_function($ord);
      if( ($U!=$ord) || ($U>=0x80) ) $buffer .= $this->CHAR_UE($U);
      else $buffer .= $char;
    }
    return $buffer;
  }
  // convert Unicode Entities encoded stream to 1B encoded 
  Function S_UE_X($stream, $C_U_X_function){
    /*
    C_U_X_function may be
    C_U_W1250
    C_U_I88592
    */

    //$this->may_be_e is used to store actualy proccessed entity
  
    $strlen = strlen($stream);
    $buffer = "";
    for($i = 0; $i<$strlen; $i++){
      $char = $stream[$i];
  
      if("" == $this->may_be_e){
        if("&" == $char)
          $this->may_be_e .= $char;
  
      }elseif("&" == $this->may_be_e){
        if("#" == $char)
          $this->may_be_e .= $char;
        else{
          $buffer .= $this->may_be_e;
          $this->may_be_e = "";
        }
  
      }elseif("&#" == $this->may_be_e){
        if(ereg("[0-9x]", $char))
          $this->may_be_e .= $char;
        else{
          $buffer .= $this->may_be_e;
          $this->may_be_e = "";
        }
  
      }elseif(ereg("^&#[0-9]+$", $this->may_be_e)){
        if(ereg("[0-9;]", $char))
          $this->may_be_e .= $char;
        else{
          $buffer .= $this->may_be_e;
          $this->may_be_e = "";
        }
  
      }elseif(ereg("^&#x[0-9A-Fa-f]+$", $this->may_be_e)){
        if(ereg("[0-9A-Fa-f;]", $char))
          $this->may_be_e .= $char;
        else{
          $buffer .= $this->may_be_e;
          $this->may_be_e = "";
        }
  
      }elseif(ereg("^&#x[0-9A-Fa-f]*$", $this->may_be_e)){
        if(ereg("[0-9A-Fa-f]", $char))
          $this->may_be_e .= $char;
        else{
          $buffer .= $this->may_be_e;
          $this->may_be_e = "";
        }
  
      }
        
      if("" <> $this->may_be_e){
        if(ereg("^&#(x?)([0-9A-Fa-f]+);$", $this->may_be_e, $a_regs)){
          if("x" != $a_regs[1]) $U = $a_regs[2];
          else $U = hexdec("0x".$a_regs[2]);
          $U *= 1;
          $this->may_be_e = "";
          $buffer .= chr($this->$C_U_X_function($U));  
        }
      }
      else
        $buffer .= $char;  
    }
    return $buffer;
  }
  // convert 1B encoded stream to UTF-8 encoded
  function S_X_UTF8($stream, $C_X_U_function){
    /*
    C_X_U_function may be:
    C_W1250_U
    C_I88592_U
    */
  
    $strlen = strlen($stream);
    $buffer = "";
    for($i = 0; $i<$strlen; $i++){
      $char = $stream[$i];
      $ord = ord($char);
      $U = $this->$C_X_U_function($ord);
      $U = $this->C_U_UTF8($U);
      $buffer .= $this->CHAR_UTF8($U);
    }
    return $buffer;
  } 
  // convert UTF-8 encoded stream to 1B encoded 
  function S_UTF8_X($stream, $C_U_X_function){
    /*
    C_U_X_function may be
    C_U_W1250
    C_U_I88592
    */

    //$this->NB_left is used to store the count of NB of actualy proccessed NB char
    //$this->NB_char is used to store the U code of actualy proccessed NB char
    
    $strlen = strlen($stream);
    $buffer = "";
  
    for($i=0;$i<$strlen;$i++){
      $c = ord($stream[$i]);
  
      if($this->NB_left == 0) $this->NB_char = 0;
  
      if(($c & 0xc0) == 0x80){
        // 10vvvvvv dokoncovani nB utf znaku
        $this->NB_char |= ($c & 0x03f)<<(6*($this->NB_left-1)); //!
        $this->NB_left--;
      }elseif(($c & 0xe0) == 0xc0){
        // 110vvvvv zacatek 2B utf znaku
        $this->NB_char |= ($c & 0x1f)<<6;
        $this->NB_left = 1;
      }elseif(($c & 0xf0) == 0xe0){
        // 1110vvvv zacatek 3B utf znaku
        $this->NB_char |= ($c & 0x0f)<<12;
        $this->NB_left = 2;
      }elseif(($c & 0xf8) == 0xf0){
        // 11110vvv zacatek 4B utf znaku
        $this->NB_char |= ($c & 0x07)<<18;
        $this->NB_left = 3;
      }else{
        // 0vvvvvvv 1B znak
        // kdyby byl $this->NB_left>0 je tam chyba
        $this->NB_char = $c;
        $this->NB_left = 0;
      }
      if($this->NB_left==0){
        $buffer .= chr($this->$C_U_X_function($this->NB_char));
      }
    }
    return $buffer;
    
  }

  // char code convert fce -----------------------------------------------------
  Function C_W1250_U($c){
    return $this->W1250_U[floor($c)];
  }
  Function C_U_W1250($c){
    return array_search(floor($c), $this->W1250_U);
  }
  Function C_I88592_U($c){
    return $this->I88592_U[floor($c)];
  }
  Function C_U_I88592($c){
    return array_search(floor($c), $this->I88592_U);
  }
                                  // UNICODE
                                  // 0000 0000  0000 0000  0000 0000  0vvv vvvv
                                  // 0000 0000  0000 0000  0000 0vvv  vvvv vvvv
                                  // 0000 0000  0000 0000  vvvv vvvv  vvvv vvvv
                                  // 0000 0000  000v vvvv  vvvv vvvv  vvvv vvvv
                                
                                  // UTF8
                                  // 0000 0000  0000 0000  0000 0000  0vvv vvvv
                                  // 0000 0000  0000 0000  110v vvvv  10vv vvvv
                                  // 0000 0000  1110 vvvv  10vv vvvv  10vv vvvv
                                  // 1111 0vvv  10vv vvvv  10vv vvvv  10vv vvvv
  
  function C_UTF8_U($c){
    if($c<0x80) $wc = $c; 
    elseif($c < 0x10000)      $wc = ($c & 0x3f) | ($c & 0x1f00)>>2;   
    elseif($c < 0x1000000)    $wc = ($c & 0x3f) | ($c & 0x1f00)>>2 | ($c & 0x0f0000)>>4;  
    elseif($c < 4294967296 /*0x100000000*/)  $wc = ($c & 0x3f) | ($c & 0x1f00)>>2 | ($c & 0x0f0000)>>4 | ($c & 0x07000000)>>6;
    return $wc;
  }
  function C_U_UTF8($c){
    if($c < 0x80) $wc = $c;
    elseif($c < 0x800)       $wc = 0xc080 | ($c & 0x3f) | ($c & 0x1f00>>2) << 2;
    elseif($c < 0x10000)   $wc = 0xe08080 | ($c & 0x3f) | ($c & 0x3f00>>2) << 2 | ($c & 0x1f0000>>4) << 4;
    elseif($c < 0x10000) $wc = 0xf0808080 | ($c & 0x3f) | ($c & 0x3f00>>2) << 2 | ($c & 0x3f0000>>4) << 4 | ($c & 0x1f000000>>6) << 6;
    return $wc;
  }
  // ---------------------------------------------------------------------------

  // char from code fce --------------------------------------------------------
  Function CHAR_UTF8($c){
    $buffer = "";
    if($c & 0xff000000)
    $buffer .= chr(($c & 0xff000000)>>24);
    if($c & 0xff0000 || $buffer)
    $buffer .= chr(($c & 0xff0000)>>16);
    if($c & 0xff00 || $buffer)
    $buffer .= chr(($c & 0xff00)>>8);
    if($c & 0xff || $buffer)
    $buffer .= chr($c & 0xff);
    return $buffer;
  }
  function CHAR_UE($c){
    return "&#x".dechex($c).";";
  }
  // ---------------------------------------------------------------------------

    
  function conv_tab_set(){
    
    $W1250_U[0x00] =  0x0000;  #  NULL
    $W1250_U[0x01] =  0x0001;  #  START OF HEADING
    $W1250_U[0x02] =  0x0002;  #  START OF TEXT
    $W1250_U[0x03] =  0x0003;  #  END OF TEXT
    $W1250_U[0x04] =  0x0004;  #  END OF TRANSMISSION
    $W1250_U[0x05] =  0x0005;  #  ENQUIRY
    $W1250_U[0x06] =  0x0006;  #  ACKNOWLEDGE
    $W1250_U[0x07] =  0x0007;  #  BELL
    $W1250_U[0x08] =  0x0008;  #  BACKSPACE
    $W1250_U[0x09] =  0x0009;  #  HORIZONTAL TABULATION
    $W1250_U[0x0a] =  0x000a;  #  LINE FEED
    $W1250_U[0x0b] =  0x000b;  #  VERTICAL TABULATION
    $W1250_U[0x0c] =  0x000c;  #  FORM FEED
    $W1250_U[0x0d] =  0x000d;  #  CARRIAGE RETURN
    $W1250_U[0x0e] =  0x000e;  #  SHIFT OUT
    $W1250_U[0x0f] =  0x000f;  #  SHIFT IN
    $W1250_U[0x10] =  0x0010;  #  DATA LINK ESCAPE
    $W1250_U[0x11] =  0x0011;  #  DEVICE CONTROL ONE
    $W1250_U[0x12] =  0x0012;  #  DEVICE CONTROL TWO
    $W1250_U[0x13] =  0x0013;  #  DEVICE CONTROL THREE
    $W1250_U[0x14] =  0x0014;  #  DEVICE CONTROL FOUR
    $W1250_U[0x15] =  0x0015;  #  NEGATIVE ACKNOWLEDGE
    $W1250_U[0x16] =  0x0016;  #  SYNCHRONOUS IDLE
    $W1250_U[0x17] =  0x0017;  #  END OF TRANSMISSION BLOCK
    $W1250_U[0x18] =  0x0018;  #  CANCEL
    $W1250_U[0x19] =  0x0019;  #  END OF MEDIUM
    $W1250_U[0x1a] =  0x001a;  #  SUBSTITUTE
    $W1250_U[0x1b] =  0x001b;  #  ESCAPE
    $W1250_U[0x1c] =  0x001c;  #  FILE SEPARATOR
    $W1250_U[0x1d] =  0x001d;  #  GROUP SEPARATOR
    $W1250_U[0x1e] =  0x001e;  #  RECORD SEPARATOR
    $W1250_U[0x1f] =  0x001f;  #  UNIT SEPARATOR
    $W1250_U[0x20] =  0x0020;  #  SPACE
    $W1250_U[0x21] =  0x0021;  #  EXCLAMATION MARK
    $W1250_U[0x22] =  0x0022;  #  QUOTATION MARK
    $W1250_U[0x23] =  0x0023;  #  NUMBER SIGN
    $W1250_U[0x24] =  0x0024;  #  DOLLAR SIGN
    $W1250_U[0x25] =  0x0025;  #  PERCENT SIGN
    $W1250_U[0x26] =  0x0026;  #  AMPERSAND
    $W1250_U[0x27] =  0x0027;  #  APOSTROPHE
    $W1250_U[0x28] =  0x0028;  #  LEFT PARENTHESIS
    $W1250_U[0x29] =  0x0029;  #  RIGHT PARENTHESIS
    $W1250_U[0x2a] =  0x002a;  #  ASTERISK
    $W1250_U[0x2b] =  0x002b;  #  PLUS SIGN
    $W1250_U[0x2c] =  0x002c;  #  COMMA
    $W1250_U[0x2d] =  0x002d;  #  HYPHEN-MINUS
    $W1250_U[0x2e] =  0x002e;  #  FULL STOP
    $W1250_U[0x2f] =  0x002f;  #  SOLIDUS
    $W1250_U[0x30] =  0x0030;  #  DIGIT ZERO
    $W1250_U[0x31] =  0x0031;  #  DIGIT ONE
    $W1250_U[0x32] =  0x0032;  #  DIGIT TWO
    $W1250_U[0x33] =  0x0033;  #  DIGIT THREE
    $W1250_U[0x34] =  0x0034;  #  DIGIT FOUR
    $W1250_U[0x35] =  0x0035;  #  DIGIT FIVE
    $W1250_U[0x36] =  0x0036;  #  DIGIT SIX
    $W1250_U[0x37] =  0x0037;  #  DIGIT SEVEN
    $W1250_U[0x38] =  0x0038;  #  DIGIT EIGHT
    $W1250_U[0x39] =  0x0039;  #  DIGIT NINE
    $W1250_U[0x3a] =  0x003a;  #  COLON
    $W1250_U[0x3b] =  0x003b;  #  SEMICOLON
    $W1250_U[0x3c] =  0x003c;  #  LESS-THAN SIGN
    $W1250_U[0x3d] =  0x003d;  #  EQUALS SIGN
    $W1250_U[0x3e] =  0x003e;  #  GREATER-THAN SIGN
    $W1250_U[0x3f] =  0x003f;  #  QUESTION MARK
    $W1250_U[0x40] =  0x0040;  #  COMMERCIAL AT
    $W1250_U[0x41] =  0x0041;  #  LATIN CAPITAL LETTER A
    $W1250_U[0x42] =  0x0042;  #  LATIN CAPITAL LETTER B
    $W1250_U[0x43] =  0x0043;  #  LATIN CAPITAL LETTER C
    $W1250_U[0x44] =  0x0044;  #  LATIN CAPITAL LETTER D
    $W1250_U[0x45] =  0x0045;  #  LATIN CAPITAL LETTER E
    $W1250_U[0x46] =  0x0046;  #  LATIN CAPITAL LETTER F
    $W1250_U[0x47] =  0x0047;  #  LATIN CAPITAL LETTER G
    $W1250_U[0x48] =  0x0048;  #  LATIN CAPITAL LETTER H
    $W1250_U[0x49] =  0x0049;  #  LATIN CAPITAL LETTER I
    $W1250_U[0x4a] =  0x004a;  #  LATIN CAPITAL LETTER J
    $W1250_U[0x4b] =  0x004b;  #  LATIN CAPITAL LETTER K
    $W1250_U[0x4c] =  0x004c;  #  LATIN CAPITAL LETTER L
    $W1250_U[0x4d] =  0x004d;  #  LATIN CAPITAL LETTER M
    $W1250_U[0x4e] =  0x004e;  #  LATIN CAPITAL LETTER N
    $W1250_U[0x4f] =  0x004f;  #  LATIN CAPITAL LETTER O
    $W1250_U[0x50] =  0x0050;  #  LATIN CAPITAL LETTER P
    $W1250_U[0x51] =  0x0051;  #  LATIN CAPITAL LETTER Q
    $W1250_U[0x52] =  0x0052;  #  LATIN CAPITAL LETTER R
    $W1250_U[0x53] =  0x0053;  #  LATIN CAPITAL LETTER S
    $W1250_U[0x54] =  0x0054;  #  LATIN CAPITAL LETTER T
    $W1250_U[0x55] =  0x0055;  #  LATIN CAPITAL LETTER U
    $W1250_U[0x56] =  0x0056;  #  LATIN CAPITAL LETTER V
    $W1250_U[0x57] =  0x0057;  #  LATIN CAPITAL LETTER W
    $W1250_U[0x58] =  0x0058;  #  LATIN CAPITAL LETTER X
    $W1250_U[0x59] =  0x0059;  #  LATIN CAPITAL LETTER Y
    $W1250_U[0x5a] =  0x005a;  #  LATIN CAPITAL LETTER Z
    $W1250_U[0x5b] =  0x005b;  #  LEFT SQUARE BRACKET
    $W1250_U[0x5c] =  0x005c;  #  REVERSE SOLIDUS
    $W1250_U[0x5d] =  0x005d;  #  RIGHT SQUARE BRACKET
    $W1250_U[0x5e] =  0x005e;  #  CIRCUMFLEX ACCENT
    $W1250_U[0x5f] =  0x005f;  #  LOW LINE
    $W1250_U[0x60] =  0x0060;  #  GRAVE ACCENT
    $W1250_U[0x61] =  0x0061;  #  LATIN SMALL LETTER A
    $W1250_U[0x62] =  0x0062;  #  LATIN SMALL LETTER B
    $W1250_U[0x63] =  0x0063;  #  LATIN SMALL LETTER C
    $W1250_U[0x64] =  0x0064;  #  LATIN SMALL LETTER D
    $W1250_U[0x65] =  0x0065;  #  LATIN SMALL LETTER E
    $W1250_U[0x66] =  0x0066;  #  LATIN SMALL LETTER F
    $W1250_U[0x67] =  0x0067;  #  LATIN SMALL LETTER G
    $W1250_U[0x68] =  0x0068;  #  LATIN SMALL LETTER H
    $W1250_U[0x69] =  0x0069;  #  LATIN SMALL LETTER I
    $W1250_U[0x6a] =  0x006a;  #  LATIN SMALL LETTER J
    $W1250_U[0x6b] =  0x006b;  #  LATIN SMALL LETTER K
    $W1250_U[0x6c] =  0x006c;  #  LATIN SMALL LETTER L
    $W1250_U[0x6d] =  0x006d;  #  LATIN SMALL LETTER M
    $W1250_U[0x6e] =  0x006e;  #  LATIN SMALL LETTER N
    $W1250_U[0x6f] =  0x006f;  #  LATIN SMALL LETTER O
    $W1250_U[0x70] =  0x0070;  #  LATIN SMALL LETTER P
    $W1250_U[0x71] =  0x0071;  #  LATIN SMALL LETTER Q
    $W1250_U[0x72] =  0x0072;  #  LATIN SMALL LETTER R
    $W1250_U[0x73] =  0x0073;  #  LATIN SMALL LETTER S
    $W1250_U[0x74] =  0x0074;  #  LATIN SMALL LETTER T
    $W1250_U[0x75] =  0x0075;  #  LATIN SMALL LETTER U
    $W1250_U[0x76] =  0x0076;  #  LATIN SMALL LETTER V
    $W1250_U[0x77] =  0x0077;  #  LATIN SMALL LETTER W
    $W1250_U[0x78] =  0x0078;  #  LATIN SMALL LETTER X
    $W1250_U[0x79] =  0x0079;  #  LATIN SMALL LETTER Y
    $W1250_U[0x7a] =  0x007a;  #  LATIN SMALL LETTER Z
    $W1250_U[0x7b] =  0x007b;  #  LEFT CURLY BRACKET
    $W1250_U[0x7c] =  0x007c;  #  VERTICAL LINE
    $W1250_U[0x7d] =  0x007d;  #  RIGHT CURLY BRACKET
    $W1250_U[0x7e] =  0x007e;  #  TILDE
    $W1250_U[0x7f] =  0x007f;  #  DELETE
    $W1250_U[0x80] =  0x0080;  #  NOT USED
    $W1250_U[0x81] =  0x0081;  #  NOT USED
    $W1250_U[0x82] =  0x201a;  #  SINGLE LOW-9 QUOTATION MARK
    $W1250_U[0x83] =  0x0083;  #  NOT USED
    $W1250_U[0x84] =  0x201e;  #  DOUBLE LOW-9 QUOTATION MARK
    $W1250_U[0x85] =  0x2026;  #  HORIZONTAL ELLIPSIS
    $W1250_U[0x86] =  0x2020;  #  DAGGER
    $W1250_U[0x87] =  0x2021;  #  DOUBLE DAGGER
    $W1250_U[0x88] =  0x0088;  #  NOT USED
    $W1250_U[0x89] =  0x2030;  #  PER MILLE SIGN
    $W1250_U[0x8a] =  0x0160;  #  LATIN CAPITAL LETTER S WITH CARON
    $W1250_U[0x8b] =  0x2039;  #  SINGLE LEFT-POINTING ANGLE QUOTATION MARK
    $W1250_U[0x8c] =  0x015a;  #  LATIN CAPITAL LETTER S WITH ACUTE
    $W1250_U[0x8d] =  0x0164;  #  LATIN CAPITAL LETTER T WITH CARON
    $W1250_U[0x8e] =  0x017d;  #  LATIN CAPITAL LETTER Z WITH CARON
    $W1250_U[0x8f] =  0x0179;  #  LATIN CAPITAL LETTER Z WITH ACUTE
    $W1250_U[0x90] =  0x0090;  #  NOT USED
    $W1250_U[0x91] =  0x2018;  #  LEFT SINGLE QUOTATION MARK
    $W1250_U[0x92] =  0x2019;  #  RIGHT SINGLE QUOTATION MARK
    $W1250_U[0x93] =  0x201c;  #  LEFT DOUBLE QUOTATION MARK
    $W1250_U[0x94] =  0x201d;  #  RIGHT DOUBLE QUOTATION MARK
    $W1250_U[0x95] =  0x2022;  #  BULLET
    $W1250_U[0x96] =  0x2013;  #  EN DASH
    $W1250_U[0x97] =  0x2014;  #  EM DASH
    $W1250_U[0x98] =  0x0098;  #  NOT USED
    $W1250_U[0x99] =  0x2122;  #  TRADE MARK SIGN
    $W1250_U[0x9a] =  0x0161;  #  LATIN SMALL LETTER S WITH CARON
    $W1250_U[0x9b] =  0x203a;  #  SINGLE RIGHT-POINTING ANGLE QUOTATION MARK
    $W1250_U[0x9c] =  0x015b;  #  LATIN SMALL LETTER S WITH ACUTE
    $W1250_U[0x9d] =  0x0165;  #  LATIN SMALL LETTER T WITH CARON
    $W1250_U[0x9e] =  0x017e;  #  LATIN SMALL LETTER Z WITH CARON
    $W1250_U[0x9f] =  0x017a;  #  LATIN SMALL LETTER Z WITH ACUTE
    $W1250_U[0xa0] =  0x00a0;  #  NO-BREAK SPACE
    $W1250_U[0xa1] =  0x02c7;  #  CARON
    $W1250_U[0xa2] =  0x02d8;  #  BREVE
    $W1250_U[0xa3] =  0x0141;  #  LATIN CAPITAL LETTER L WITH STROKE
    $W1250_U[0xa4] =  0x00a4;  #  CURRENCY SIGN
    $W1250_U[0xa5] =  0x0104;  #  LATIN CAPITAL LETTER A WITH OGONEK
    $W1250_U[0xa6] =  0x00a6;  #  BROKEN BAR
    $W1250_U[0xa7] =  0x00a7;  #  SECTION SIGN
    $W1250_U[0xa8] =  0x00a8;  #  DIAERESIS
    $W1250_U[0xa9] =  0x00a9;  #  COPYRIGHT SIGN
    $W1250_U[0xaa] =  0x015e;  #  LATIN CAPITAL LETTER S WITH CEDILLA
    $W1250_U[0xab] =  0x00ab;  #  LEFT-POINTING DOUBLE ANGLE QUOTATION MARK
    $W1250_U[0xac] =  0x00ac;  #  NOT SIGN
    $W1250_U[0xad] =  0x00ad;  #  SOFT HYPHEN
    $W1250_U[0xae] =  0x00ae;  #  REGISTERED SIGN
    $W1250_U[0xaf] =  0x017b;  #  LATIN CAPITAL LETTER Z WITH DOT ABOVE
    $W1250_U[0xb0] =  0x00b0;  #  DEGREE SIGN
    $W1250_U[0xb1] =  0x00b1;  #  PLUS-MINUS SIGN
    $W1250_U[0xb2] =  0x02db;  #  OGONEK
    $W1250_U[0xb3] =  0x0142;  #  LATIN SMALL LETTER L WITH STROKE
    $W1250_U[0xb4] =  0x00b4;  #  ACUTE ACCENT
    $W1250_U[0xb5] =  0x00b5;  #  MICRO SIGN
    $W1250_U[0xb6] =  0x00b6;  #  PILCROW SIGN
    $W1250_U[0xb7] =  0x00b7;  #  MIDDLE DOT
    $W1250_U[0xb8] =  0x00b8;  #  CEDILLA
    $W1250_U[0xb9] =  0x0105;  #  LATIN SMALL LETTER A WITH OGONEK
    $W1250_U[0xba] =  0x015f;  #  LATIN SMALL LETTER S WITH CEDILLA
    $W1250_U[0xbb] =  0x00bb;  #  RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK
    $W1250_U[0xbc] =  0x013d;  #  LATIN CAPITAL LETTER L WITH CARON
    $W1250_U[0xbd] =  0x02dd;  #  DOUBLE ACUTE ACCENT
    $W1250_U[0xbe] =  0x013e;  #  LATIN SMALL LETTER L WITH CARON
    $W1250_U[0xbf] =  0x017c;  #  LATIN SMALL LETTER Z WITH DOT ABOVE
    $W1250_U[0xc0] =  0x0154;  #  LATIN CAPITAL LETTER R WITH ACUTE
    $W1250_U[0xc1] =  0x00c1;  #  LATIN CAPITAL LETTER A WITH ACUTE
    $W1250_U[0xc2] =  0x00c2;  #  LATIN CAPITAL LETTER A WITH CIRCUMFLEX
    $W1250_U[0xc3] =  0x0102;  #  LATIN CAPITAL LETTER A WITH BREVE
    $W1250_U[0xc4] =  0x00c4;  #  LATIN CAPITAL LETTER A WITH DIAERESIS
    $W1250_U[0xc5] =  0x0139;  #  LATIN CAPITAL LETTER L WITH ACUTE
    $W1250_U[0xc6] =  0x0106;  #  LATIN CAPITAL LETTER C WITH ACUTE
    $W1250_U[0xc7] =  0x00c7;  #  LATIN CAPITAL LETTER C WITH CEDILLA
    $W1250_U[0xc8] =  0x010c;  #  LATIN CAPITAL LETTER C WITH CARON
    $W1250_U[0xc9] =  0x00c9;  #  LATIN CAPITAL LETTER E WITH ACUTE
    $W1250_U[0xca] =  0x0118;  #  LATIN CAPITAL LETTER E WITH OGONEK
    $W1250_U[0xcb] =  0x00cb;  #  LATIN CAPITAL LETTER E WITH DIAERESIS
    $W1250_U[0xcc] =  0x011a;  #  LATIN CAPITAL LETTER E WITH CARON
    $W1250_U[0xcd] =  0x00cd;  #  LATIN CAPITAL LETTER I WITH ACUTE
    $W1250_U[0xce] =  0x00ce;  #  LATIN CAPITAL LETTER I WITH CIRCUMFLEX
    $W1250_U[0xcf] =  0x010e;  #  LATIN CAPITAL LETTER D WITH CARON
    $W1250_U[0xd0] =  0x0110;  #  LATIN CAPITAL LETTER D WITH STROKE
    $W1250_U[0xd1] =  0x0143;  #  LATIN CAPITAL LETTER N WITH ACUTE
    $W1250_U[0xd2] =  0x0147;  #  LATIN CAPITAL LETTER N WITH CARON
    $W1250_U[0xd3] =  0x00d3;  #  LATIN CAPITAL LETTER O WITH ACUTE
    $W1250_U[0xd4] =  0x00d4;  #  LATIN CAPITAL LETTER O WITH CIRCUMFLEX
    $W1250_U[0xd5] =  0x0150;  #  LATIN CAPITAL LETTER O WITH DOUBLE ACUTE
    $W1250_U[0xd6] =  0x00d6;  #  LATIN CAPITAL LETTER O WITH DIAERESIS
    $W1250_U[0xd7] =  0x00d7;  #  MULTIPLICATION SIGN
    $W1250_U[0xd8] =  0x0158;  #  LATIN CAPITAL LETTER R WITH CARON
    $W1250_U[0xd9] =  0x016e;  #  LATIN CAPITAL LETTER U WITH RING ABOVE
    $W1250_U[0xda] =  0x00da;  #  LATIN CAPITAL LETTER U WITH ACUTE
    $W1250_U[0xdb] =  0x0170;  #  LATIN CAPITAL LETTER U WITH DOUBLE ACUTE
    $W1250_U[0xdc] =  0x00dc;  #  LATIN CAPITAL LETTER U WITH DIAERESIS
    $W1250_U[0xdd] =  0x00dd;  #  LATIN CAPITAL LETTER Y WITH ACUTE
    $W1250_U[0xde] =  0x0162;  #  LATIN CAPITAL LETTER T WITH CEDILLA
    $W1250_U[0xdf] =  0x00df;  #  LATIN SMALL LETTER SHARP S
    $W1250_U[0xe0] =  0x0155;  #  LATIN SMALL LETTER R WITH ACUTE
    $W1250_U[0xe1] =  0x00e1;  #  LATIN SMALL LETTER A WITH ACUTE
    $W1250_U[0xe2] =  0x00e2;  #  LATIN SMALL LETTER A WITH CIRCUMFLEX
    $W1250_U[0xe3] =  0x0103;  #  LATIN SMALL LETTER A WITH BREVE
    $W1250_U[0xe4] =  0x00e4;  #  LATIN SMALL LETTER A WITH DIAERESIS
    $W1250_U[0xe5] =  0x013a;  #  LATIN SMALL LETTER L WITH ACUTE
    $W1250_U[0xe6] =  0x0107;  #  LATIN SMALL LETTER C WITH ACUTE
    $W1250_U[0xe7] =  0x00e7;  #  LATIN SMALL LETTER C WITH CEDILLA
    $W1250_U[0xe8] =  0x010d;  #  LATIN SMALL LETTER C WITH CARON
    $W1250_U[0xe9] =  0x00e9;  #  LATIN SMALL LETTER E WITH ACUTE
    $W1250_U[0xea] =  0x0119;  #  LATIN SMALL LETTER E WITH OGONEK
    $W1250_U[0xeb] =  0x00eb;  #  LATIN SMALL LETTER E WITH DIAERESIS
    $W1250_U[0xec] =  0x011b;  #  LATIN SMALL LETTER E WITH CARON
    $W1250_U[0xed] =  0x00ed;  #  LATIN SMALL LETTER I WITH ACUTE
    $W1250_U[0xee] =  0x00ee;  #  LATIN SMALL LETTER I WITH CIRCUMFLEX
    $W1250_U[0xef] =  0x010f;  #  LATIN SMALL LETTER D WITH CARON
    $W1250_U[0xf0] =  0x0111;  #  LATIN SMALL LETTER D WITH STROKE
    $W1250_U[0xf1] =  0x0144;  #  LATIN SMALL LETTER N WITH ACUTE
    $W1250_U[0xf2] =  0x0148;  #  LATIN SMALL LETTER N WITH CARON
    $W1250_U[0xf3] =  0x00f3;  #  LATIN SMALL LETTER O WITH ACUTE
    $W1250_U[0xf4] =  0x00f4;  #  LATIN SMALL LETTER O WITH CIRCUMFLEX
    $W1250_U[0xf5] =  0x0151;  #  LATIN SMALL LETTER O WITH DOUBLE ACUTE
    $W1250_U[0xf6] =  0x00f6;  #  LATIN SMALL LETTER O WITH DIAERESIS
    $W1250_U[0xf7] =  0x00f7;  #  DIVISION SIGN
    $W1250_U[0xf8] =  0x0159;  #  LATIN SMALL LETTER R WITH CARON
    $W1250_U[0xf9] =  0x016f;  #  LATIN SMALL LETTER U WITH RING ABOVE
    $W1250_U[0xfa] =  0x00fa;  #  LATIN SMALL LETTER U WITH ACUTE
    $W1250_U[0xfb] =  0x0171;  #  LATIN SMALL LETTER U WITH DOUBLE ACUTE
    $W1250_U[0xfc] =  0x00fc;  #  LATIN SMALL LETTER U WITH DIAERESIS
    $W1250_U[0xfd] =  0x00fd;  #  LATIN SMALL LETTER Y WITH ACUTE
    $W1250_U[0xfe] =  0x0163;  #  LATIN SMALL LETTER T WITH CEDILLA
    $W1250_U[0xff] =  0x02d9;  #  DOT ABOVE
    
    $I88592_U[0x00] =  0x0000;  #  NULL
    $I88592_U[0x01] =  0x0001;  #  START OF HEADING
    $I88592_U[0x02] =  0x0002;  #  START OF TEXT
    $I88592_U[0x03] =  0x0003;  #  END OF TEXT
    $I88592_U[0x04] =  0x0004;  #  END OF TRANSMISSION
    $I88592_U[0x05] =  0x0005;  #  ENQUIRY
    $I88592_U[0x06] =  0x0006;  #  ACKNOWLEDGE
    $I88592_U[0x07] =  0x0007;  #  BELL
    $I88592_U[0x08] =  0x0008;  #  BACKSPACE
    $I88592_U[0x09] =  0x0009;  #  HORIZONTAL TABULATION
    $I88592_U[0x0a] =  0x000a;  #  LINE FEED
    $I88592_U[0x0b] =  0x000b;  #  VERTICAL TABULATION
    $I88592_U[0x0c] =  0x000c;  #  FORM FEED
    $I88592_U[0x0d] =  0x000d;  #  CARRIAGE RETURN
    $I88592_U[0x0e] =  0x000e;  #  SHIFT OUT
    $I88592_U[0x0f] =  0x000f;  #  SHIFT IN
    $I88592_U[0x10] =  0x0010;  #  DATA LINK ESCAPE
    $I88592_U[0x11] =  0x0011;  #  DEVICE CONTROL ONE
    $I88592_U[0x12] =  0x0012;  #  DEVICE CONTROL TWO
    $I88592_U[0x13] =  0x0013;  #  DEVICE CONTROL THREE
    $I88592_U[0x14] =  0x0014;  #  DEVICE CONTROL FOUR
    $I88592_U[0x15] =  0x0015;  #  NEGATIVE ACKNOWLEDGE
    $I88592_U[0x16] =  0x0016;  #  SYNCHRONOUS IDLE
    $I88592_U[0x17] =  0x0017;  #  END OF TRANSMISSION BLOCK
    $I88592_U[0x18] =  0x0018;  #  CANCEL
    $I88592_U[0x19] =  0x0019;  #  END OF MEDIUM
    $I88592_U[0x1a] =  0x001a;  #  SUBSTITUTE
    $I88592_U[0x1b] =  0x001b;  #  ESCAPE
    $I88592_U[0x1c] =  0x001c;  #  FILE SEPARATOR
    $I88592_U[0x1d] =  0x001d;  #  GROUP SEPARATOR
    $I88592_U[0x1e] =  0x001e;  #  RECORD SEPARATOR
    $I88592_U[0x1f] =  0x001f;  #  UNIT SEPARATOR
    $I88592_U[0x20] =  0x0020;  #  SPACE
    $I88592_U[0x21] =  0x0021;  #  EXCLAMATION MARK
    $I88592_U[0x22] =  0x0022;  #  QUOTATION MARK
    $I88592_U[0x23] =  0x0023;  #  NUMBER SIGN
    $I88592_U[0x24] =  0x0024;  #  DOLLAR SIGN
    $I88592_U[0x25] =  0x0025;  #  PERCENT SIGN
    $I88592_U[0x26] =  0x0026;  #  AMPERSAND
    $I88592_U[0x27] =  0x0027;  #  APOSTROPHE
    $I88592_U[0x28] =  0x0028;  #  LEFT PARENTHESIS
    $I88592_U[0x29] =  0x0029;  #  RIGHT PARENTHESIS
    $I88592_U[0x2A] =  0x002A;  #  ASTERISK
    $I88592_U[0x2B] =  0x002B;  #  PLUS SIGN
    $I88592_U[0x2C] =  0x002C;  #  COMMA
    $I88592_U[0x2D] =  0x002D;  #  HYPHEN-MINUS
    $I88592_U[0x2E] =  0x002E;  #  FULL STOP
    $I88592_U[0x2F] =  0x002F;  #  SOLIDUS
    $I88592_U[0x30] =  0x0030;  #  DIGIT ZERO
    $I88592_U[0x31] =  0x0031;  #  DIGIT ONE
    $I88592_U[0x32] =  0x0032;  #  DIGIT TWO
    $I88592_U[0x33] =  0x0033;  #  DIGIT THREE
    $I88592_U[0x34] =  0x0034;  #  DIGIT FOUR
    $I88592_U[0x35] =  0x0035;  #  DIGIT FIVE
    $I88592_U[0x36] =  0x0036;  #  DIGIT SIX
    $I88592_U[0x37] =  0x0037;  #  DIGIT SEVEN
    $I88592_U[0x38] =  0x0038;  #  DIGIT EIGHT
    $I88592_U[0x39] =  0x0039;  #  DIGIT NINE
    $I88592_U[0x3A] =  0x003A;  #  COLON
    $I88592_U[0x3B] =  0x003B;  #  SEMICOLON
    $I88592_U[0x3C] =  0x003C;  #  LESS-THAN SIGN
    $I88592_U[0x3D] =  0x003D;  #  EQUALS SIGN
    $I88592_U[0x3E] =  0x003E;  #  GREATER-THAN SIGN
    $I88592_U[0x3F] =  0x003F;  #  QUESTION MARK
    $I88592_U[0x40] =  0x0040;  #  COMMERCIAL AT
    $I88592_U[0x41] =  0x0041;  #  LATIN CAPITAL LETTER A
    $I88592_U[0x42] =  0x0042;  #  LATIN CAPITAL LETTER B
    $I88592_U[0x43] =  0x0043;  #  LATIN CAPITAL LETTER C
    $I88592_U[0x44] =  0x0044;  #  LATIN CAPITAL LETTER D
    $I88592_U[0x45] =  0x0045;  #  LATIN CAPITAL LETTER E
    $I88592_U[0x46] =  0x0046;  #  LATIN CAPITAL LETTER F
    $I88592_U[0x47] =  0x0047;  #  LATIN CAPITAL LETTER G
    $I88592_U[0x48] =  0x0048;  #  LATIN CAPITAL LETTER H
    $I88592_U[0x49] =  0x0049;  #  LATIN CAPITAL LETTER I
    $I88592_U[0x4A] =  0x004A;  #  LATIN CAPITAL LETTER J
    $I88592_U[0x4B] =  0x004B;  #  LATIN CAPITAL LETTER K
    $I88592_U[0x4C] =  0x004C;  #  LATIN CAPITAL LETTER L
    $I88592_U[0x4D] =  0x004D;  #  LATIN CAPITAL LETTER M
    $I88592_U[0x4E] =  0x004E;  #  LATIN CAPITAL LETTER N
    $I88592_U[0x4F] =  0x004F;  #  LATIN CAPITAL LETTER O
    $I88592_U[0x50] =  0x0050;  #  LATIN CAPITAL LETTER P
    $I88592_U[0x51] =  0x0051;  #  LATIN CAPITAL LETTER Q
    $I88592_U[0x52] =  0x0052;  #  LATIN CAPITAL LETTER R
    $I88592_U[0x53] =  0x0053;  #  LATIN CAPITAL LETTER S
    $I88592_U[0x54] =  0x0054;  #  LATIN CAPITAL LETTER T
    $I88592_U[0x55] =  0x0055;  #  LATIN CAPITAL LETTER U
    $I88592_U[0x56] =  0x0056;  #  LATIN CAPITAL LETTER V
    $I88592_U[0x57] =  0x0057;  #  LATIN CAPITAL LETTER W
    $I88592_U[0x58] =  0x0058;  #  LATIN CAPITAL LETTER X
    $I88592_U[0x59] =  0x0059;  #  LATIN CAPITAL LETTER Y
    $I88592_U[0x5A] =  0x005A;  #  LATIN CAPITAL LETTER Z
    $I88592_U[0x5B] =  0x005B;  #  LEFT SQUARE BRACKET
    $I88592_U[0x5C] =  0x005C;  #  REVERSE SOLIDUS
    $I88592_U[0x5D] =  0x005D;  #  RIGHT SQUARE BRACKET
    $I88592_U[0x5E] =  0x005E;  #  CIRCUMFLEX ACCENT
    $I88592_U[0x5F] =  0x005F;  #  LOW LINE
    $I88592_U[0x60] =  0x0060;  #  GRAVE ACCENT
    $I88592_U[0x61] =  0x0061;  #  LATIN SMALL LETTER A
    $I88592_U[0x62] =  0x0062;  #  LATIN SMALL LETTER B
    $I88592_U[0x63] =  0x0063;  #  LATIN SMALL LETTER C
    $I88592_U[0x64] =  0x0064;  #  LATIN SMALL LETTER D
    $I88592_U[0x65] =  0x0065;  #  LATIN SMALL LETTER E
    $I88592_U[0x66] =  0x0066;  #  LATIN SMALL LETTER F
    $I88592_U[0x67] =  0x0067;  #  LATIN SMALL LETTER G
    $I88592_U[0x68] =  0x0068;  #  LATIN SMALL LETTER H
    $I88592_U[0x69] =  0x0069;  #  LATIN SMALL LETTER I
    $I88592_U[0x6A] =  0x006A;  #  LATIN SMALL LETTER J
    $I88592_U[0x6B] =  0x006B;  #  LATIN SMALL LETTER K
    $I88592_U[0x6C] =  0x006C;  #  LATIN SMALL LETTER L
    $I88592_U[0x6D] =  0x006D;  #  LATIN SMALL LETTER M
    $I88592_U[0x6E] =  0x006E;  #  LATIN SMALL LETTER N
    $I88592_U[0x6F] =  0x006F;  #  LATIN SMALL LETTER O
    $I88592_U[0x70] =  0x0070;  #  LATIN SMALL LETTER P
    $I88592_U[0x71] =  0x0071;  #  LATIN SMALL LETTER Q
    $I88592_U[0x72] =  0x0072;  #  LATIN SMALL LETTER R
    $I88592_U[0x73] =  0x0073;  #  LATIN SMALL LETTER S
    $I88592_U[0x74] =  0x0074;  #  LATIN SMALL LETTER T
    $I88592_U[0x75] =  0x0075;  #  LATIN SMALL LETTER U
    $I88592_U[0x76] =  0x0076;  #  LATIN SMALL LETTER V
    $I88592_U[0x77] =  0x0077;  #  LATIN SMALL LETTER W
    $I88592_U[0x78] =  0x0078;  #  LATIN SMALL LETTER X
    $I88592_U[0x79] =  0x0079;  #  LATIN SMALL LETTER Y
    $I88592_U[0x7A] =  0x007A;  #  LATIN SMALL LETTER Z
    $I88592_U[0x7B] =  0x007B;  #  LEFT CURLY BRACKET
    $I88592_U[0x7C] =  0x007C;  #  VERTICAL LINE
    $I88592_U[0x7D] =  0x007D;  #  RIGHT CURLY BRACKET
    $I88592_U[0x7E] =  0x007E;  #  TILDE
    $I88592_U[0x7F] =  0x007F;  #  DELETE (DEL)
    $I88592_U[0x80] =   0x0080;  #  PADDING CHARACTER (PAD)
    $I88592_U[0x81] =   0x0081;  #  HIGH OCTET PRESET (HOP)
    $I88592_U[0x82] =   0x0082;  #  BREAK PERMITTED HERE (BPH)
    $I88592_U[0x83] =   0x0083;  #  NO BREAK HERE (NBH)
    $I88592_U[0x84] =   0x0084;  #  INDEX (IND)
    $I88592_U[0x85] =   0x0085;  #  NEXT LINE (NEL)
    $I88592_U[0x86] =   0x0086;  #  START OF SELECTED AREA (SSA)
    $I88592_U[0x87] =   0x0087;  #  END OF SELECTED AREA (ESA)
    $I88592_U[0x88] =   0x0088;  #  CHARACTER TABULATION SET (HTS)
    $I88592_U[0x89] =   0x0089;  #  CHARACTER TABULATION WITH JUSTIFICATION (HTJ)
    $I88592_U[0x8A] =   0x008A;  #  LINE TABULATION SET (VTS)
    $I88592_U[0x8B] =   0x008B;  #  PARTIAL LINE FORWARD (PLD)
    $I88592_U[0x8C] =   0x008C;  #  PARTIAL LINE BACKWARD (PLU)
    $I88592_U[0x8D] =   0x008D;  #  REVERSE LINE FEED (RI)
    $I88592_U[0x8E] =   0x008E;  #  SINGLE-SHIFT TWO (SS2)
    $I88592_U[0x8F] =   0x008F;  #  SINGLE-SHIFT THREE (SS3)
    $I88592_U[0x90] =   0x0090;  #  DEVICE CONTROL STRING (DCS)
    $I88592_U[0x91] =   0x0091;  #  PRIVATE USE ONE (PU1)
    $I88592_U[0x92] =   0x0092;  #  PRIVATE USE TWO (PU2)
    $I88592_U[0x93] =   0x0093;  #  SET TRANSMIT STATE (STS)
    $I88592_U[0x94] =   0x0094;  #  CANCEL CHARACTER (CCH)
    $I88592_U[0x95] =   0x0095;  #  MESSAGE WAITING (MW)
    $I88592_U[0x96] =   0x0096;  #  START OF GUARDED AREA (SPA)
    $I88592_U[0x97] =   0x0097;  #  END OF GUARDED AREA (EPA)
    $I88592_U[0x98] =   0x0098;  #  START OF STRING (SOS)
    $I88592_U[0x99] =   0x0099;  #  SINGLE GRAPHIC CHARACTER INTRODUCER (SGCI)
    $I88592_U[0x9A] =   0x009A;  #  SINGLE CHARACTER INTRODUCER (SCI)
    $I88592_U[0x9B] =   0x009B;  #  CONTROL SEQUENCE INTRODUCER (CSI)
    $I88592_U[0x9C] =   0x009C;  #  STRING TERMINATOR (ST)
    $I88592_U[0x9D] =   0x009D;  #  OPERATING SYSTEM COMMAND (OSC)
    $I88592_U[0x9E] =   0x009E;  #  PRIVACY MESSAGE (PM)
    $I88592_U[0x9F] =   0x009F;  #  APPLICATION PROGRAM COMMAND (APC)
    $I88592_U[0xA0] =  0x00A0;  #  NO-BREAK SPACE
    $I88592_U[0xA1] =  0x0104;  #  LATIN CAPITAL LETTER A WITH OGONEK
    $I88592_U[0xA2] =  0x02D8;  #  BREVE
    $I88592_U[0xA3] =  0x0141;  #  LATIN CAPITAL LETTER L WITH STROKE
    $I88592_U[0xA4] =  0x00A4;  #  CURRENCY SIGN
    $I88592_U[0xA5] =  0x013D;  #  LATIN CAPITAL LETTER L WITH CARON
    $I88592_U[0xA6] =  0x015A;  #  LATIN CAPITAL LETTER S WITH ACUTE
    $I88592_U[0xA7] =  0x00A7;  #  SECTION SIGN
    $I88592_U[0xA8] =  0x00A8;  #  DIAERESIS
    $I88592_U[0xA9] =  0x0160;  #  LATIN CAPITAL LETTER S WITH CARON
    $I88592_U[0xAA] =  0x015E;  #  LATIN CAPITAL LETTER S WITH CEDILLA
    $I88592_U[0xAB] =  0x0164;  #  LATIN CAPITAL LETTER T WITH CARON
    $I88592_U[0xAC] =  0x0179;  #  LATIN CAPITAL LETTER Z WITH ACUTE
    $I88592_U[0xAD] =  0x00AD;  #  SOFT HYPHEN
    $I88592_U[0xAE] =  0x017D;  #  LATIN CAPITAL LETTER Z WITH CARON
    $I88592_U[0xAF] =  0x017B;  #  LATIN CAPITAL LETTER Z WITH DOT ABOVE
    $I88592_U[0xB0] =  0x00B0;  #  DEGREE SIGN
    $I88592_U[0xB1] =  0x0105;  #  LATIN SMALL LETTER A WITH OGONEK
    $I88592_U[0xB2] =  0x02DB;  #  OGONEK
    $I88592_U[0xB3] =  0x0142;  #  LATIN SMALL LETTER L WITH STROKE
    $I88592_U[0xB4] =  0x00B4;  #  ACUTE ACCENT
    $I88592_U[0xB5] =  0x013E;  #  LATIN SMALL LETTER L WITH CARON
    $I88592_U[0xB6] =  0x015B;  #  LATIN SMALL LETTER S WITH ACUTE
    $I88592_U[0xB7] =  0x02C7;  #  CARON
    $I88592_U[0xB8] =  0x00B8;  #  CEDILLA
    $I88592_U[0xB9] =  0x0161;  #  LATIN SMALL LETTER S WITH CARON
    $I88592_U[0xBA] =  0x015F;  #  LATIN SMALL LETTER S WITH CEDILLA
    $I88592_U[0xBB] =  0x0165;  #  LATIN SMALL LETTER T WITH CARON
    $I88592_U[0xBC] =  0x017A;  #  LATIN SMALL LETTER Z WITH ACUTE
    $I88592_U[0xBD] =  0x02DD;  #  DOUBLE ACUTE ACCENT
    $I88592_U[0xBE] =  0x017E;  #  LATIN SMALL LETTER Z WITH CARON
    $I88592_U[0xBF] =  0x017C;  #  LATIN SMALL LETTER Z WITH DOT ABOVE
    $I88592_U[0xC0] =  0x0154;  #  LATIN CAPITAL LETTER R WITH ACUTE
    $I88592_U[0xC1] =  0x00C1;  #  LATIN CAPITAL LETTER A WITH ACUTE
    $I88592_U[0xC2] =  0x00C2;  #  LATIN CAPITAL LETTER A WITH CIRCUMFLEX
    $I88592_U[0xC3] =  0x0102;  #  LATIN CAPITAL LETTER A WITH BREVE
    $I88592_U[0xC4] =  0x00C4;  #  LATIN CAPITAL LETTER A WITH DIAERESIS
    $I88592_U[0xC5] =  0x0139;  #  LATIN CAPITAL LETTER L WITH ACUTE
    $I88592_U[0xC6] =  0x0106;  #  LATIN CAPITAL LETTER C WITH ACUTE
    $I88592_U[0xC7] =  0x00C7;  #  LATIN CAPITAL LETTER C WITH CEDILLA
    $I88592_U[0xC8] =  0x010C;  #  LATIN CAPITAL LETTER C WITH CARON
    $I88592_U[0xC9] =  0x00C9;  #  LATIN CAPITAL LETTER E WITH ACUTE
    $I88592_U[0xCA] =  0x0118;  #  LATIN CAPITAL LETTER E WITH OGONEK
    $I88592_U[0xCB] =  0x00CB;  #  LATIN CAPITAL LETTER E WITH DIAERESIS
    $I88592_U[0xCC] =  0x011A;  #  LATIN CAPITAL LETTER E WITH CARON
    $I88592_U[0xCD] =  0x00CD;  #  LATIN CAPITAL LETTER I WITH ACUTE
    $I88592_U[0xCE] =  0x00CE;  #  LATIN CAPITAL LETTER I WITH CIRCUMFLEX
    $I88592_U[0xCF] =  0x010E;  #  LATIN CAPITAL LETTER D WITH CARON
    $I88592_U[0xD0] =  0x0110;  #  LATIN CAPITAL LETTER D WITH STROKE
    $I88592_U[0xD1] =  0x0143;  #  LATIN CAPITAL LETTER N WITH ACUTE
    $I88592_U[0xD2] =  0x0147;  #  LATIN CAPITAL LETTER N WITH CARON
    $I88592_U[0xD3] =  0x00D3;  #  LATIN CAPITAL LETTER O WITH ACUTE
    $I88592_U[0xD4] =  0x00D4;  #  LATIN CAPITAL LETTER O WITH CIRCUMFLEX
    $I88592_U[0xD5] =  0x0150;  #  LATIN CAPITAL LETTER O WITH DOUBLE ACUTE
    $I88592_U[0xD6] =  0x00D6;  #  LATIN CAPITAL LETTER O WITH DIAERESIS
    $I88592_U[0xD7] =  0x00D7;  #  MULTIPLICATION SIGN
    $I88592_U[0xD8] =  0x0158;  #  LATIN CAPITAL LETTER R WITH CARON
    $I88592_U[0xD9] =  0x016E;  #  LATIN CAPITAL LETTER U WITH RING ABOVE
    $I88592_U[0xDA] =  0x00DA;  #  LATIN CAPITAL LETTER U WITH ACUTE
    $I88592_U[0xDB] =  0x0170;  #  LATIN CAPITAL LETTER U WITH DOUBLE ACUTE
    $I88592_U[0xDC] =  0x00DC;  #  LATIN CAPITAL LETTER U WITH DIAERESIS
    $I88592_U[0xDD] =  0x00DD;  #  LATIN CAPITAL LETTER Y WITH ACUTE
    $I88592_U[0xDE] =  0x0162;  #  LATIN CAPITAL LETTER T WITH CEDILLA
    $I88592_U[0xDF] =  0x00DF;  #  LATIN SMALL LETTER SHARP S
    $I88592_U[0xE0] =  0x0155;  #  LATIN SMALL LETTER R WITH ACUTE
    $I88592_U[0xE1] =  0x00E1;  #  LATIN SMALL LETTER A WITH ACUTE
    $I88592_U[0xE2] =  0x00E2;  #  LATIN SMALL LETTER A WITH CIRCUMFLEX
    $I88592_U[0xE3] =  0x0103;  #  LATIN SMALL LETTER A WITH BREVE
    $I88592_U[0xE4] =  0x00E4;  #  LATIN SMALL LETTER A WITH DIAERESIS
    $I88592_U[0xE5] =  0x013A;  #  LATIN SMALL LETTER L WITH ACUTE
    $I88592_U[0xE6] =  0x0107;  #  LATIN SMALL LETTER C WITH ACUTE
    $I88592_U[0xE7] =  0x00E7;  #  LATIN SMALL LETTER C WITH CEDILLA
    $I88592_U[0xE8] =  0x010D;  #  LATIN SMALL LETTER C WITH CARON
    $I88592_U[0xE9] =  0x00E9;  #  LATIN SMALL LETTER E WITH ACUTE
    $I88592_U[0xEA] =  0x0119;  #  LATIN SMALL LETTER E WITH OGONEK
    $I88592_U[0xEB] =  0x00EB;  #  LATIN SMALL LETTER E WITH DIAERESIS
    $I88592_U[0xEC] =  0x011B;  #  LATIN SMALL LETTER E WITH CARON
    $I88592_U[0xED] =  0x00ED;  #  LATIN SMALL LETTER I WITH ACUTE
    $I88592_U[0xEE] =  0x00EE;  #  LATIN SMALL LETTER I WITH CIRCUMFLEX
    $I88592_U[0xEF] =  0x010F;  #  LATIN SMALL LETTER D WITH CARON
    $I88592_U[0xF0] =  0x0111;  #  LATIN SMALL LETTER D WITH STROKE
    $I88592_U[0xF1] =  0x0144;  #  LATIN SMALL LETTER N WITH ACUTE
    $I88592_U[0xF2] =  0x0148;  #  LATIN SMALL LETTER N WITH CARON
    $I88592_U[0xF3] =  0x00F3;  #  LATIN SMALL LETTER O WITH ACUTE
    $I88592_U[0xF4] =  0x00F4;  #  LATIN SMALL LETTER O WITH CIRCUMFLEX
    $I88592_U[0xF5] =  0x0151;  #  LATIN SMALL LETTER O WITH DOUBLE ACUTE
    $I88592_U[0xF6] =  0x00F6;  #  LATIN SMALL LETTER O WITH DIAERESIS
    $I88592_U[0xF7] =  0x00F7;  #  DIVISION SIGN
    $I88592_U[0xF8] =  0x0159;  #  LATIN SMALL LETTER R WITH CARON
    $I88592_U[0xF9] =  0x016F;  #  LATIN SMALL LETTER U WITH RING ABOVE
    $I88592_U[0xFA] =  0x00FA;  #  LATIN SMALL LETTER U WITH ACUTE
    $I88592_U[0xFB] =  0x0171;  #  LATIN SMALL LETTER U WITH DOUBLE ACUTE
    $I88592_U[0xFC] =  0x00FC;  #  LATIN SMALL LETTER U WITH DIAERESIS
    $I88592_U[0xFD] =  0x00FD;  #  LATIN SMALL LETTER Y WITH ACUTE
    $I88592_U[0xFE] =  0x0163;  #  LATIN SMALL LETTER T WITH CEDILLA
    $I88592_U[0xFF] =  0x02D9;  #  DOT ABOVE
    
    
    $this->W1250_U = $W1250_U;
    $this->I88592_U = $I88592_U;
  
  } //conv_tab_set
  
}//class convertor



?>