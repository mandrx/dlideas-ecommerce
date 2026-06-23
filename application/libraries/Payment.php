<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;

class Payment
{
    private $CI;
    private $webhook_secret;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->config->load('payment');

        require_once FCPATH . 'vendor/autoload.php';
        Stripe::setApiKey($this->CI->config->item('stripe_secret_key'));
        $this->webhook_secret = $this->CI->config->item('stripe_webhook_secret');
    }

    public function create_payment_intent($amount_cents, $currency = null, $metadata = [], $idempotency_key = null)
    {
        $currency = $currency ?? $this->CI->config->item('currency');
        $params   = [
            'amount'   => $amount_cents,
            'currency' => $currency,
            'metadata' => $metadata,
        ];
        $options = $idempotency_key ? ['idempotency_key' => $idempotency_key] : [];
        return PaymentIntent::create($params, $options);
    }

    public function retrieve_payment_intent($id)
    {
        return PaymentIntent::retrieve($id);
    }

    public function construct_webhook_event($payload, $sig_header)
    {
        return Webhook::constructEvent($payload, $sig_header, $this->webhook_secret);
    }

    public function get_publishable_key()
    {
        return $this->CI->config->item('stripe_publishable_key');
    }
}
