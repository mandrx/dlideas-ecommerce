<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('format_currency')) {
    function format_currency($amount, $symbol = 'S$')
    {
        return $symbol . ' ' . number_format((float) $amount, 2, '.', ',');
    }
}

if (!function_exists('format_date')) {
    function format_date($datetime, $format = 'M j, Y')
    {
        if (empty($datetime) || strtotime($datetime) === false) {
            return $datetime;
        }
        return date($format, strtotime($datetime));
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime($datetime)
    {
        if (empty($datetime) || strtotime($datetime) === false) {
            return $datetime;
        }
        return date('M j, Y g:i A', strtotime($datetime));
    }
}

if (!function_exists('slugify')) {
    function slugify($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        $CI =& get_instance();
        return '<input type="hidden" name="' . $CI->security->get_csrf_token_name()
            . '" value="' . $CI->security->get_csrf_hash() . '">';
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text($text, $limit = 100, $suffix = '...')
    {
        if (mb_strlen($text) <= $limit) return $text;
        return mb_substr($text, 0, $limit) . $suffix;
    }
}
