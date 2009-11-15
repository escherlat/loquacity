<?php
// function.blogroll.php - outputs a blogroll from your favorites at blo.gs
// based on php blogroll by phil ringnalda - http://philringnalda.com/phpblogroll/

function identify_function_blogroll () {
$help = '
<p>blogroll!
<p>Based on phpblogroll by phil ringnalda - http://philringnalda.com/phpblogroll/
<p>You will need a blogroll account from <a href="http://www.blo.gs">blo.gs</a> to use this.
<p>Make sure bblog/compiled_templates/favorites.xml is writable by the webserver.
<p>Sign up at blo.gs and then add your favorites to your favorites list (you\'ll be happier
 if you use a decent browser, since Opera/Mozilla will show a cute little plus in a
 circle for things you haven\'t added, and an x in a circle for things you\'ve already added,
 while IE will show a square box if it\'s added and "&oplus" if it isn\'t.

Once you\'ve added your favorites to your list, click "settings" and check the box to make your
list of favorites public. Save your settings, go back to the home page, and click "share".
Down at the bottom of the page is a list of links to favorites in three flavors.
Right click the "favorites.xml" and select "save target as" to save a copy of
favorites.xml on your computer (you only have to save and upload it once <b>into compiled_templates</b>
to prime the pump - after that it\'s automatic).
While you are there, make a note of the number in the url,
which is your user number - you\'ll need it.

<p>Once you have uploaded your favorites.xml into bblog/compiled_templates and chmod 777\'d it you
can put {blogroll userid=1234} in your template. Your userid being you blo.gs userid.';

return array (
    'name'           =>'blogroll',
    'type'             =>'function',
    'nicename'     =>'Blogroll',
    'description'   =>'Displays a blo.gs blogroll',
    'authors'        =>'phil ringnalda',
    'licence'         =>'Free',
    'help'   => $help
  );
}


function smarty_function_blogroll($params,&$bBlog) {
    // ( todo: less/no globals! )
    global $blogroll_open_tags, $blogroll_temp, $blogroll_current_tag, $blogroll_weblog_index;
    global $blogroll_close_tags;
    global $blogroll_html_header, $blogroll_html_footer;

    if(is_numeric($params['userid'])) $userid = $params['userid'];
      else return "<p>You need to set your blo.gs user id like : {blogroll userid=1234}";
    ob_start();
?>
<script type="text/javascript">
/* called by the blogroll select list produced by blogroll.php,
   in a DOM-compliant browser creates a link and clicks it to
   set the referrer, otherwise just changes location.href */
function gothere(where) {
  if(document.createElement){
    newLink = document.createElement("A");
    if (newLink.click){
      newLink.href = where.options[where.selectedIndex].value;
      theBod = document.getElementsByTagName("BODY");
      theBod[0].appendChild(newLink);
      newLink.click();
      }
    else{
      location.href = where.options[where.selectedIndex].value;
      }
    }
  else {
    location.href = where.options[where.selectedIndex].value;
    }
}
</script>
<?php

// local filename
// we know that compiled_templates will always be writable
$blogroll_xml_file = BBLOGROOT.'cache/favorites.xml';

// remote file url
$blogroll_xml_source = 'http://blo.gs/'.$userid.'/favorites.xml';
// something that will always appear in the remote file if it returns any of your favorites
$blogroll_xml_test = 'weblogUpdates';

// fresh enough to use? give it a sniff
if ( filemtime($blogroll_xml_file) < (time()-3900) ) {     // just over an hour
  if ( $blogroll_local_fq = fopen($blogroll_xml_file,"w") ){
    $blogroll_remote_fp = fopen($blogroll_xml_source,"r");
    $blogroll_remote_data = fread($blogroll_remote_fp, 100000);
    if (stristr($blogroll_remote_data, $blogroll_xml_test)){
      if ($blogroll_remote_fp && $blogroll_local_fq){
        fwrite($blogroll_local_fq,$blogroll_remote_data);
        }
      }
    fclose($blogroll_remote_fp);
    fclose($blogroll_local_fq);
    }
  }

// forget the old filemtime
clearstatcache();

// what to write before the first link or option
$blogroll_html_header = '<form action=""><select onChange="gothere(this)" class="selectmenu">';
$blogroll_html_header .= '<option value="">' . date("n/d g:ia",filemtime($blogroll_xml_file)) . '</option>';

// what to write after the last link or option
$blogroll_html_footer = '</select></form>';


$blogroll_open_tags = array(
    'WEBLOGUPDATES' => '<WEBLOGUPDATES>',
    'WEBLOG' => '<WEBLOG>');

$blogroll_close_tags = array(
    'WEBLOGUPDATES' => '</WEBLOGUPDATES>');

// declare the character set - UTF-8 is the default
$blogroll_type = 'ISO-8859-1';

// create our parser
$blogroll_xml_parser = xml_parser_create($blogroll_type);

// set some parser options
xml_parser_set_option($blogroll_xml_parser, XML_OPTION_CASE_FOLDING, true);
xml_parser_set_option($blogroll_xml_parser, XML_OPTION_TARGET_ENCODING, $blogroll_type);

// this tells PHP what functions to call when it finds an element
// these funcitons also handle the element's attributes
xml_set_element_handler($blogroll_xml_parser, 'blogrollStartElement','blogrollEndElement');


if ($blogroll_fp = @fopen($blogroll_xml_file, 'r')) {
  // loop through the file and parse baby!
  while ($blogroll_data = fread($blogroll_fp, 4096)) {
    if (!xml_parse($blogroll_xml_parser, $blogroll_data, feof($blogroll_fp))) {
      die(sprintf( "XML error: %s at line %d\n\n",
      xml_error_string(xml_get_error_code($blogroll_xml_parser)),
      xml_get_current_line_number($blogroll_xml_parser)));
      }
    }
	}
else {

  echo"<option value=''>Temporarily 404'd</option>";
  }

xml_parser_free($blogroll_xml_parser);
$o = ob_get_contents();
ob_end_clean();
return $o;

}
function blogrollStartElement($parser, $name, $attrs=''){
    global $blogroll_open_tags, $blogroll_temp, $blogroll_current_tag, $blogroll_weblog_index;
    $blogroll_current_tag = $name;
    if ($format = $blogroll_open_tags[$name]){
    switch($name){
        case 'WEBLOGUPDATES':
            //starting to parse
            $blogroll_weblog_index = -1;
        break;
        case 'WEBLOG':
            //indivdual blog
            $blogroll_weblog_index++;
            $blogroll_temp[$blogroll_weblog_index]['name'] = htmlentities(addslashes((strlen($attrs['NAME']) > 19) ? substr($attrs['NAME'], 0, 17) . "..." : $attrs['NAME']));
            $blogroll_temp[$blogroll_weblog_index]['url'] = $attrs['URL'];
        break;    
        default:
        break;
    }
    }
}

function blogrollEndElement($parser, $name, $attrs=''){
    global $blogroll_close_tags, $blogroll_temp, $blogroll_current_tag;
    if ($format = $blogroll_close_tags[$name]){
    switch($name){
        case 'WEBLOGUPDATES':
        blogrollWriteLinks();
        break;
        default:
        break;
    }
    }
}


function blogrollWriteLinks(){
    global $blogroll_temp, $blogroll_html_header, $blogroll_html_footer;
    echo "<script type=\"text/javascript\">\n";
    echo "document.write('$blogroll_html_header');\n";
    for($i = 0; $i < sizeof($blogroll_temp); $i++){
        echo "document.write('<option value=\"" . $blogroll_temp[$i]['url'] . "\">" . $blogroll_temp[$i]['name'] . "</option>');\n";
        }
    echo "document.write('$blogroll_html_footer');\n";
    echo "</script>\n";
    echo "<noscript>\n";
    for($i = 0; $i < sizeof($blogroll_temp); $i++){
        echo "<a href=\"" . $blogroll_temp[$i]['url']."\">" . $blogroll_temp[$i]['name'] . "</a><br>\n";
        }
    echo "</noscript>\n";    
}



?> 
