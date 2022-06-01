<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('PAYMENT_NETWORK_ENDPOINT_HOSTED', 'https://gateway.example.com/hosted/');
fn_define('PAYMENT_NETWORK_ENDPOINT_HOSTED_MODAL', 'https://gateway.example.com/hosted/modal/');
fn_define('PAYMENT_NETWORK_ENDPOINT_HOSTED_DIRECT', 'https://gateway.example.com/direct/');

fn_define('PAYMENT_NETWORK_TYPE_HOSTED_V1', 'hosted');
fn_define('PAYMENT_NETWORK_TYPE_HOSTED_V2', 'hosted_v2');
fn_define('PAYMENT_NETWORK_TYPE_IFRAME_V1', 'iframe');
fn_define('PAYMENT_NETWORK_TYPE_IFRAME_V2', 'iframe_v2');

fn_define('PAYMENT_NETWORK_DEFAULT_MERCHANT_ID', '100856');
fn_define('PAYMENT_NETWORK_DEFAULT_SECRET', 'Circle4Take40Idea');