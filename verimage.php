<?
/*
* @version 0.1 (auto-set)
*/
   

  Define('IWIDTH', 120);
  Define('IHEIGHT', 20);
  Define('TEXTLENGTH', 5);
  Define('FROMFILE', './lib/codebook.txt');
  Define('FONT', './lib/_vimage_tahoma.ttf');

  include_once("./lib/general.class.php");


  $position = (int)$_GET['n'];

  $file = fopen(FROMFILE, "r");
  $content = fread($file, filesize(FROMFILE));
  fclose($file);

  while ($position > strlen($content))
        $position -= strlen($content);

  $text = substr($content, $position, 3 * TEXTLENGTH);

  $text = ereg_replace("[^a-zA-Z0-9]+", '', $text);
  $text = substr($text, 0, TEXTLENGTH);
  $text = strtolower($text);

  header ("Content-type: image/png");
  $im = @ImageCreate (IWIDTH, IHEIGHT) or die ("Cannot Initialize new GD image stream");

  $background_color = ImageColorAllocate ($im, 255, 255, 255);
  $text_color = ImageColorAllocate ($im, 233, 14, 91);
  imagefill($im, 0, 0, $background_color);


  $xpos = rand(1,IWIDTH-50);
  $color = ImageColorAllocate ($im, rand(0,150), rand(0,170), rand(0,140));

  for ($i=0; $i<500;$i++)
 imagesetpixel($im, rand(0,IWIDTH), rand(0,IHEIGHT), ImageColorAllocate ($im,  rand(195,205), rand(195,205), rand(195,205)));

  imagestring($im, 5, $xpos, rand(0,IHEIGHT-16), $text, $color);
  // comment if not works
  /*
        for ($i = 0; $i < strlen($text); $i++)
        {
                $angle = rand(0,15);
                if (rand(0,1) == 0) $angle = -$angle;
                $size = rand(20,25);
                $color = ImageColorAllocate ($im, rand(0,200), rand(0,200), rand(0,200));
                $box = ImageTTFBBox($size, $angle, FONT, substr($text,$i,1));
                ImageTTFText ($im, $size, $angle, $xpos, 50, $color, FONT, substr($text,$i,1));
                $xpos += max($box[2],$box[4]) + rand(2,6);
        }*/
  // comment if not works
//  debmes('Image output');

  ImagePng($im);
  ImageDestroy($im);
//  print_r($text);
?>