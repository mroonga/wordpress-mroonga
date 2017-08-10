<?php
/*
Plugin Name: Mroonga full text search engine
Plugin URI:
Description: Full text search
Version: 0.1
Author: Yasuhiro Horimoto
Author URI: https://www.clear-code.com/
License: GPL2
*/
/*
*/

class MroongaSearch
{
  public static function activate()
  {
    self::mrn_create_table();
  }
  public static function deactivate()
  {
    self::mrn_drop_table(); 
  }

  public static function insert_data($post_ID, $post)
  {
    $dbname = "wddb";
    $tablename = "mrn_blogs";

    $connection = mysql_connect('localhost', 'root', 'P@ssw0rd');
    mysql_select_db($dbname, $connection);

    $sql = "INSERT INTO `{$tablename}` ("
    ."`post_author`, `post_date`, `post_date_gmt`, "
    ."`post_content`, `post_content_filtered`, `post_title`, "
    ."`post_excerpt`, `post_status`, `post_type`, "
    ."`comment_status`, `ping_status`, `post_password`, "
    ."`post_name`, `to_ping`, `pinged`, `post_modified`, "
    ."`post_modified_gmt`, `post_parent`, `menu_order`, "
    ."`post_mime_type`, `guid`"
    .")"
    ."VALUES "
    ."("
    ."{$post->post_author}, '{$post->post_date}', '{$post->post_date_gmt}', "
    ."'{$post->post_content}', '{$post->post_content_filtered}', '{$post->post_title}', "
    ."'{$post->post_excerpt}', '{$post->post_status}', '{$post->post_type}', "
    ."'{$post->comment_status}', '{$post->ping_status}', '{$post->post_password}', "
    ."'{$post->post_name}', '{$post->to_ping}', '{$post->pinged}', '{$post->post_modified}', "
    ."'{$post->post_modified_gmt}', {$post->post_parent}, {$post->menu_order}, "
    ."'{$post->post_mime_type}', '{$post->guid}'"
    .");";
    $res = mysql_query($sql)or die(mysql_error());

    mysql_close($connection);
  }

  private static function mrn_create_table()
  {
    $dbname = "wddb";
    $tablename = "mrn_blogs";

    $connection = mysql_connect('localhost', 'root', 'P@ssw0rd');
    mysql_select_db($dbname, $connection);

    $sql = "CREATE TABLE `{$tablename}` ("
    ."`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,"
    ."`post_author` bigint(20) unsigned DEFAULT '0',"
    ."`post_date` datetime DEFAULT '0000-00-00 00:00:00',"
    ."`post_date_gmt` datetime DEFAULT '0000-00-00 00:00:00',"
    ."`post_content` longtext COLLATE utf8mb4_unicode_ci,"
    ."`post_title` text COLLATE utf8mb4_unicode_ci,"
    ."`post_excerpt` text COLLATE utf8mb4_unicode_ci,"
    ."`post_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'publish',"
    ."`comment_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'open',"
    ."`ping_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'open',"
    ."`post_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',"
    ."`post_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',"
    ."`to_ping` text COLLATE utf8mb4_unicode_ci,"
    ."`pinged` text COLLATE utf8mb4_unicode_ci,"
    ."`post_modified` datetime DEFAULT '0000-00-00 00:00:00',"
    ."`post_modified_gmt` datetime DEFAULT '0000-00-00 00:00:00',"
    ."`post_content_filtered` longtext COLLATE utf8mb4_unicode_ci,"
    ."`post_parent` bigint(20) unsigned DEFAULT '0',"
    ."`guid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',"
    ."`menu_order` int(11) DEFAULT '0',"
    ."`post_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'post',"
    ."`post_mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',"
    ."`comment_count` bigint(20) DEFAULT '0',"
    ."PRIMARY KEY (`ID`),"
    ."FULLTEXT INDEX (`post_content`)"
    .") ENGINE=Mroonga DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $res = mysql_query($sql)or die(mysql_error());
    mysql_close($connection);
  }

  private static function mrn_drop_table()
  {
    $dbname = "wddb";
    $tablename = "mrn_blogs";

    $connection = mysql_connect('localhost', 'root', 'P@ssw0rd');
    mysql_select_db($dbname, $connection);

    $sql = "DROP TABLE `{$tablename}`;";

    $res = mysql_query($sql)or die(mysql_error());
    mysql_close($connection);
  }

  public function fulltext_search($search, $wp_query)
  {
    if(isset($wp_query->query['s']))
    {
      $search = "SELECT SQL_CALC_FOUND_ROWS mrn_blogs.* "
      ."FROM mrn_blogs WHERE 1=1 "
      ."AND (( MATCH(mrn_blogs.post_content) "
      ."AGAINST('{$wp_query->query['s']}' IN BOOLEAN MODE) ));";
    }
    return $search;
  }
}

$MroongaSearch = new MroongaSearch();
add_action('publish_post', array($MroongaSearch, 'insert_data'), 10, 2);
add_filter('posts_request', array($MroongaSearch, 'fulltext_search'), 10, 2);

register_activation_hook(__FILE__, array('MroongaSearch', 'activate'));
register_deactivation_hook(__FILE__, array('MroongaSearch', 'deactivate'));
?>
