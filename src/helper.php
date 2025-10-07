<?php

function uuid()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function validate_registration($full_name, $email, $password) {
    if (empty($full_name) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
    }
    return ['success' => true];
}

function book_ticket($trip_id, $user_id) {
    require __DIR__ . '/db_connect.php';
    // Check trip exists and has capacity
    $stmt = $db->prepare('SELECT capacity, price FROM Trips WHERE id = :trip_id');
    $stmt->execute([':trip_id' => $trip_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$trip) {
        return ['success' => false, 'message' => 'Trip not found.'];
    }
    // Seat check
    $stmt = $db->prepare('SELECT COUNT(*) FROM Tickets WHERE trip_id = :trip_id AND status = "active"');
    $stmt->execute([':trip_id' => $trip_id]);
    $booked = $stmt->fetchColumn();
    if ($booked >= $trip['capacity']) {
        return ['success' => false, 'message' => 'No seats available.'];
    }
    // Balance operations
    $stmt = $db->prepare('SELECT balance FROM User WHERE id = :user_id');
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['balance'] < $trip['price']) {
        return ['success' => false, 'message' => 'Insufficient balance.'];
    }

    $stmt = $db->prepare('UPDATE User SET balance = balance - :price WHERE id = :user_id');
    $stmt->execute([':price' => $trip['price'], ':user_id' => $user_id]);
    // Create ticket
    $ticket_id = uuid();
    $stmt = $db->prepare('INSERT INTO Tickets (id, trip_id, user_id, total_price) VALUES (:id, :trip_id, :user_id, :total_price)');
    $stmt->execute([
        ':id' => $ticket_id,
        ':trip_id' => $trip_id,
        ':user_id' => $user_id,
        ':total_price' => $trip['price']
    ]);
    // Seat assignment
    $seat_number = $booked + 1;
    $booked_seat_id = uuid();
    $stmt = $db->prepare('INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (:id, :ticket_id, :seat_number)');
    $stmt->execute([
        ':id' => $booked_seat_id,
        ':ticket_id' => $ticket_id,
        ':seat_number' => $seat_number
    ]);

    $stmt = $db->prepare('UPDATE Trips SET capacity = capacity - 1 WHERE id = :trip_id');
    $stmt->execute([':trip_id' => $trip_id]);
    return ['success' => true, 'message' => 'Ticket booked successfully! Seat: ' . $seat_number, 'ticket_id' => $ticket_id, 'seat_number' => $seat_number];
}
// Get balance
function get_user_balance($user_id) {
    require __DIR__ . '/db_connect.php';
    $stmt = $db->prepare('SELECT balance, full_name FROM User WHERE id = :id');
    $stmt->execute([':id' => $user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get user tickets
function get_user_tickets($user_id, $limit = 5) {
    require __DIR__ . '/db_connect.php';
    $stmt = $db->prepare('SELECT t.id, tr.departure_city, tr.destination_city, tr.departure_time, t.status
        FROM Tickets t
        JOIN Trips tr ON t.trip_id = tr.id
        WHERE t.user_id = :user_id
        ORDER BY t.created_at DESC
        LIMIT :limit');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>