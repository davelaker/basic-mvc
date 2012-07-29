<?php
function create_idx(&$array, $idx) {
  if (!isset($array[$idx])) {
    $array[$idx] = array();
  }
}

/**
 * Increment a $key in an array by $value,
 *
 * NOTE: the array is passed by referenced, but we return also return the value after the increment
 *
 * @param array &$array
 * @param       $key
 * @param int   $value
 *
 * @return int
 */
function incr(array &$array, $key, $value = 1) {
  if (!isset($array[$key])) {
    $array[$key] = 0;
  }
  $array[$key] += $value;
  return $array[$key];
}

/**
 * Similar to incr(), but for constructing arrays-of-arrays.
 *
 * NOTE: the array is passed by referenced, but we return also return the updated array
 *
 * @param &array $arr Array in which we're appending to a value
 * @param scalar $idx   Key of value to which we're appending
 * @param        $value Thing to append to $arr[$idx]
 *
 * @return array
 */
function append(array &$array, $key, $value) {
  if (!isset($array[$key])) {
    $array[$key] = array();
  }
  $array[$key][] = $value;
  return $array[$key];
}

/**
 * @param array $array
 *
 * @return mixed
 */
function head(array $array) {
  return reset($array);
}

/**
 * @param array $array
 *
 * @return mixed
 */
function last(array $array) {
  return end($arr);
}

/**
 * Add all values from the array $from to the array $to (igoring keys)
 *
 */
function array_extend(array &$to, array $from) {
  foreach ($from as $val) {
    $to[] = $val;
  }
}