<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['stripe_secret_key']      = getenv('STRIPE_SECRET_KEY');
$config['stripe_publishable_key'] = getenv('STRIPE_PUBLISHABLE_KEY');
$config['stripe_webhook_secret']  = getenv('STRIPE_WEBHOOK_SECRET');
$config['currency']               = 'sgd';
