<?php
function identify_function_sectionlinks () {
$help = '
<p>Sectionlinks is a Smarty function to be used in templates.
<p>Example usage
<ul><li>To create a link list of sections, one per line :<br>
   {sectionlinks}
   <li>To create a link list of sections, seperated by a # <br>
     {sectionlinks sep="#"}
     <li>To make a list with &lt;ul&gt use {sectionlinkd mode="list"}<br />
   <li>Used within a {posts} loop, to link to sections that the post is in, seperated by a commer :<br>
      {sectionlinks sep=", " sections=$post.sections}
</ul>';


  return array (
    'name'           =>'sectionlinks',
    'type'             =>'function',
    'nicename'     =>'Section Links',
    'description'   =>'Make links to sections',
    'authors'        =>'Eaden McKee <eadz@bblog.com>',
    'licence'         =>'GPL',
    'help'   => $help
  );


}

function smarty_function_sectionlinks($params, &$bBlog) {

    $linkcode = '';

    if(!isset($params['mode'])) $mode = "break";
    else $mode = $params['mode'];

    if($mode=='list') $sep = "";
    else if(!isset($params['sep'])) $sep = "<br />";
    else $sep = $params['sep'];

    if(isset($params['sections'])) $sections = $params['sections'];
    else $sections = $bBlog->sections;

    $num = count($sections);
    $i=0;
  
    if ($mode=='list') $linkcode .= "<ul>";

    foreach ($sections as $section) {
            $i++;
            // we using arrays in the template and objects in the core..
            if(isset($params['sections'])) {
                   $url = $section['url'];
                   $nicename = $section['nicename'];
            } else {
                   $url = $section->url;
                   $nicename = $section->nicename;
            }

            if($mode=='list') $linkcode .= "<li>";

            $linkcode .= '<a href="'.$url.'">'.$nicename.'</a>';

            if($mode=='list') $linkcode .= "</li>";
            else if($num > $i) $linkcode .= $sep;

    }

    if ($mode=='list') $linkcode .= "</ul>";

    return $linkcode;
}

/* vim: set expandtab: */

?>
