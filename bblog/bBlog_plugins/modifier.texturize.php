<?php
// by photomatt : http://photomatt.net/tools/texturize
function identify_modifier_texturize () {
  return array (
    'name'           =>'texturize',
    'type'           =>'modifier',
    'nicename'       =>'Texturize',
    'description'    =>'Makes quotes curly',
    'authors'         =>'photomatt',
    'licence'         =>'GPL',
    'help'            => '<p>See <A href="http://photomatt.net/tools/texturize">http://photomatt.net/tools/texturize</a>'
  );
}

function smarty_modifier_texturize($text) {
    $textarr = preg_split("/(<.*>)/U", $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
    $stop = count($textarr); $next = true; // loop stuff
    for ($i = 0; $i < $stop; $i++) {
        $curl = $textarr[$i];
        if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Gecko')) {
            $curl = str_replace('<q>', '&#8220;', $curl);
            $curl = str_replace('</q>', '&#8221;', $curl);
        }
        if ('<' != $curl{0} && $next) { // If it's not a tag
            $curl = str_replace('---', '&#8212;', $curl);
            $curl = str_replace('--', '&#8211;', $curl);
            $curl = str_replace("...", '&#8230;', $curl);
            $curl = str_replace('``', '&#8220;', $curl);

            // This is a hack, look at this more later. It works pretty well though.
            $cockney = array("'tain't","'twere","'twas","'tis","'twill","'til","'bout","'nuff","'round", "'em");
            $cockneyreplace = array("&#8217;tain&#8217;t","&#8217;twere","&#8217;twas","&#8217;tis","&#8217;twill","&#8217;til","&#8217;bout","&#8217;nuff","&#8217;round","&#8217;em");
            $curl = str_replace($cockney, $cockneyreplace, $curl);


            $curl = preg_replace("/'s/", "&#8217;s", $curl);
            $curl = preg_replace("/'(\d\d(?:&#8217;|')?s)/", "&#8217;$1", $curl);
            $curl = preg_replace('/(\s|\A|")\'/', '$1&#8216;', $curl);
            $curl = preg_replace("/(\d+)\"/", "$1&Prime;", $curl);
            $curl = preg_replace("/(\d+)'/", "$1&prime;", $curl);
            $curl = preg_replace("/(\S)'([^'\s])/", "$1&#8217;$2", $curl);
            $curl = preg_replace('/"([\s.]|\Z)/', '&#8221;$1', $curl);
            $curl = preg_replace('/(\s|\A)"/', '$1&#8220;', $curl);
            $curl = preg_replace("/'([\s.]|\Z)/", '&#8217;$1', $curl);
            $curl = preg_replace("/\(tm\)/i", '&#8482;', $curl);
            $curl = preg_replace("/\(c\)/i", '&#169;', $curl);
            $curl = preg_replace("/\(r\)/i", '&#174;', $curl);

            $curl = str_replace("''", '&#8221;', $curl);
            $curl = preg_replace('/&([^#])(?![a-z]{2,8};)/', '&#038;$1', $curl);

            $curl = preg_replace('/(d+)x(\d+)/', "$1&#215;$2", $curl);

        } elseif (strstr($curl, '<code') || strstr($curl, '<pre') || strstr($curl, '<kbd' || strstr($curl, '<style') || strstr($curl, '<script'))) {
            // strstr is fast
            $next = false;
        } else {
            $next = true;
        }
        $output .= $curl;
    }

    return $output;

}

?>
