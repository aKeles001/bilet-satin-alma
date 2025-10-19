<?php
require_once __DIR__ . '/../src/auth.php';
function uuid()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Validate registration
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

// Book ticket
function book_ticket($trip_id, $user_id) {
    require __DIR__ . '/db_connect.php';

    $stmt = $db->prepare('SELECT capacity, price FROM Trips WHERE id = :trip_id');
    $stmt->execute([':trip_id' => $trip_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$trip) {
        return ['success' => false, 'message' => 'Trip not found.'];
    }
    // Seat check
    $booked = available_seats($trip_id);
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
    return ['success' => true, 'message' => 'Ticket booked successfully! Seat: ' . $seat_number, 'ticket_id' => $ticket_id, 'seat_number' => $seat_number];
}
// Get balance
function get_user_balance($user_id) {
    require __DIR__ . '/db_connect.php';
    
    $stmt = $db->prepare('SELECT balance, full_name FROM User WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $user_id, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && isset($user['balance'])) {
        $user['balance'] = (float) $user['balance'];
    return $user;
    }
}



// Get user tickets
function get_user_tickets($user_id, $limit = 5) {
    global $db;
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

// Get company trips
function get_company_trips($company_id) {
    require __DIR__ . '/db_connect.php';
    $stmt = $db->prepare('SELECT id, company_id, destination_city, departure_city, departure_time, price capacity, created_date
        FROM Trips
        WHERE company_id = :company_id
        ORDER BY created_date DESC');
    $stmt->bindValue(':company_id', $company_id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function get_company_trip($trip_id) {
    global $db;
    $stmt = $db->prepare('SELECT * FROM Trips WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $trip_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



// Register trip
function registerTrip($destination_city, $departure_city, $departure_time, $arrival_time, $price, $capacity, $company_id)
{
    global $db;

    $destination_city = trim($destination_city ?? '');
    $departure_city = trim($departure_city ?? '');
    $departure_time = trim($departure_time ?? '');
    $arrival_time = trim($arrival_time ?? '');
    $price = trim($price ?? '');
    $capacity = trim($capacity ?? '');
    $company_id = trim($company_id ?? '');

    if (!$destination_city || !$departure_city || !$departure_time || !$arrival_time || !$price || !$capacity || !$company_id) {
         return ['success' => false, 'message' => 'All fields are required.'];
    }
    // Check if user exists
    $id = uuid();

    $stmt = $db->prepare("INSERT INTO `Trips` (id, destination_city, departure_city, departure_time, arrival_time, price, capacity, company_id) VALUES (:id, :destination_city, :departure_city, :departure_time, :arrival_time, :price, :capacity, :company_id)");
    $result = $stmt->execute([
        ':id' => $id,
        ':destination_city' => $destination_city,
        ':departure_city' => $departure_city,
        ':departure_time' => $departure_time,
        ':arrival_time' => $arrival_time,
        ':price' => $price,
        ':capacity' => $capacity,
        ':company_id' => $company_id
    ]);

    if ($result) {
        return ['success' => true, 'message' => 'Sefer başarıyla eklendi.'];
    } else {
        return ['success' => false, 'message' => 'Sefer eklenirken bir hata oluştu.'];
    }
}

function cancel_trip($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM Trips WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $result = $stmt->execute();
    if ($result) {
        return ['success' => true, 'message' => 'Sefer başarıyla kaldırılıd.'];
    } else {
        return ['success' => false, 'message' => 'Sefer kaldırılırken bir hata oluştu.'];
    }
}

function edit_trip($trip_id, $destination_city, $departure_city, $departure_time, $arrival_time, $price, $capacity, $company_id) {
    global $db;

    $trip_id = trim($trip_id ?? '');
    $destination_city = trim($destination_city ?? '');
    $departure_city = trim($departure_city ?? '');
    $departure_time = trim($departure_time ?? '');
    $arrival_time = trim($arrival_time ?? '');
    $price = trim($price ?? '');
    $capacity = trim($capacity ?? '');
    $company_id = trim($company_id ?? '');

    if (!$destination_city || !$departure_city || !$departure_time || !$arrival_time || !$price || !$capacity) {
         return ['success' => false, 'message' => 'All fields are required.'];
    }

    $stmt = $db->prepare("UPDATE Trips 
        SET destination_city = :destination_city,
            departure_city = :departure_city,
            departure_time = :departure_time,
            arrival_time = :arrival_time,
            price = :price,
            capacity = :capacity
        WHERE id = :trip_id AND company_id = :company_id");

    $stmt->bindValue(':destination_city', $destination_city, PDO::PARAM_STR);
    $stmt->bindValue(':departure_city', $departure_city, PDO::PARAM_STR);
    $stmt->bindValue(':departure_time', $departure_time, PDO::PARAM_STR);
    $stmt->bindValue(':arrival_time', $arrival_time, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_INT);
    $stmt->bindValue(':capacity', $capacity, PDO::PARAM_INT);
    $stmt->bindValue(':trip_id', $trip_id);
    $stmt->bindValue(':company_id', $company_id);

    $result = $stmt->execute();

    if ($result) {
        return ['success' => true, 'message' => 'Sefer başarıyla güncellendi.'];
    } else {
        return ['success' => false, 'message' => 'Sefer güncellenirken bir hata oluştu.'];
    }
}

function add_coupon($code, $discount, $usage_limit, $expire_date, $company_id) {
    global $db;
    $id = uuid();
    $code = trim($code ?? '');
    $discount = trim($discount ?? '');
    $usage_limit = trim($usage_limit ?? '');
    $expire_date = trim($expire_date ?? '');
    $company_id = trim($company_id ?? '');

    if (!$code || !$discount || !$usage_limit || !$expire_date || !$company_id) {
         return ['success' => false, 'message' => 'All fields are required.'];
    }

    $stmt = $db->prepare("SELECT id FROM `Coupons` WHERE code = :code AND company_id = :company_id");
    $stmt->execute([':code' => $code, ':company_id' => $company_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'message' => 'Coupon code already exists.'];
    }
    else {
        $stmt = $db->prepare("INSERT INTO `Coupons` (id, code, discount, usage_limit, expire_date, company_id) VALUES (:id, :code, :discount, :usage_limit, :expire_date, :company_id)");
        $result = $stmt->execute([
        ':id' => $id,
        ':code' => $code,
        ':discount' => $discount,
        ':usage_limit' => $usage_limit,
        ':expire_date' => $expire_date,
        ':company_id' => $company_id
    ]);
        if ($result) {
            return ['success' => true, 'message' => 'Coupon added successfully.'];
        } else {
            return ['success' => false, 'message' => 'Error adding coupon.'];
        }
    }
}

function add_admin_coupon($code, $discount, $usage_limit, $expire_date, $company_id) {
    global $db;
    $id = uuid();
    $code = trim($code ?? '');
    $discount = trim($discount ?? '');
    $usage_limit = trim($usage_limit ?? '');
    $expire_date = trim($expire_date ?? '');
    $company_id = trim($company_id ?? '');

    if (!$code || !$discount || !$usage_limit || !$expire_date) {
         return ['success' => false, 'message' => 'All fields are required.'];
    }

    $stmt = $db->prepare("SELECT id FROM `Coupons` WHERE code = :code");
    $stmt->execute([':code' => $code]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'message' => 'Coupon code already exists.'];
    }
    else {
        $stmt = $db->prepare("INSERT INTO `Coupons` (id, code, discount, usage_limit, expire_date, company_id) VALUES (:id, :code, :discount, :usage_limit, :expire_date, :company_id)");
        $result = $stmt->execute([
        ':id' => $id,
        ':code' => $code,
        ':discount' => $discount,
        ':usage_limit' => $usage_limit,
        ':expire_date' => $expire_date,
        ':company_id' => $company_id,
    ]);
        if ($result) {
            return ['success' => true, 'message' => 'Coupon added successfully.'];
        } else {
            return ['success' => false, 'message' => 'Error adding coupon.'];
        }
    }
}
function get_company_coupons($company_id) {
    global $db;
    $stmt = $db->prepare('SELECT * FROM Coupons WHERE company_id = :company_id ORDER BY created_at DESC');
    $stmt->bindValue(':company_id', $company_id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function get_company_coupon($coupon_id) {
    global $db;
    $stmt = $db->prepare('SELECT * FROM Coupons WHERE id = :id LIMIT 1');
    $stmt->bindValue(':id', $coupon_id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function get_coupons($id){
    global $db;
    $stmt = $db->prepare('SELECT *
    FROM Coupons
    WHERE company_id = :id
    ORDER BY code ASC');
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function edit_coupon($coupon_id, $code, $discount, $usage_limit, $expire_date, $company_id) {
    global $db;

    $coupon_id = trim($coupon_id ?? '');
    $code = trim($code ?? '');
    $discount = trim($discount ?? '');
    $usage_limit = trim($usage_limit ?? '');
    $expire_date = trim($expire_date ?? '');
    $company_id = trim($company_id ?? '');

    if (!$coupon_id || !$code || !$discount || !$usage_limit || !$expire_date || !$company_id) {
         return ['success' => false, 'message' => 'All fields are required.'];
    }

    $stmt = $db->prepare("UPDATE Coupons 
        SET code = :code,
            discount = :discount,
            usage_limit = :usage_limit,
            expire_date = :expire_date
        WHERE id = :id AND company_id = :company_id");

    $stmt->bindValue(':code', $code, PDO::PARAM_STR);
    $stmt->bindValue(':discount', $discount, PDO::PARAM_INT);
    $stmt->bindValue(':usage_limit', $usage_limit, PDO::PARAM_INT);
    $stmt->bindValue(':expire_date', $expire_date, PDO::PARAM_STR);
    $stmt->bindValue(':id', $coupon_id, PDO::PARAM_STR);
    $stmt->bindValue(':company_id', $company_id, PDO::PARAM_STR);

    $result = $stmt->execute();

    if ($result) {
        return ['success' => true, 'message' => 'Kupon başarıyla güncellendi.'];
    } else {
        return ['success' => false, 'message' => 'Kupon güncellenirken bir hata oluştu.'];
    }
}

function cancel_coupon($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM Coupons WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $result = $stmt->execute();
    if ($result) {
        return ['success' => true, 'message' => 'Kupon başarıyla kaldırılıd.'];
    } else {
        return ['success' => false, 'message' => 'Kupon kaldırılırken bir hata oluştu.'];
    }
}

function get_company_tickets($company_id) {
    global $db;
    $stmt = $db->prepare('SELECT t.id, tr.departure_city, tr.destination_city, tr.departure_time, t.status, tr.capacity, u.full_name
        FROM Tickets t
        JOIN Trips tr ON t.trip_id = tr.id
        JOIN User u ON t.user_id = u.id
        WHERE tr.company_id = :company_id
        ORDER BY t.created_at DESC');
    $stmt->bindValue(':company_id', $company_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cancel_ticket($id) {
    global $db;
    $db->beginTransaction();

    try {
        $stmt_update = $db->prepare(
            "UPDATE Tickets SET status = 'cancelled' WHERE id = :id AND status = 'active'"
        );
        $stmt_update->bindValue(':id', $id);
        $stmt_update->execute();

        if ($stmt_update->rowCount() > 0) {

            $stmt_info = $db->prepare("SELECT user_id, total_price FROM Tickets WHERE id = :id");
            $stmt_info->bindValue(':id', $id);
            $stmt_info->execute();
            $ticket = $stmt_info->fetch(PDO::FETCH_ASSOC);


            $stmt_refund = $db->prepare('UPDATE User SET balance = balance + :price WHERE id = :user_id');
            $stmt_refund->bindValue(':price', $ticket['total_price']);
            $stmt_refund->bindValue(':user_id', $ticket['user_id']);
            $stmt_refund->execute();


            $stmt_delete = $db->prepare("DELETE FROM Booked_Seats WHERE ticket_id = :ticket_id");
            $stmt_delete->bindValue(':ticket_id', $id);
            $stmt_delete->execute();


            $db->commit();
            return ['success' => true, 'message' => 'Bilet başarıyla iptal edildi ve ücret iadesi yapıldı.'];
        } else {
            $db->rollBack();
            return ['success' => false, 'message' => 'Bilet iptal edilemedi. Bilet bulunamadı veya zaten aktif değildi.'];
        }
    } catch (Exception $e) {
        $db->rollBack();
        error_log($e->getMessage());
        return ['success' => false, 'message' => 'İşlem sırasında bir veritabanı hatası oluştu.'];
    }
}
function delete_ticket_history($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM Tickets WHERE id = :id AND status = 'cancelled'");
    $stmt->bindValue(':id', $id);
    $result = $stmt->execute();
    if ($result) {
        return ['success' => true, 'message' => 'Bilet geçmişi başarıyla silindi.'];
    } else {
        return ['success' => false, 'message' => 'Bilet geçmişi silinirken bir hata oluştu.'];
    }
}
function available_seats($trip_id) {
    global $db;
    $stmt = $db->prepare('SELECT COUNT(*) FROM Tickets WHERE trip_id = :trip_id AND status = "active"');
    $stmt->execute([':trip_id' => $trip_id]);
    $booked = $stmt->fetchColumn();
    return $booked;
}

function get_companies() {
    global $db;
    $stmt = $db->prepare('SELECT b.id, b.name, u.full_name, u.email, u.company_id
        FROM Bus_Company b
        JOIN User u ON b.id = u.company_id
        ORDER BY b.created_at DESC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function add_company($name, $target_path, $full_name, $email, $password) {
    global $db;
    $id_b = uuid();
    $name = trim($name ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $target_path = trim($target_path ?? '');
    if (!$name || !$target_path || !$full_name || !$email || !$password) {
         return ['success' => false, 'message' => 'All fields are required.'];
    }
    $stmt = $db->prepare('INSERT INTO Bus_Company (id, name, logo_path) VALUES (:id, :name, :logo_path)');
    $result = $stmt->execute([
        ':id' => $id_b,
        ':name' => $name,
        ':logo_path' => $target_path]);
    if ($result) {
        $result = registerUser($full_name, $email, $password, $id_b);
        if ($result['success']) {
            return ['success'=> true,'message'=> 'Succssfully added company and '];
        } else {
            return ['success'=> false,'message'=> 'Company added but user registration failed: ' . $result['message']];
        }
    }

}
?>