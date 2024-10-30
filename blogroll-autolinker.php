<?php

/*
Plugin Name: Blogroll Autolinker
Plugin URI: http://stevenberg.net/projects/blogroll-autolinker
Description: Automatically turns names from your blogroll into links in your posts.
Version: 1.2
Author: Steven Berg
Author URI: http://stevenberg.net/

    Copyright 2008  Steven Berg  (email : steven@stevenberg.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class BlogrollAutolinker {

    function activate() {
        add_option('blogroll-autolinker-begin', '[');
        add_option('blogroll-autolinker-end', ']');
    }

    function init() {
        add_action('admin_menu', array(__CLASS__, 'add_options_page'));
        add_filter('the_content', array(__CLASS__, 'linkify'));
    }

    function add_options_page() {
        add_options_page(
            'Blogroll Autolinker Options',
            'Blogroll Autolinker',
            'manage_options',
            'blogroll-autolinker-options',
            array(__CLASS__, 'options_page'));
    }

    function linkify($content) {
        $names = array();
        $links = array();
        $begin = get_option('blogroll-autolinker-begin');
        $end = get_option('blogroll-autolinker-end');
        foreach (get_terms('link_category') as $category) {
            foreach (get_bookmarks("category={$category->term_id}") as $link) {
                $html = "<a href='{$link->link_url}' rel='external {$link->link_rel}' title='{$link->link_description}'>{$link->link_name}</a>";
                $names[] = $begin . $link->link_name . $end;
                $links[] = $html;
                $names[] = $begin . $category->name . '/' . $link->link_name . $end;
                $links[] = $html;
            }
        }
        return str_replace($names, $links, $content);
    }

    function options_page() {
        global $wpdb;
        if (!current_user_can('manage_options')) {
            die('You don&#8217;t have sufficient permission to access this file.');
        }
        if (isset($_POST['update'])) {
            check_admin_referer('blogroll-autolinker-update-options');
            update_option('blogroll-autolinker-begin', $_POST['begin']);
            update_option('blogroll-autolinker-end', $_POST['end']);
            echo '<div id="message" class="updated fade"><p><strong>Options saved.</strong></p></div>';
        }
        $begin = get_option('blogroll-autolinker-begin');
        $end = get_option('blogroll-autolinker-end');
?>
<div class="wrap">
<h2>Blogroll Autolinker Options</h2>
<p>With your current options, you can insert a link like this: <strong><code><?php echo $begin ?>name<?php echo $end ?></code></strong> or <strong><code><?php echo $begin ?>category/name<?php echo $end ?></code></strong>.</p>
<form method="post" action="">
<?php if (function_exists('wp_nonce_field')) wp_nonce_field('blogroll-autolinker-update-options'); ?>
<table class="optiontable">
<tr valign="top">
<th scope="row"><label for="begin">Begin linked name:</label></th>
<td><input name="begin" id="begin" type="text" value="<?php form_option('blogroll-autolinker-begin') ?>" size="1" /></td>
</tr>
<tr valign="top">
<th scope="row"><label for="end">End linked name:</label></th>
<td><input name="end" id="end" type="text" value="<?php form_option('blogroll-autolinker-end') ?>" size="1" /></td>
</tr>
</table>
<p class="submit"><input name="update" type="submit" value="Update Options &raquo;" /></p>
</form>
</div>
<?php
    }
}

add_action('init', array('BlogrollAutolinker', 'init'));
add_action('activate_'.basename(__FILE__), array('BlogrollAutolinker', 'activate'));

