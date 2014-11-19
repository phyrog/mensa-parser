<?php

  require_once 'includes/phpquery/phpQuery/phpQuery.php';


  /**
   * Squishes a string by replacing all whitespace (including linebreaks) by one space
  **/
  function squish_string($str) {
    return preg_replace('/\s+/', ' ', trim($str));
  }

  /**
   * Joins the texts of the given objects with a delimiter
  **/
  function join_by($objs, $delim) {

    $res = "";

    foreach($objs as $o) {
      $res = $res . pq($o)->text() . $delim;
    }
    $res = substr($res, 0, strlen($res) - strlen($delim));

    return $res;
  }
  
  function parse_mensa($uri) {
    phpQuery::newDocument(file_get_contents($uri));

    $date = strtotime(pq(".tab-date:first")->text());

    $categories = phpQuery::map(pq(".food-plan:first .food-category"), function($category) {
      // Find name of the category
      $name = squish_string(pq($category)->find("thead .category-name")->text());

      // Find description of dishes
      $descr = pq($category)->find("tbody .field-name-field-description");

      // Remove annotation numbers
      $descr->children()->remove("sup");

      // Join, if multiple dishes are available in the same category
      $description = squish_string(join_by($descr, ", "));

      return array(array("name" => $name, "meal" => $description));
    });

    return array("dishes" => $categories, "date" => $date);
  }

?>
