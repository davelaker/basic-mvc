<?php

function cleanForOutput($string) {

  $string = strip_tags($string);
  $string = nl2br($string);
  $string = htmlspecialchars($string);

  return $string;
}

function pr($var, $msg = false) {
  if (Config::read('debug') > 0) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    if ($msg != false) {
      die($msg);
    }
  }
}

function _pr($var, $msg = true) {
  pr($var, $msg);
}

function vd($var, $msg = false) {
  if (Config::read('debug') > 0) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    if ($msg != false) {
      die($msg);
    }
  }
}

function _vd($var, $msg = false) {
  vd($var, $msg);
}

function convertToUTF8($content) {

  $enc = mb_detect_encoding($content);
  return mb_convert_encoding($content, "UTF-8", $enc);
}

if (!function_exists('getMicrotime')) {

  /**
   * Returns microtime for execution time checking
   *
   * @return float Microtime
   */
  function getMicrotime() {
    return microtime(true);
  }
}

function truncate($text, $length = 100, $options = array()) {
  $default = array(
    'ending' => '...', 'exact' => true, 'html' => false
  );
  $options = array_merge($default, $options);
  extract($options);

  if ($html) {
    if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
      return $text;
    }
    $totalLength = mb_strlen(strip_tags($ending));
    $openTags    = array();
    $truncate    = '';

    preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
    foreach ($tags as $tag) {
      if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
        if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
          array_unshift($openTags, $tag[2]);
        } else {
          if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
            $pos = array_search($closeTag[1], $openTags);
            if ($pos !== false) {
              array_splice($openTags, $pos, 1);
            }
          }
        }
      }
      $truncate .= $tag[1];

      $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
      if ($contentLength + $totalLength > $length) {
        $left           = $length - $totalLength;
        $entitiesLength = 0;
        if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
          foreach ($entities[0] as $entity) {
            if ($entity[1] + 1 - $entitiesLength <= $left) {
              $left--;
              $entitiesLength += mb_strlen($entity[0]);
            } else {
              break;
            }
          }
        }

        $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
        break;
      } else {
        $truncate .= $tag[3];
        $totalLength += $contentLength;
      }
      if ($totalLength >= $length) {
        break;
      }
    }
  } else {
    if (mb_strlen($text) <= $length) {
      return $text;
    } else {
      $truncate = mb_substr($text, 0, $length - mb_strlen($ending));
    }
  }
  if (!$exact) {
    $spacepos = mb_strrpos($truncate, ' ');
    if (isset($spacepos)) {
      if ($html) {
        $bits = mb_substr($truncate, $spacepos);
        preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
        if (!empty($droppedTags)) {
          foreach ($droppedTags as $closingTag) {
            if (!in_array($closingTag[1], $openTags)) {
              array_unshift($openTags, $closingTag[1]);
            }
          }
        }
      }
      $truncate = mb_substr($truncate, 0, $spacepos);
    }
  }
  $truncate .= $ending;

  if ($html) {
    foreach ($openTags as $tag) {
      $truncate .= '</' . $tag . '>';
    }
  }

  return $truncate;
}

function idx($var, $index, $default = '') {

  $val = null;
  if (is_object($var)) {
    if (isset($var->$index)) {
      $val = $var->$index;
    }
  }
  if (is_array($var)) {
    if (isset($var[$index])) {
      $val = $var[$index];
    }
  }

  if ($val === null) {
    $val = $default;
  }

  return $val;
}
