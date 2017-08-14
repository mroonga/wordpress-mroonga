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

class MroongaSearch
{
  /* TODO: Move to construct. Add table name prefix. */
  private $table_name = "mrn_posts";

  public function activate()
  {
    $this->create_table();
    $this->copy_data();
  }

  public function deactivate()
  {
    $this->drop_table();
  }

  private function create_table()
  {
    global $wpdb;

    $wpdb->query("CREATE TABLE {$this->table_name} ( "
                 ."`post_id` bigint(20) unsigned NOT NULL, "
                 ."`post_title` text COLLATE utf8mb4_unicode_ci, "
                 ."`post_content` longtext COLLATE utf8mb4_unicode_ci, "
                 ."FULLTEXT INDEX (`post_title`, `post_content`) COMMENT 'normalizer \"NormalizerAuto\"'"
                 .") ENGINE=Mroonga "
                 ."DEFAULT CHARSET=utf8mb4 "
                 ."COLLATE=utf8mb4_unicode_ci;");
  }

  private function copy_data()
  {
    global $wpdb;

    $wpdb->query("INSERT INTO {$this->table_name} "
                 . "(post_id, post_title, post_content) "
                 . "SELECT ID, post_title, post_content "
                 . "FROM {$wpdb->posts}");
  }

  private function drop_table()
  {
    global $wpdb;

    $wpdb->query("DROP TABLE {$this->table_name}");
  }

  public function insert_data($post_id, $post)
  {
    global $wpdb;

    $wpdb->query($wpdb->prepare("INSERT INTO {$this->table_name} "
                                ."(post_id, post_title, post_content) "
                                ."VALUES "
                                ."(%s, %s, %s)",
                                $post_id,
                                $post->post_title,
                                $post->post_content));
  }

  public function fulltext_search($search, $wp_query)
  {
    return '';
  }

  public function fulltext_search_join($join, $wp_query)
  {
    global $wpdb;

    $search_query = $wp_query->get("s");
    if (strlen($search_query) > 0)
    {
      $join .= $wpdb->prepare(" INNER JOIN (SELECT post_id, "
                              . "MATCH (post_title, post_content) "
                              . "AGAINST (%s IN BOOLEAN MODE) AS score "
                              . "FROM {$this->table_name} WHERE "
                              . "MATCH (post_title, post_content) "
                              . "AGAINST (%s IN BOOLEAN MODE)) AS matched_posts "
                              . "ON {$wpdb->posts}.ID = matched_posts.post_id",
                              "*D+W1:10,2:1 $search_query",
                              "*D+W1:10,2:1 $search_query");
    }
    return $join;
  }

  public function fulltext_search_orderby($orderby, $wp_query)
  {
    global $wpdb;

    $search_query = $wp_query->get("s");
    if (strlen($search_query) > 0)
    {
      $orderby = 'score DESC';
    }
    return $orderby;
  }
}

$MroongaSearch = new MroongaSearch();

register_activation_hook(__FILE__, array($MroongaSearch, 'activate'));
register_deactivation_hook(__FILE__, array($MroongaSearch, 'deactivate'));

add_action('publish_post', array($MroongaSearch, 'insert_data'), 10, 2);
add_filter('posts_search', array($MroongaSearch, 'fulltext_search'), 10, 2);
add_filter('posts_join', array($MroongaSearch, 'fulltext_search_join'), 10, 2);
add_filter('posts_search_orderby', array($MroongaSearch, 'fulltext_search_orderby'), 10, 2);
