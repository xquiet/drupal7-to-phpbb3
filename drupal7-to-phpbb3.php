<?php
/** Drupal 7 To phpBB3 migration tool.
  * Authors: Frapedas - Blog: http://xakers-gr.blogspot.com
  * 	     Matteo Pasotti <matteo.pasotti@gmail.com>
  
  * This Script written to convert Drupal Forum (Or advanced forums) to phpbb3
  * You need a very fresh install of phpbb3 in the /phpbb3/ directory
  * Upload this script in root / and run from browser.  
  * the very first (admin) username you will register during installation
  * make sure does not exists in drupal already and is unique.
  * After running this script you have to click 'Clear cache' from phpbb administration
  * You will have after Manually from phpbb admin: a
  * 1. Clear cache from admin panel, 
  * 2. Arrange all forums and redefine the categories/forums.
  * 3. Sync all posts and statistics. 
  * 4. Give permission to forums for registered users group.
  *
  *
  * This program is free software: you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation, either version 3 of the License, or
  * (at your option) any later version.
  * 
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  * GNU General Public License for more details.
  * 
  * You should have received a copy of the GNU General Public License
  * along with this program. If not, see <http://www.gnu.org/licenses/>.
  *
  * Copyright: 2013 by Matteo Pasotti <matteo.pasotti@gmail.com>
  
  */

$mysqli_host = "localhost";
$mysqli_username = "root";
$mysqli_pass = ""; 

$phpbb_db = "bb";  // <---- your phpbb db name
$drupal_db = "drupal"; // <---- your drupal db name

ini_set('max_execution_time', 600);  // increase the execution time to avoid timeout issues

/* allow usage of some phpBB functions */
define('IN_PHPBB', true);

$phpEx = "php";
chdir('phpbb3');
include('phpBB3/includes/functions.php');
include('phpBB3/includes/utf/utf_tools.php');

$con = mysqli_connect($mysqli_host,$mysqli_username,$mysqli_pass);
if (!$con){
  die('Could not connect: ' . mysqli_error());
}
mysqli_query($con,"SET NAMES utf8");
echo "<pre>".mysqli_error($con)."</pre>";

echo "<b>Lets try to convert this...</b><p><p><hr>";
//First we insert all the Drupal Users..
echo "<h3>Migrating users</h3>";
$sa = mysqli_query($con,"SELECT * FROM $drupal_db.users"); 
while($row = mysqli_fetch_array($sa)) {
  echo "Users: ".$row[0]."<br>";
  $row[0] = $row[0] + 53; //We jump 53 in ids because phpbb3 have already 53 bots+anonymous+admin account.
  $email_has = phpbb_email_hash($row['mail']);
  $normalized = @utf8_clean_string($row['name']);

  echo "<pre>$email_has&nbsp;$normalized</pre>";
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_users (user_id, user_type, user_regdate, username, username_clean, user_password, user_email, user_email_hash, group_id) VALUES ('$row[0]' ,'0',  '$row[created]', '$row[name]',  '$normalized', '$row[pass]', '$row[mail]', '$email_has','2')");   
  echo "<pre>".mysqli_error($con)."</pre>";
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_user_group (group_id, user_id, user_pending) VALUES ('2', '$row[0]','0')"); 
  echo "<pre>".mysqli_error($con)."</pre>";
}
echo "<h3>Migrating comments as posts</h3>";
//We insert the content of the comments as posts
$sa = mysqli_query($con,"SELECT * FROM $drupal_db.field_data_comment_body WHERE bundle='comment_node_forum'"); 
while($row = mysqli_fetch_array($sa)) {
  echo "<pre>".$roz['subject']."</pre>";
  if ($row[0] > 1) {
  }
  $roz = mysqli_fetch_array(mysqli_query($con,"SELECT * FROM $drupal_db.comment WHERE cid='$row[entity_id]'"));
  $roz['uid'] = $roz['uid'] + 53;
  $roz['subject'] = mysqli_real_escape_string($con,$roz['subject']);
  $row['comment_body_value'] = mysqli_real_escape_string($con,$row['comment_body_value']);
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_posts (post_id, topic_id, poster_id, post_time, post_subject, post_text) VALUES ('$roz[cid]','$roz[nid]','$roz[uid]','$roz[created]', '$roz[subject]','$row[comment_body_value]'  )");
  echo "<pre>".mysqli_error($con)."</pre>";
}
echo "<h3>Migrating topics as posts</h3>";
//We insert the content of topics as posts 
$sa = mysqli_query($con,"SELECT * FROM $drupal_db.field_data_body WHERE bundle='forum'");
while($row = mysqli_fetch_array($sa)) {
  if ($row[0] > 1) {
  }
  $roz = mysqli_fetch_array(mysqli_query($con,"SELECT * FROM $drupal_db.node WHERE nid='$row[entity_id]'"));
  $roz['uid'] = $roz['uid'] + 53;
  $row['body_value'] = mysqli_real_escape_string($con,$row['body_value']);
  $roz['title'] = mysqli_real_escape_string($con,$roz['title']);
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_posts (post_id, topic_id, poster_id, post_time, post_subject, post_text) VALUES ('$roz[nid]','$row[entity_id]','$roz[uid]','$roz[created]', '$roz[title]','$row[body_value]'  )");
  echo "<pre>".mysqli_error($con)."</pre>";
}
echo "<h3>Migrating topic titles</h3>";
//We Insert the topic titles etc. 
$sa = mysqli_query($con,"SELECT * FROM $drupal_db.node WHERE type='forum' AND nid<vid"); 
while($row = mysqli_fetch_array($sa)) {
  if ($row[0] > 1) {
  }
  $row['uid'] = $row['uid'] + 53;
  $roz = mysqli_fetch_array(mysqli_query($con,"SELECT * FROM $drupal_db.taxonomy_index WHERE nid='$row[nid]'"));
  $row['title'] = mysqli_real_escape_string($con,$row['title']);
  echo "<pre>Adding: ".$row['title']."</pre>";
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_topics (topic_id, forum_id, topic_title, topic_poster, topic_time) VALUES ('$row[0]', ' $roz[tid]', '$row[title]', '$row[uid]', '$row[created]')");
  echo "<pre>".mysqli_error($con)."</pre>";
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_topics_posted VALUES ('$row[uid]','$row[0]','1' )");
  echo "<pre>".mysqli_error($con)."</pre>";
}
echo "<h3>Migrating forums</h3>";
//Insert the forums
$i = 20;
$sa = mysqli_query($con,"SELECT * FROM $drupal_db.taxonomy_term_data WHERE vid='2'"); 
while($row = mysqli_fetch_array($sa)) {
  echo "Forums: ".$row['name']."<br>";
  $left = $i;
  $right = $i + 1;
  $i = $i + 2;

  $row['description'] = mysqli_real_escape_string($con,$row['description']);
  $row['name'] = mysqli_real_escape_string($con,$row['name']);
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_forums (forum_id, forum_name, forum_desc, left_id, right_id, forum_type) VALUES ('$row[tid]' ,'$row[name]','$row[description]', '$left', '$right', '1')");   
  echo "<pre>".mysqli_error($con)."</pre>";
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_acl_groups (group_id, forum_id, auth_role_id) VALUES ('1', '$row[tid]', '17')");
  echo "<pre>".mysqli_error($con)."</pre>";
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_acl_groups (group_id, forum_id, auth_role_id) VALUES ('2', '$row[tid]', '17')");
  echo "<pre>".mysqli_error($con)."</pre>";
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_acl_groups (group_id, forum_id, auth_role_id) VALUES ('3', '$row[tid]', '17')");
  echo "<pre>".mysqli_error($con)."</pre>";
  mysqli_query($con,"INSERT INTO $phpbb_db.phpbb_acl_groups (group_id, forum_id, auth_role_id) VALUES ('6', '$row[tid]', '17')");
  echo "<pre>".mysqli_error($con)."</pre>";
}
//end.
?>
