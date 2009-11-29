<?php
/**
 * ./bblog/inc/taglines.php
 *
 * @package default
 */


// taglines.php - random taglines
$taglines = array();
$taglines[] = "In need of a tagline since 2003";
$taglines[] = ":)";
$taglines[] = "Coded from scratch to be cruft free";
$taglines[] = "Smarty based blog software";
$taglines[] = "<sub>Trackbackable comments, threaded commentable trackbacks</sub> : A mouthfull of features";
$taglines[] = "Version ".BBLOG_VERSION;
$taglines[] = "The revolution will be blogged";
$taglines[] = "bBlog has more mmm";
$taglines[] = "Forged in Middle-Earth";
$taglines[] = "So fresh and so clean";
$taglines[] = "Simple, Powerful, Modable, Extenable";
$taglines[] = "The new black";
$taglines[] = "This software never has bugs, it just develops random features";
$taglines[] = "As a computer, I find your faith in technology amusing";
$taglines[] = "Why fork when you can spoon?";
$taglines[] = "Bootylicious blogging";
$taglines[] = "Please support bBlog by <a style='color:#ffffff; font-weight:bolder;' href='http://www.bblog.com/donate.php' target='_blank'>donating</a>";
$tl_n = array_rand($taglines);
$bBlog->assign('tagline', $taglines[$tl_n]);
?>
