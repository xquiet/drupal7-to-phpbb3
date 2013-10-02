drupal7-to-phpbb3
=================

A basic tool to help webadmins during the migration of their forums from Drupal 7 advanced/forum to phpBB3

Instructions
-------------
This Script written to convert Drupal Forum (Or advanced forums) to phpbb3
You need a very fresh install of phpbb3 in the /phpbb3/ directory
Upload this script to the root of your web server and run it using a common browser.
The very first (admin) username you will register during installation
make sure does not exists in drupal already and that it is unique.
After running this script you have to click 'Clear cache' from phpbb administration
You will have to make some steps manually through the phpbb APC: 
 1. Clear cache from admin panel,
 2. Arrange all forums and redefine the categories/forums.
 3. Sync all posts and statistics.
 4. Give permission to forums for registered users group.
