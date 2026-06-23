<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['protocol']   = 'smtp';
$config['smtp_host']  = getenv('MAIL_HOST') ?: 'smtp.mailtrap.io';
$config['smtp_port']  = getenv('MAIL_PORT') ?: 587;
$config['smtp_user']  = getenv('MAIL_USER') ?: '';
$config['smtp_pass']  = getenv('MAIL_PASS') ?: '';
$config['mailtype']   = 'html';
$config['charset']    = 'utf-8';
$config['from_email'] = getenv('MAIL_FROM') ?: 'noreply@ci3ecomm.local';
$config['from_name']  = 'CI3 Ecomm';
