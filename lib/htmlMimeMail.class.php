<?php
/**
 * This file is part of the htmlMimeMail package (http://www.phpguru.org/)
 *
 * htmlMimeMail is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * htmlMimeMail is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with htmlMimeMail; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * (c) Copyright 2004 Richard Heyes
 */


class htmlMimeMail
{
   /**
    * The html part of the message
    * @var string
    */
   var $html;

   /**
    * The text part of the message(only used in TEXT only messages)
    * @var string
    */
   var $text;

   /**
    * The main body of the message after building
    * @var string
    */
   var $output;

   /**
    * The alternative text to the HTML part (only used in HTML messages)
    * @var string
    */
   var $html_text;

   /**
    * An array of embedded images/objects
    * @var array
    */
   var $html_images;

   /**
    * An array of recognised image types for the findHtmlImages() method
    * @var array
    */
   var $image_types;

   /**
    * Parameters that affect the build process
    * @var array
    */
   var $build_params;

   /**
    * Array of attachments
    * @var array
    */
   var $attachments;

   /**
    * The main message headers
    * @var array
    */
   var $headers;

   /**
    * Whether the message has been built or not
    * @var boolean
    */
   var $is_built;

   /**
    * The return path address. If not set the From:
    * address is used instead
    * @var string
    */
   var $return_path;

   /**
    * Array of information needed for smtp sending
    * @var array
    */
   var $smtp_params;

   /**
    * Constructor function. Sets the headers
    * if supplied.
    */
   function __construct()
   {
      /**
       * Initialise some variables.
       */
      $this->html_images = array();
      $this->headers     = array();
      $this->is_built    = false;

      /**
       * If you want the auto load functionality
       * to find other image/file types, add the
       * extension and content type here.
       */
      $this->image_types = array(
                                  'gif'       => 'image/gif',
                                  'jpg'       => 'image/jpeg',
                                  'jpeg'      => 'image/jpeg',
                                  'jpe'       => 'image/jpeg',
                                  'bmp'       => 'image/bmp',
                                  'png'       => 'image/png',
                                  'tif'       => 'image/tiff',
                                  'tiff'      => 'image/tiff',
                                  'swf'       => 'application/x-shockwave-flash'
                                );

      /**
       * Set these up
       */
      $this->build_params['html_encoding'] = 'quoted-printable';
      $this->build_params['text_encoding'] = '7bit';
      $this->build_params['html_charset']  = 'ISO-8859-1';
      $this->build_params['text_charset']  = 'ISO-8859-1';
      $this->build_params['head_charset']  = 'ISO-8859-1';
      $this->build_params['text_wrap']     = 998;

      /**
       * Defaults for smtp sending
       */
      if (!empty($GLOBALS['HTTP_SERVER_VARS']['HTTP_HOST'])) {
         $helo = $GLOBALS['HTTP_SERVER_VARS']['HTTP_HOST'];
      } elseif (!empty($GLOBALS['HTTP_SERVER_VARS']['SERVER_NAME'])) {
         $helo = $GLOBALS['HTTP_SERVER_VARS']['SERVER_NAME'];
      } else {
         $helo = 'localhost';
      }

      $this->smtp_params['host'] = 'localhost';
      $this->smtp_params['port'] = 25;
      $this->smtp_params['helo'] = $helo;
      $this->smtp_params['auth'] = false;
      $this->smtp_params['user'] = '';
      $this->smtp_params['pass'] = '';

      /**
       * Make sure the MIME version header is first.
       */
      $this->headers['MIME-Version'] = '1.0';
   }

   /**
    * This function will read a file in from a supplied filename and return it.
    * This can then be given as the first argument of the the functions add_html_image() or add_attachment().
    */
   function getFile($filename)
   {
      $return = '';
      if ($fp = fopen($filename, 'rb'))
      {
         while (!feof($fp))
         {
            $return .= fread($fp, 1024);
         }

         fclose($fp);
         return $return;
      }
      else
      {
         return false;
      }
   }

   /**
    * Accessor to set the CRLF style
    */
   function setCrlf($crlf = "\n")
   {
      if (!defined('CRLF'))
         define('CRLF', $crlf, true);

      if (!defined('MAIL_MIMEPART_CRLF'))
         define('MAIL_MIMEPART_CRLF', $crlf, true);
   }

   /**
    * Accessor to set the SMTP parameters
    */
   function setSMTPParams($host = null, $port = null, $helo = null, $auth = null, $user = null, $pass = null)
   {
      if (!is_null($host)) $this->smtp_params['host'] = $host;
      if (!is_null($port)) $this->smtp_params['port'] = $port;
      if (!is_null($helo)) $this->smtp_params['helo'] = $helo;
      if (!is_null($auth)) $this->smtp_params['auth'] = $auth;
      if (!is_null($user)) $this->smtp_params['user'] = $user;
      if (!is_null($pass)) $this->smtp_params['pass'] = $pass;
   }

   /**
    * Accessor function to set the text encoding
    */
   function setTextEncoding($encoding = '7bit')
   {
      $this->build_params['text_encoding'] = $encoding;
   }

   /**
    * Accessor function to set the HTML encoding
    */
   function setHtmlEncoding($encoding = 'quoted-printable')
   {
      $this->build_params['html_encoding'] = $encoding;
   }

   /**
    * Accessor function to set the text charset
    */
   function setTextCharset($charset = 'ISO-8859-1')
   {
      $this->build_params['text_charset'] = $charset;
   }

   /**
    * Accessor function to set the HTML charset
    */
   function setHtmlCharset($charset = 'ISO-8859-1')
   {
      $this->build_params['html_charset'] = $charset;
   }

   /**
    * Accessor function to set the header encoding charset
    */
   function setHeadCharset($charset = 'ISO-8859-1')
   {
      $this->build_params['head_charset'] = $charset;
   }

   /**
    * Accessor function to set the text wrap count
    */
   function setTextWrap($count = 998)
   {
      $this->build_params['text_wrap'] = $count;
   }

   /**
    * Accessor to set a header
    */
   function setHeader($name, $value)
   {
      $this->headers[$name] = $value;
   }

   /**
    * Accessor to add a Subject: header
    */
   function setSubject($subject)
   {
      $this->headers['Subject'] = $subject;
   }

   /**
    * Accessor to add a From: header
    */
   function setFrom($from)
   {
      $this->headers['From'] = $from;
   }

   /**
    * Accessor to set the return path
    */
   function setReturnPath($return_path)
   {
      $this->return_path = $return_path;
   }

   /**
    * Accessor to add a Cc: header
    */
   function setCc($cc)
   {
      $this->headers['Cc'] = $cc;
   }

   /**
    * Accessor to add a Bcc: header
    */
   function setBcc($bcc)
   {
      $this->headers['Bcc'] = $bcc;
   }

   /**
    * Adds plain text. Use this function
    * when NOT sending html email
    */
   function setText($text = '')
   {
      $this->text = $text;
   }

   /**
    * Adds a html part to the mail.
    * Also replaces image names with content-id's.
    */
   function setHtml($html, $text = null, $images_dir = null)
   {
      $this->html      = $html;
      $this->html_text = $text;

      if (isset($images_dir)) {
         $this->_findHtmlImages($images_dir);
      }
   }

   /**
    * Function for extracting images from html source.
    * This function will look through the html code supplied by add_html() and find any file that ends in one of the extensions defined in $obj->image_types.
    * If the file exists it will read it in and embed it, (not an attachment).
    *
    * @author Dan Allen
    */
   function _findHtmlImages($images_dir)
   {
      $extensions = array();
      // Build the list of image extensions
      while (list($key,) = each($this->image_types)) {
         $extensions[] = $key;
      }

      preg_match_all('/(?:"|\')([^"\']+\.('.implode('|', $extensions).'))(?:"|\')/Ui', $this->html, $images);

      for ($i=0; $i<count($images[1]); $i++)
      {
         if (file_exists($images_dir . $images[1][$i]))
         {
            $html_images[] = $images[1][$i];
            $this->html = str_replace($images[1][$i], basename($images[1][$i]), $this->html);
         }
      }

      if (!empty($html_images))
      {
         // If duplicate images are embedded, they may show up as attachments, so remove them.
         $html_images = array_unique($html_images);
         sort($html_images);

         for ($i=0; $i<count($html_images); $i++)
         {
            if ($image = $this->getFile($images_dir.$html_images[$i]))
            {
               $ext = substr($html_images[$i], strrpos($html_images[$i], '.') + 1);
               $content_type = $this->image_types[strtolower($ext)];
               $this->addHtmlImage($image, basename($html_images[$i]), $content_type);
            }
         }
      }
   }

   /**
    * Adds an image to the list of embedded images.
    */
   function addHtmlImage($file, $name = '', $c_type='application/octet-stream')
   {
      $this->html_images[] = array('body'   => $file,
                                   'name'   => $name,
                                   'c_type' => $c_type,
                                   'cid'    => md5(uniqid(time()))
                                  );
   }


   /**
    * Adds a file to the list of attachments.
    */
   function addAttachment($file, $name = '', $c_type='application/octet-stream', $encoding = 'base64')
   {
      $this->attachments[] = array('body'      => $file,
                                   'name'      => $name,
                                   'c_type'    => $c_type,
                                   'encoding'  => $encoding
                                  );
   }

   /**
    * Adds a text subpart to a mime_part object
    */
   function &_addTextPart(&$obj, $text)
   {
      $params['content_type'] = 'text/plain';
      $params['encoding']     = $this->build_params['text_encoding'];
      $params['charset']      = $this->build_params['text_charset'];

      if (is_object($obj))
         $return = $obj->addSubpart($text, $params);
      else
         $return = new Mail_mimePart($text, $params);

      return $return;
   }

   /**
    * Adds a html subpart to a mime_part object
    */
   function &_addHtmlPart(&$obj)
   {
      $params['content_type'] = 'text/html';
      $params['encoding']     = $this->build_params['html_encoding'];
      $params['charset']      = $this->build_params['html_charset'];

      if (is_object($obj))
         $return = $obj->addSubpart($this->html, $params);
      else
         $return = new Mail_mimePart($this->html, $params);

      return $return;
   }

   /**
    * Starts a message with a mixed part
    */
   function &_addMixedPart()
   {
      $params['content_type'] = 'multipart/mixed';
      $return = new Mail_mimePart('', $params);

      return $return;
   }

   /**
    * Adds an alternative part to a mime_part object
    */
   function &_addAlternativePart(&$obj)
   {
      $params['content_type'] = 'multipart/alternative';

      if (is_object($obj))
         $return = $obj->addSubpart('', $params);
      else
         $return = new Mail_mimePart('', $params);

      return $return;
   }

   /**
    * Adds a html subpart to a mime_part object
    */
   function &_addRelatedPart(&$obj)
   {
      $params['content_type'] = 'multipart/related';

      if (is_object($obj))
         $return = $obj->addSubpart('', $params);
      else
         $return = new Mail_mimePart('', $params);

      return $return;
   }

   /**
    * Adds an html image subpart to a mime_part object
    */
   function _addHtmlImagePart(&$obj, $value)
   {
      $params['content_type'] = $value['c_type'];
      $params['encoding']     = 'base64';
      $params['disposition']  = 'inline';
      $params['dfilename']    = $value['name'];
      $params['cid']          = $value['cid'];

      $obj->addSubpart($value['body'], $params);
   }

   /**
    * Adds an attachment subpart to a mime_part object
    */
   function _addAttachmentPart(&$obj, $value)
   {
      $params['content_type'] = $value['c_type'];
      $params['encoding']     = $value['encoding'];
      $params['disposition']  = 'attachment';
      $params['dfilename']    = $value['name'];

      $obj->addSubpart($value['body'], $params);
   }

   /**
    * Builds the multipart message from the list ($this->_parts). $params is an array of parameters that shape the building of the message.
    *
    * Currently supported are:
    *
    * $params['html_encoding'] - The type of encoding to use on html. Valid options are "7bit", "quoted-printable" or "base64" (all without quotes).
    *                            7bit is EXPRESSLY NOT RECOMMENDED. Default is quoted-printable
    * $params['text_encoding'] - The type of encoding to use on plain text Valid options are "7bit", "quoted-printable" or "base64" (all without quotes).
    *                            Default is 7bit
    * $params['text_wrap']     - The character count at which to wrap 7bit encoded data.
    *                            Default this is 998.
    * $params['html_charset']  - The character set to use for a html section.
    *                            Default is ISO-8859-1
    * $params['text_charset']  - The character set to use for a text section.
    *                          - Default is ISO-8859-1
    * $params['head_charset']  - The character set to use for header encoding should it be needed.
    *                          - Default is ISO-8859-1
    */
   function buildMessage($params = array())
   {
      if (!empty($params)) {
         while (list($key, $value) = each($params)) {
            $this->build_params[$key] = $value;
         }
      }

      if (!empty($this->html_images)) {
         foreach ($this->html_images as $value) {
            $this->html = str_replace($value['name'], 'cid:'.$value['cid'], $this->html);
         }
      }

      $null        = null;
      $attachments = !empty($this->attachments) ? true : false;
      $html_images = !empty($this->html_images) ? true : false;
      $html        = !empty($this->html)        ? true : false;
      $text        = isset($this->text)         ? true : false;

      switch (true)
      {
         case $text AND !$attachments:
            $message = &$this->_addTextPart($null, $this->text);
            break;

         case !$text AND $attachments AND !$html:
            $message = &$this->_addMixedPart();

            for ($i=0; $i<count($this->attachments); $i++)
               $this->_addAttachmentPart($message, $this->attachments[$i]);

            break;

         case $text AND $attachments:
            $message = &$this->_addMixedPart();
            $this->_addTextPart($message, $this->text);
            $attachmentsCount = count($this->attachments);

            for ($i = 0; $i < $attachmentsCount; $i++) {
               $this->_addAttachmentPart($message, $this->attachments[$i]);
            }
            break;

         case $html AND !$attachments AND !$html_images:
            if (!is_null($this->html_text))
            {
               $message = &$this->_addAlternativePart($null);
               $this->_addTextPart($message, $this->html_text);
               $this->_addHtmlPart($message);
            }
            else
            {
               $message = &$this->_addHtmlPart($null);
            }
            break;

         case $html AND !$attachments AND $html_images:
            if (!is_null($this->html_text))
            {
               $message = &$this->_addAlternativePart($null);
               $this->_addTextPart($message, $this->html_text);
               $related = &$this->_addRelatedPart($message);
            }
            else
            {
               $message = &$this->_addRelatedPart($null);
               $related = &$message;
            }

            $this->_addHtmlPart($related);
            $imagesCount = count($this->html_images);

            for ($i = 0; $i < $imagesCount; $i++)
               $this->_addHtmlImagePart($related, $this->html_images[$i]);

            break;

         case $html AND $attachments AND !$html_images:
            $message = &$this->_addMixedPart();
            if (!is_null($this->html_text))
            {
               $alt = &$this->_addAlternativePart($message);
               $this->_addTextPart($alt, $this->html_text);
               $this->_addHtmlPart($alt);
            }
            else
            {
               $this->_addHtmlPart($message);
            }

            $attachmentsCount = count($this->attachments);

            for ($i = 0; $i < $attachmentsCount; $i++)
               $this->_addAttachmentPart($message, $this->attachments[$i]);

            break;

         case $html AND $attachments AND $html_images:
            $message = &$this->_addMixedPart();
            if (!is_null($this->html_text))
            {
               $alt = &$this->_addAlternativePart($message);
               $this->_addTextPart($alt, $this->html_text);
               $rel = &$this->_addRelatedPart($alt);
            }
            else
            {
               $rel = &$this->_addRelatedPart($message);
            }

            $this->_addHtmlPart($rel);
            $imagesCount = count($this->html_images);
            $attachmentsCount = count($this->attachments);

            for ($i = 0; $i < $imagesCount; $i++)
               $this->_addHtmlImagePart($rel, $this->html_images[$i]);

            for ($i = 0; $i < $attachmentsCount; $i++)
               $this->_addAttachmentPart($message, $this->attachments[$i]);

            break;
      }

      if (isset($message))
      {
         $output = $message->encode();
         $this->output   = $output['body'];
         $this->headers  = array_merge($this->headers, $output['headers']);

         // Add message ID header
         srand((double)microtime() * 10000000);
         $host_or_name = (isset($GLOBALS["SERVER_NAME"]) && $GLOBALS['HTTP_SERVER_VARS']['SERVER_NAME']) ? $GLOBALS['HTTP_SERVER_VARS']['SERVER_NAME'] : "";
         $host_or_name = (isset($GLOBALS["HTTP_SERVER_VARS"]) && $GLOBALS['HTTP_SERVER_VARS']['HTTP_HOST'] && !empty($GLOBALS['HTTP_SERVER_VARS']['HTTP_HOST'])) ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_HOST'] : $host_or_name;
         $message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), $host_or_name);
         $this->headers['Message-ID'] = $message_id;

         $this->is_built = true;

         return true;
      }
      else
      {
         return false;
      }
   }

   /**
    * Function to encode a header if necessary according to RFC2047
    */
   function _encodeHeader($input, $charset = 'ISO-8859-1')
   {
      /*
      preg_match_all('/(\s?\w*[\x80-\xFF]+\w*\s?)/', $input, $matches);

      foreach ($matches[1] as $value)
      {
         $replacement = preg_replace('/([\x20\x80-\xFF])/e', '"=" . strtoupper(dechex(ord("\1")))', $value);
         $input = str_replace($value, '=?' . $charset . '?Q?' . $replacement . '?=', $input);
      }
      */
      return $input;
   }

   /**
    * Sends the mail.
    *
    * @param  array  $recipients
    * @param  string $type OPTIONAL
    * @return mixed
    */
   function send($recipients, $type = 'mail')
   {
      if (!defined('CRLF'))
         $this->setCrlf($type == 'mail' ? "\n" : "\r\n");

      if (!$this->is_built)
         $this->buildMessage();

      switch ($type)
      {
         case 'mail':
            $subject = '';

            if (!empty($this->headers['Subject']))
            {
               $subject = $this->_encodeHeader($this->headers['Subject'], $this->build_params['head_charset']);
               unset($this->headers['Subject']);
            }

            // Get flat representation of headers
            $headers = [];
            foreach ($this->headers as $name => $value)
               $headers[] = $name . ': ' . $this->_encodeHeader($value, $this->build_params['head_charset']);

            $to = $this->_encodeHeader(implode(', ', $recipients), $this->build_params['head_charset']);

            if (!empty($this->return_path))
            {
               $result = @mail($to, $subject, $this->output, implode(CRLF, $headers), '-f' . $this->return_path);
            }
            else
            {
               $result = @mail($to, $subject, $this->output, implode(CRLF, $headers));
            }

            // Reset the subject in case mail is resent
            if ($subject !== '')
               $this->headers['Subject'] = $subject;

            // Return
            return $result;
            break;

         case 'smtp':
            require_once(dirname(__FILE__) . '/smtp.php');
            require_once(dirname(__FILE__) . '/RFC822.php');
            $smtp = &smtp::connect($this->smtp_params);

            // Parse recipients argument for internet addresses
            foreach ($recipients as $recipient)
            {
               $addresses = Mail_RFC822::parseAddressList($recipient, $this->smtp_params['helo'], null, false);

               foreach ($addresses as $address) {
                  $smtp_recipients[] = sprintf('%s@%s', $address->mailbox, $address->host);
               }
            }
            unset($addresses); // These are reused
            unset($address);   // These are reused

            // Get flat representation of headers, parsing
            // Cc and Bcc as we go
            foreach ($this->headers as $name => $value)
            {
               if ($name == 'Cc' OR $name == 'Bcc')
               {
                  $addresses = Mail_RFC822::parseAddressList($value, $this->smtp_params['helo'], null, false);
                  foreach ($addresses as $address) {
                     $smtp_recipients[] = sprintf('%s@%s', $address->mailbox, $address->host);
                  }
               }
               if ($name == 'Bcc') {
                  continue;
               }
               $headers[] = $name . ': ' . $this->_encodeHeader($value, $this->build_params['head_charset']);
            }
            // Add To header based on $recipients argument
            $headers[] = 'To: ' . $this->_encodeHeader(implode(', ', $recipients), $this->build_params['head_charset']);

            // Add headers to send_params
            $send_params['headers']    = $headers;
            $send_params['recipients'] = array_values(array_unique($smtp_recipients));
            $send_params['body']       = $this->output;

            // Setup return path
            if (isset($this->return_path)) {
               $send_params['from'] = $this->return_path;
            } elseif (!empty($this->headers['From'])) {
               $from = Mail_RFC822::parseAddressList($this->headers['From']);
               $send_params['from'] = sprintf('%s@%s', $from[0]->mailbox, $from[0]->host);
            } else {
               $send_params['from'] = 'postmaster@' . $this->smtp_params['helo'];
            }

            // Send it
            if (!$smtp->send($send_params))
            {
               $this->errors = $smtp->errors;
               return false;
            }

            return true;
            break;
         default:
            return false;
            break;
      }
   }

   /**
    * Use this method to return the email in message/rfc822 format.
    * Useful for adding an email to another email as an attachment. there's a commented out example in example.php.
    */
   function getRFC822($recipients)
   {
      // Make up the date header as according to RFC822
      $this->setHeader('Date', date('D, d M y H:i:s O'));

      if (!defined('CRLF')) {
         $this->setCrlf($type == 'mail' ? "\n" : "\r\n");
      }

      if (!$this->is_built) {
         $this->buildMessage();
      }

      $headers[] = array();
      // Return path ?
      if (isset($this->return_path)) {
         $headers[] = 'Return-Path: ' . $this->return_path;
      }


      // Get flat representation of headers
      foreach ($this->headers as $name => $value) {
         $headers[] = $name . ': ' . $value;
      }
      $headers[] = 'To: ' . implode(', ', $recipients);

      return implode(CRLF, $headers) . CRLF . CRLF . $this->output;
   }
} // End of class.

//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Richard Heyes <richard@phpguru.org>                         |
// +----------------------------------------------------------------------+

/**
 *
 *  Raw mime encoding class
 *
 * What is it?
 *   This class enables you to manipulate and build
 *   a mime email from the ground up.
 *
 * Why use this instead of mime.php?
 *   mime.php is a userfriendly api to this class for
 *   people who aren't interested in the internals of
 *   mime mail. This class however allows full control
 *   over the email.
 *
 * Eg.
 *
 * // Since multipart/mixed has no real body, (the body is
 * // the subpart), we set the body argument to blank.
 *
 * $params['content_type'] = 'multipart/mixed';
 * $email = new Mail_mimePart('', $params);
 *
 * // Here we add a text part to the multipart we have
 * // already. Assume $body contains plain text.
 *
 * $params['content_type'] = 'text/plain';
 * $params['encoding']     = '7bit';
 * $text = $email->addSubPart($body, $params);
 *
 * // Now add an attachment. Assume $attach is
 * the contents of the attachment
 *
 * $params['content_type'] = 'application/zip';
 * $params['encoding']     = 'base64';
 * $params['disposition']  = 'attachment';
 * $params['dfilename']    = 'example.zip';
 * $attach =& $email->addSubPart($body, $params);
 *
 * // Now build the email. Note that the encode
 * // function returns an associative array containing two
 * // elements, body and headers. You will need to add extra
 * // headers, (eg. Mime-Version) before sending.
 *
 * $email = $message->encode();
 * $email['headers'][] = 'Mime-Version: 1.0';
 *
 *
 * Further examples are available at http://www.phpguru.org
 *
 * TODO:
 *  - Set encode() to return the $obj->encoded if encode()
 *    has already been run. Unless a flag is passed to specifically
 *    re-build the message.
 *
 * @author  Richard Heyes <richard@phpguru.org>
 * @version $Revision: 1.3 $
 * @package Mail
 */

class Mail_mimePart {

   /**
    * The encoding type of this part
    * @var string
    */
   var $_encoding;

   /**
    * An array of subparts
    * @var array
    */
   var $_subparts;

   /**
    * The output of this part after being built
    * @var string
    */
   var $_encoded;

   /**
    * Headers for this part
    * @var array
    */
   var $_headers;

   /**
    * The body of this part (not encoded)
    * @var string
    */
   var $_body;

   /**
    * Constructor.
    *
    * Sets up the object.
    *
    * @param string $body   - The body of the mime part if any.
    * @param array  $params - An associative array of parameters:
    *                  content_type - The content type for this part eg multipart/mixed
    *                  encoding     - The encoding to use, 7bit, 8bit, base64, or quoted-printable
    *                  cid          - Content ID to apply
    *                  disposition  - Content disposition, inline or attachment
    *                  dfilename    - Optional filename parameter for content disposition
    *                  description  - Content description
    *                  charset      - Character set to use
    * @access public
    */
   function __construct($body = '', $params = array())
   {
      if (!defined('MAIL_MIMEPART_CRLF')) {
         define('MAIL_MIMEPART_CRLF', defined('MAIL_MIME_CRLF') ? MAIL_MIME_CRLF : "\r\n", TRUE);
      }

      foreach ($params as $key => $value) {
         switch ($key) {
            case 'content_type':
               $headers['Content-Type'] = $value . (isset($charset) ? '; charset="' . $charset . '"' : '');
               break;

            case 'encoding':
               $this->_encoding = $value;
               $headers['Content-Transfer-Encoding'] = $value;
               break;

            case 'cid':
               $headers['Content-ID'] = '<' . $value . '>';
               break;

            case 'disposition':
               $headers['Content-Disposition'] = $value . (isset($dfilename) ? '; filename="' . $dfilename . '"' : '');
               break;

            case 'dfilename':
               if (isset($headers['Content-Disposition'])) {
                  $headers['Content-Disposition'] .= '; filename="' . $value . '"';
               } else {
                  $dfilename = $value;
               }
               break;

            case 'description':
               $headers['Content-Description'] = $value;
               break;

            case 'charset':
               if (isset($headers['Content-Type'])) {
                  $headers['Content-Type'] .= '; charset="' . $value . '"';
               } else {
                  $charset = $value;
               }
               break;
         }
      }

      // Default content-type
      if (!isset($headers['Content-Type']))
         $headers['Content-Type'] = 'text/plain';

      //Default encoding
      if (!isset($this->_encoding))
         $this->_encoding = '7bit';

      // Assign stuff to member variables
      $this->_encoded  = array();
      $this->_headers  = $headers;
      $this->_body     = $body;
   }

   /**
    * Encodes and returns the email. Also stores it in the encoded member variable
    *
    * @return string An associative array containing two elements, body and headers. The headers element is itself an indexed array.
    * @access public
    */
   function encode()
   {
      $encoded =& $this->_encoded;

      if (!empty($this->_subparts))
      {
         srand((double)microtime() * 1000000);
         $boundary = '=_' . md5(uniqid(rand()) . microtime());
         $this->_headers['Content-Type'] .= ';' . MAIL_MIMEPART_CRLF . "\t" . 'boundary="' . $boundary . '"';

         // Add body parts to $subparts
         for ($i = 0; $i < count($this->_subparts); $i++) {
            $headers = array();
            $tmp = $this->_subparts[$i]->encode();
            foreach ($tmp['headers'] as $key => $value) {
               $headers[] = $key . ': ' . $value;
            }
            $subparts[] = implode(MAIL_MIMEPART_CRLF, $headers) . MAIL_MIMEPART_CRLF . MAIL_MIMEPART_CRLF . $tmp['body'];
         }

         $encoded['body'] = '--' . $boundary . MAIL_MIMEPART_CRLF .
                            implode('--' . $boundary . MAIL_MIMEPART_CRLF, $subparts) .
                            '--' . $boundary.'--' . MAIL_MIMEPART_CRLF;
      }
      else
      {
         $encoded['body'] = $this->_getEncodedData($this->_body, $this->_encoding) . MAIL_MIMEPART_CRLF;
      }

      // Add headers to $encoded
      $encoded['headers'] =& $this->_headers;

      return $encoded;
   }

   /**
    * &addSubPart()
    *
    * Adds a subpart to current mime part and returns
    * a reference to it
    *
    * @param string $body   The body of the subpart, if any.
    * @param array  $params The parameters for the subpart, same as the $params argument for constructor.
    * @return array A reference to the part you just added. It is crucial if using multipart/* in your subparts that you use =& in your script when calling this function,
    *               otherwise you will not be able to add further subparts.
    * @access public
    */
   function &addSubPart($body, $params)
   {
      $this->_subparts[] = new Mail_mimePart($body, $params);
      return $this->_subparts[count($this->_subparts) - 1];
   }

   /**
    * _getEncodedData()
    * Returns encoded data based upon encoding passed to it
    *
    * @param string $data     The data to encode.
    * @param string $encoding The encoding type to use, 7bit, base64, or quoted-printable.
    * @access private
    */
   function _getEncodedData($data, $encoding)
   {
      switch ($encoding) {
         case '8bit':
         case '7bit':
            return $data;
            break;

         case 'quoted-printable':
            return $this->_quotedPrintableEncode($data);
            break;

         case 'base64':
            return rtrim(chunk_split(base64_encode($data), 76, MAIL_MIMEPART_CRLF));
            break;

         default:
            return $data;
      }
   }

   /**
    * Encodes data to quoted-printable standard.
    * @param string $input     The data to encode
    * @param int     $line_max Optional max line length. Should  not be more than 76 chars
    * @return string
    */
   function _quotedPrintableEncode($input , $line_max = 76)
   {
      $lines  = preg_split("/\r?\n/", $input);
      $eol    = MAIL_MIMEPART_CRLF;
      $escape = '=';
      $output = '';

      while(list(, $line) = each($lines))
      {
         $linlen  = strlen($line);
         $newline = '';

         for ($i = 0; $i < $linlen; $i++)
         {
            $char = substr($line, $i, 1);
            $dec  = ord($char);

            if (($dec == 32) AND ($i == ($linlen - 1))){    // convert space at eol only
               $char = '=20';
            }
            elseif($dec == 9)
            {
               ; // Do nothing if a tab.
            }
            elseif(($dec == 61) OR ($dec < 32 ) OR ($dec > 126))
            {
               $char = $escape . strtoupper(sprintf('%02s', dechex($dec)));
            }

            if ((strlen($newline) + strlen($char)) >= $line_max)          // MAIL_MIMEPART_CRLF is not counted
            {
               $output  .= $newline . $escape . $eol;                    // soft line break; " =\r\n" is okay
               $newline  = '';
            }
            $newline .= $char;
         } // end of for

         $output .= $newline . $eol;
      }

      $output = substr($output, 0, -1 * strlen($eol)); // Don't want last crlf
      return $output;
   }
} // End of class
