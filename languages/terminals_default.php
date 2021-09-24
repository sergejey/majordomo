<?php
/**
 * Default language file
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.0
 */


$dictionary = array(

    /* start array for convert number to string */
    
	'NUMBER_TO_STRING_1TEN' => array( array('','one','two','three','four','five','six','seven', 'eight','nine'), array('','one','two','three','four','five','six','seven', 'eight','nine')),
	'NUMBER_TO_STRING_2TEN' => array('ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'),
	'NUMBER_TO_STRING_TENS' => array(2=>'twenty ',' thirty ',' forty ',' fifty ',' sixty ',' seventy ',' eighty ',' ninety '),
	'NUMBER_TO_STRING_HUNDRED' => array('','one hundred','two hundred','three hundred','four hundred','five hundred','six hundred', 'seven hundred','eight hundred','nine hundred'),
	'NUMBER_TO_STRING_UNIT' => array(array(' ' ,' ' , 1), array(' ' ,' point'   ,' point' ,0),array('thousand ',' thousands', 'thousands'     ,1), array('million ',' million ',' millions' ,0), array('billion', 'billion', 'billion',0)),
	'NUMBER_TO_STRING_NULL' => 'zero',
	
    /* end array for convert number to string  */

);

foreach ($dictionary as $k => $v) {
    if (!defined('LANG_' . $k)) {
        @define('LANG_' . $k, $v);
    }
}
