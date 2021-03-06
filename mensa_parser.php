<?php

  require_once 'includes/phpquery/phpQuery/phpQuery.php';


  /**
   * Squishes a string by replacing all whitespace (including linebreaks) by one space
  **/
  function squish_string($str) {
    return preg_replace('/\s+/', ' ', trim($str));
  }

  function parse_date($str) {
    setlocale(LC_TIME, "de_DE.utf8");
    $date = strptime($str, "%d. %b");
    $date["tm_year"] = date("Y");
    if($date["tm_mon"]+1 < date("m"))
      $date["tm_year"]++;
    return @mktime(0, 0, 0, $date["tm_mon"]+1, $date["tm_mday"], $date["tm_year"]);
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
      $res = $res . $o . $delim;
    }
    $res = substr($res, 0, strlen($res) - strlen($delim));

    return $res;
  }

  function parse_menu($uri) {
    phpQuery::newDocument(file_get_contents($uri));

    $dates = phpQuery::map(pq(".tab-date"), function($date) {
      return parse_date(pq($date)->text());
    });

    $days = phpQuery::map(pq(".food-plan"), function($day) {
      return array(phpQuery::map(pq($day)->find(".food-category"), function($category) {
        // Find name of the category
        $name = squish_string(pq($category)->find("thead .category-name")->text());

        // Find description of dishes
        $descr = pq($category)->find("tbody .field-name-field-description");

        // Remove annotation numbers
        $descr->children()->remove("sup");

        $description = phpQuery::map($descr, function($meal) {
          return squish_string(pq($meal)->text());
        });

        return array(array("name" => $name, "meals" => $description));
      }));
    });

    return array_combine($dates, $days);
  }

  function join_dishes($menu) {
    return array_map(function($day) {
      return array_map(function($category) {
        return array("name" => $category["name"],
                     "meal" => join_by($category["meals"], ", "));
      }, $day);
    }, $menu);
  }

  function parse_mensa($uri, $day = null) {

    $menu = join_dishes(parse_menu($uri));

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
