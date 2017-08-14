<?php
/*
Plugin Name: Mroonga
Plugin URI: https://github.com/mroonga/wordpress-mroonga
Description: This plugin provides fast and rich full text search features based on Mroonga. Mroonga is a MySQL/MariaDB plugin. You don't need to add a new server only for full text search. You can use existing MySQL/MariaDB server. It reduces maintainance cost.
Version: 0.1
Author: Yasuhiro Horimoto
Author URI: https://www.clear-code.com/
License: GPL2
*/

class MroongaSearch
{
  public function table_name()
  {
    global $wpdb;

    return $wpdb->prefix . "mrn_posts";
  }

  public function activate()
  {
    $this->ensure_mroonga();
    $this->create_table();
    $this->copy_data();
    $this->create_index();
  }

  public function deactivate()
  {
    $this->drop_table();
  }

  private function ensure_mroonga()
  {
    global $wpdb;

    if ($wpdb->query("SELECT name FROM mysql.plugin "
                     . "WHERE name = 'Mroonga'") > 0) {
      return;
    }

    $wpdb->query("INSTALL PLUGIN Mroonga SONAME 'ha_mroonga.so'");
    // TODO Report error on failure
  }

  private function create_table()
  {
    global $wpdb;

    $wpdb->query("CREATE TABLE {$this->table_name()} ( "
                 . "`post_id` bigint(20) unsigned PRIMARY KEY, "
                 . "`post_title` text COLLATE utf8mb4_unicode_ci, "
                 . "`post_content` longtext COLLATE utf8mb4_unicode_ci"
                 . ") ENGINE=Mroonga "
                 . "DEFAULT CHARSET=utf8mb4 "
                 . "COLLATE=utf8mb4_unicode_ci;");
  }

  private function copy_data()
  {
    global $wpdb;

    $wpdb->query("INSERT INTO {$this->table_name()} "
                 . "(post_id, post_title, post_content) "
                 . "SELECT ID, post_title, post_content "
                 . "FROM {$wpdb->posts} "
                 . "WHERE post_status = 'publish'");
  }

  private function create_index()
  {
    global $wpdb;

    $wpdb->query("ALTER TABLE {$this->table_name()} "
                 . "ADD FULLTEXT INDEX "
                 . "(post_title, post_content) "
                 . "COMMENT 'normalizer \"NormalizerAuto\"'");
  }

  private function drop_table()
  {
    global $wpdb;

    $wpdb->query("DROP TABLE {$this->table_name()}");
  }

  public function update_post($post_id, $post)
  {
    global $wpdb;

    $wpdb->replace($this->table_name(),
                   array("post_id" => $post_id,
                         "post_title" => $post->post_title,
                         "post_content" => $post->post_content));
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
      $boolean_mode_query = "*D+W1:10,2:1 $search_query";
      $join .= $wpdb->prepare(" INNER JOIN (SELECT post_id, "
                              . "MATCH (post_title, post_content) "
                              . "AGAINST (%s IN BOOLEAN MODE) AS score "
                              . "FROM {$this->table_name()} WHERE "
                              . "MATCH (post_title, post_content) "
                              . "AGAINST (%s IN BOOLEAN MODE)) AS matched_posts "
                              . "ON {$wpdb->posts}.ID = matched_posts.post_id",
                              $boolean_mode_query,
                              $boolean_mode_query);
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

add_action('publish_post', array($MroongaSearch, 'update_post'), 10, 2);
add_filter('posts_search', array($MroongaSearch, 'fulltext_search'), 10, 2);
add_filter('posts_join', array($MroongaSearch, 'fulltext_search_join'), 10, 2);
add_filter('posts_search_orderby', array($MroongaSearch, 'fulltext_search_orderby'), 10, 2);
