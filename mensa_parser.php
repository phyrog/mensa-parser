<?php

  require_once 'includes/phpquery/phpQuery/phpQuery.php';


  /**
   * Squishes a string by replacing all whitespace (including linebreaks) by one space
  **/
  function squish_string($str) {
    return preg_replace('/\s+/', ' ', trim($str));
  }

  function zip_lists($list1, $list2) {
    return array_map(function($key, $val) {
      return array($key, $val);
    }, $list1, $list2);
  };

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
  
  function parse_mensa($uri, $day = null) {
    phpQuery::newDocument(file_get_contents($uri));

    $dates = phpQuery::map(pq(".tab-date"), function($date) {
      return strtotime(pq($date)->text());
    });

    $days = phpQuery::map(pq(".food-plan"), function($day) {
      return array(phpQuery::map(pq($day)->find(".food-category"), function($category) {
        // Find name of the category
        $name = squish_string(pq($category)->find("thead .category-name")->text());

        // Find description of dishes
        $descr = pq($category)->find("tbody .field-name-field-description");

        // Remove annotation numbers
        $descr->children()->remove("sup");

        // Join, if multiple dishes are available in the same category
        $description = squish_string(join_by($descr, ", "));

        return array(array("name" => $name, "meal" => $description));
      }));
    });

    $menu = array_combine($dates, $days);

    if(is_null($day)) {
      return $menu;
    }
    else {
      $_menus = zip_lists(array_keys($menu), $menu);
      $menu_on_or_after_day = array_filter($_menus, function($elem) use ($day) {
        return $elem[0] >= $day;
      });
      $f = reset($menu_on_or_after_day);
      return array("dishes" => $f[1], "date" => $f[0]);
    }
  }

?>
