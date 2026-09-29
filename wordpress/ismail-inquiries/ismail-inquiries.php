<?php
/* SPDX-License-Identifier: MIT
 * Copyright (c) 2026 Ismail Habib
 */

/* Plugin Name: Ismail Inquiry Desk
Description: Local WordPress inquiry storage, validated form, staff review and CSV export.
Version: 1.0.0
Author: Ismail Habib
License: MIT
License URI: https://opensource.org/license/mit/
*/
if (!defined('ABSPATH')) exit;
function ih_table(){ global $wpdb; return $wpdb->prefix.'ih_inquiries'; }
register_activation_hook(__FILE__, function(){
 global $wpdb; require_once ABSPATH.'wp-admin/includes/upgrade.php';
 $table=ih_table(); $charset=$wpdb->get_charset_collate();
 dbDelta("CREATE TABLE $table (id bigint unsigned NOT NULL AUTO_INCREMENT, created datetime NOT NULL, name varchar(120) NOT NULL, email varchar(254) NOT NULL, service varchar(40) NOT NULL, message text NOT NULL, status varchar(20) DEFAULT 'new' NOT NULL, PRIMARY KEY (id)) $charset;");
 $pages=array('contact'=>array('Let’s talk','[ih_inquiry_form]'),'services'=>array('Practical help for connected businesses','<h2>AI automation</h2><p>Connect website interfaces, backend processing and AI features into useful, reviewable workflows.</p><h2>API & system integration</h2><p>Connect external services, trace failed requests and improve existing integration logic.</p><h2>Web development</h2><p>WordPress themes, layouts, forms, backend connections and maintenance.</p><h2>Legacy modernization</h2><p>Review existing C++ Windows code, adapt compatibility and verify preserved behavior.</p>'),'approach'=>array('Hi, I’m Ismail.','<p>My work spans AI automation, API integration, existing C++ Windows applications and WordPress websites. I start with the workflow and its constraints, implement a focused change and test the complete path.</p>'),'data-privacy'=>array('Data & privacy','<p>This form stores your name, email, service, message and submission time in this WordPress database. Staff with administrator permissions can review, export and delete inquiries. No email is sent by this plugin. No analytics or third-party tracking are included.</p><p>The operator must add their contact information, appropriate privacy notice and retention policy before public deployment.</p>'));
 foreach($pages as $slug=>$content){ if(!get_page_by_path($slug)) wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_name'=>$slug,'post_title'=>$content[0],'post_content'=>$content[1])); }
});
add_shortcode('ih_inquiry_form',function(){ ob_start(); if(isset($_GET['inquiry']) && $_GET['inquiry']==='saved') echo '<div class="notice" role="status">Your inquiry is saved. This form does not send email.</div>'; ?>
<div class="formlayout"><aside><h3>A little context goes a long way.</h3><p>Tell me about the task, the systems involved and the result you need. Avoid secrets and confidential documents.</p></aside><div class="formpanel"><form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="ih_submit"><?php wp_nonce_field('ih_submit','ih_nonce'); ?><label for="ih-name">Name *</label><input id="ih-name" name="name" maxlength="120" required autocomplete="name"><label for="ih-email">Email *</label><input id="ih-email" name="email" type="email" maxlength="254" required autocomplete="email"><label for="ih-service">Service *</label><select id="ih-service" name="service"><option value="automation">AI automation</option><option value="integration">API integration</option><option value="web">Web development</option><option value="legacy">Legacy modernization</option><option value="unsure">Help me define the scope</option></select><label for="ih-message">Project message *</label><textarea id="ih-message" name="message" minlength="20" maxlength="5000" required></textarea><div class="honeypot" aria-hidden="true"><label>Leave empty<input name="website" tabindex="-1" autocomplete="off"></label></div><label class="check"><input type="checkbox" name="consent" value="yes" required><span>I agree to storing this inquiry for review. <a href="<?php echo esc_url(home_url('/data-privacy/')); ?>">Data & privacy</a></span></label><button type="submit">Send inquiry ↗</button></form></div></div>
<?php return ob_get_clean(); });
function ih_submit(){
 if(!isset($_POST['ih_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ih_nonce'])),'ih_submit')) wp_die('Invalid form session.',403);
 $name=sanitize_text_field(wp_unslash($_POST['name']??'')); $email=sanitize_email(wp_unslash($_POST['email']??'')); $service=sanitize_key($_POST['service']??''); $message=sanitize_textarea_field(wp_unslash($_POST['message']??''));
 if(!empty($_POST['website']) || empty($name) || strlen($name)>120 || !is_email($email) || !in_array($service,array('automation','integration','web','legacy','unsure'),true) || strlen($message)<20 || strlen($message)>5000 || ($_POST['consent']??'')!=='yes') wp_die('Please check required fields, email, consent and a message of 20–5000 characters. Use Back to correct the form.',400);
 global $wpdb; $saved=$wpdb->insert(ih_table(),array('created'=>current_time('mysql',true),'name'=>$name,'email'=>$email,'service'=>$service,'message'=>$message,'status'=>'new'),array('%s','%s','%s','%s','%s','%s'));
 if(!$saved) wp_die('Storage failed. Please try again.',500);
 wp_safe_redirect(add_query_arg('inquiry','saved',home_url('/contact/')));exit;
}
add_action('admin_post_ih_submit','ih_submit');add_action('admin_post_nopriv_ih_submit','ih_submit');
add_action('admin_menu',function(){add_menu_page('Inquiry desk','Inquiry desk','manage_options','ih-inquiries','ih_inbox','dashicons-email-alt');});
function ih_inbox(){ if(!current_user_can('manage_options')) return; global $wpdb; $rows=$wpdb->get_results('SELECT * FROM '.ih_table().' ORDER BY id DESC'); echo '<div class="wrap"><h1>Inquiry desk</h1><p>Stored in this WordPress installation. Email delivery is not enabled.</p><a class="button" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=ih_export'),'ih_export')).'">Export CSV</a>';
 foreach($rows as $row){ echo '<div style="background:white;padding:20px;margin:20px 0;border:1px solid #ddd"><h2>'.esc_html($row->name).' — '.esc_html($row->service).'</h2><p>'.esc_html($row->email).' · '.esc_html($row->created).' UTC · '.esc_html($row->status).'</p><p>'.nl2br(esc_html($row->message)).'</p><form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="ih_update"><input type="hidden" name="id" value="'.absint($row->id).'">';wp_nonce_field('ih_update');echo '<button class="button" name="operation" value="reviewed">Mark reviewed</button> <button class="button" name="operation" value="delete">Delete</button></form></div>'; }
 if(!$rows) echo '<p>No inquiries yet.</p>'; echo '</div>';
}
add_action('admin_post_ih_update',function(){if(!current_user_can('manage_options'))wp_die('Access denied',403);check_admin_referer('ih_update');global $wpdb;$id=absint($_POST['id']??0);if(($_POST['operation']??'')==='delete')$wpdb->delete(ih_table(),array('id'=>$id),array('%d'));elseif(($_POST['operation']??'')==='reviewed')$wpdb->update(ih_table(),array('status'=>'reviewed'),array('id'=>$id),array('%s'),array('%d'));wp_safe_redirect(admin_url('admin.php?page=ih-inquiries'));exit;});
add_action('admin_post_ih_export',function(){if(!current_user_can('manage_options'))wp_die('Access denied',403);check_admin_referer('ih_export');global $wpdb;$rows=$wpdb->get_results('SELECT * FROM '.ih_table().' ORDER BY id DESC',ARRAY_A);header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="inquiries.csv"');$f=fopen('php://output','w');fputcsv($f,array('ID','Created UTC','Name','Email','Service','Message','Status'));foreach($rows as $row){foreach($row as &$v){if(preg_match('/^[=+@\-\t\r]/',(string)$v))$v="'".$v;}unset($v);fputcsv($f,$row);}fclose($f);exit;});
