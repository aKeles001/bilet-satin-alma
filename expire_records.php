<?php
include_once __DIR__ . '/src/helper.php';


date_default_timezone_set('Europe/Istanbul');

$coupons = get_coupons();
foreach ($coupons as $coupon) {
    $departure_raw = str_replace('T', ' ', $coupon['expire_date']);
    $departure_timestamp = strtotime($departure_raw);

    if ($departure_timestamp < time()) {
        updateCouponStatus($coupon['id']);
    }
}

$tickets = get_tickets();
foreach ($tickets as $ticket) {
    $departure_raw = str_replace('T', ' ', $ticket['departure_time']);
    $departure_timestamp = strtotime($departure_raw);
    if ($departure_timestamp < time()) {
        updateTicketStatus($ticket['id']);
    }
}