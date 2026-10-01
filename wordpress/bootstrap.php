<?php
define('WP_INSTALLING',true);
$_SERVER['HTTP_HOST']='127.0.0.1:8187';
$_SERVER['REQUEST_METHOD']='GET';
require '/var/www/html/wp-load.php';
require_once ABSPATH.'wp-admin/includes/upgrade.php';
require_once ABSPATH.'wp-admin/includes/plugin.php';
add_filter('pre_wp_mail',function(){return false;});
if(!is_blog_installed()){wp_install('Ismail Habib','port_admin','admin@example.invalid',false,'',getenv('port_ADMIN_PASSWORD'));}
update_option('siteurl','http://127.0.0.1:8187');
update_option('home','http://127.0.0.1:8187');
update_option('permalink_structure','/%postname%/');
$result=activate_plugin('ismail-inquiries/ismail-inquiries.php');
if(is_wp_error($result)){fwrite(STDERR,$result->get_error_message());exit(1);}
switch_theme('ismail-studio');
flush_rewrite_rules(true);
echo 'WordPress '.get_bloginfo('version').' initialized with ismail-studio and ismail-inquiries'.PHP_EOL;
