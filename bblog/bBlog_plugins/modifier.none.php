<?php

 function identify_modifier_none () {
  return array (
    'name'           =>'none',
    'type'           =>'modifier',
    'nicename'       =>'None',
    'description'    =>'Does nothing at all to your text',
    'authors'         =>'Eaden McKee',
    'licence'         =>'GPL',
    'help'             =>'There is not much to say here... your post will stay exactly as you type it and will not be changed'
  );
}

function smarty_modifier_none(&$string)
{
    return $string;
}

?>
