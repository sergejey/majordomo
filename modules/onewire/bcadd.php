<?php
  // $Id: bcadd.php,v 1.1 2006/12/29 11:26:46 d1mag Exp $
  // $Author: d1mag $

  // File: bcadd
  //
  //This function is meant to be a drop-in replacement for those
  //people who don't have bcmath functionality in their PHP
  //distribution. It probably has problems, and the best solution
  //is to build yourself a version with bcmath support. Barring
  //this, you can use this hack. (Jeff)
  //
  // A regex for exponential numbers is
  //[-+]?([0-9]*\.)?[0-9]+([eE][-+]?[0-9]+)?

  // Function: bcadd
  //
  //Provides bcadd functionality (from bcmath extension) without
  //having to load bcmath PHP extension.
  //
  // Parameters:
  //
  //$left - First number to add.
  //
  //$right - Second number to add.
  //
  //$scale - Number of digits of precision desired after the
  //decimal point. 0 will produce an integer.
  //
  // Returns:
  //
  //left and right added together with a precision of scale.
  //
function bcadd ($left, $right, $scale) {
  // Deal with numbers smaller than $scale
  $_left = ($left < pow(10, -$scale)) ? 0 : $left;
  $_right = ($right < pow(10, -$scale)) ? 0 : $right;

  // first add the two numbers
  $sum = (double)($_left + $_right);

  // check for a dot in the number
  if (strpos($sum, ".") === false) {
    // not found, integer
    $int_part = $sum;
    $real_part = 0;
  } else {
    // if not, we split
    list ($int_part, $real_part) = explode (".", $sum);
  } // end checking for a dot

  // handle scale of 0
  if ($scale == 0) return $int_part;

  // handle real parts that need more precision
  if ($scale > strlen($real_part)) {
    for ($i=0;$i<=($scale - strlen($real_part));$i++)
      $real_part .= "0";
  } // end checking for more precision needed

  // return built string
  return $int_part . "." . substr($real_part, 0, $scale);
} // end function bcadd

?>
