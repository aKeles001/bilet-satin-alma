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
        return ['success' => false, 'message' => 'Tum Alanların Doldurulması Gereklidir.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Yanlış email formatı.'];
    }
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Şifre en az 6 karakterden oluşmalı.'];
    }
    return ['success' => true];
}

// Book ticket
function book_ticket($trip_id, $user_id, $booked_seat, $code = 0) {
    global $db;
    $stmt = $db->prepare('SELECT capacity, price, company_id, departure_time FROM Trips WHERE id = :trip_id');
    $stmt->execute([':trip_id' => $trip_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);
    $departure_raw = str_replace('T', ' ', $trip['departure_time']);
    $departure_timestamp = strtotime($departure_raw);

    if ($departure_timestamp < time()) {
        return ['success' => false, 'message' => 'Sefer gerçekleşti!'];
    }
    if (!$trip) {
        return ['success' => false, 'message' => 'Sefer bulunamadı.'];
    }
    $booked = available_seats($trip_id);
    if ($booked >= $trip['capacity']) {
        return ['success' => false, 'message' => 'Boş koltuk bulunamadı.'];
    }
    
    $stmt = $db->prepare('SELECT balance FROM User WHERE id = :user_id');
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($code)){
        if (!$user || $user['balance'] < $trip['price']) {
            return ['success' => false, 'message' => 'Bakiye yetersiz.'];
        }
        $stmt = $db->prepare('UPDATE User SET balance = balance - :price WHERE id = :user_id');
        $stmt->execute([':price' => $trip['price'], ':user_id' => $user_id]);}
        else {
        $stmt = $db->prepare('SELECT * 
            FROM Coupons
            WHERE code = :code AND (company_id = :company_id OR company_id IS NULL)');
        $stmt->execute([':code'=> $code, ':company_id' => $trip['company_id']]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($coupon) {
            $trip['price'] = max(0, $trip['price'] - $coupon['discount']);

            if (!$user || $user['balance'] < $trip['price']) {
                return ['success' => false, 'message' => 'Bakiye yetersiz.'];
            }

            $stmt = $db->prepare('UPDATE User SET balance = balance - :price WHERE id = :user_id');
            $stmt->execute([':price' => $trip['price'], ':user_id' => $user_id]);
            $stmt = $db->prepare('INSERT INTO User_Coupons (id, coupon_id, user_id) VALUES (:id, :coupon_id, :user_id)');
            $stmt->execute([':id'=> uuid(),
                            ':coupon_id' => $coupon['id'],
                            ':user_id'=> $user_id]);
            $stmt = $db->prepare('UPDATE Coupons 
                        SET usage_limit = usage_limit - :usage 
                        WHERE id = :coupon_id');
            $stmt->bindValue(':usage', 1);
            $stmt->bindValue(':coupon_id', $coupon['id']);
            $stmt->execute();
        } else {
            return ['success' => false, 'message' => 'Geçersiz kupon!'];
        }
}
    $ticket_id = uuid();
    $stmt = $db->prepare('INSERT INTO Tickets (id, trip_id, user_id, total_price) VALUES (:id, :trip_id, :user_id, :total_price)');
    $stmt->execute([
        ':id' => $ticket_id,
        ':trip_id' => $trip_id,
        ':user_id' => $user_id,
        ':total_price' => $trip['price']
    ]);
    $seat_number = $booked_seat;
    $booked_seat_id = uuid();
    $stmt = $db->prepare('INSERT INTO Booked_Seats (id, ticket_id, seat_number) VALUES (:id, :ticket_id, :seat_number)');
    $stmt->execute([
        ':id' => $booked_seat_id,
        ':ticket_id' => $ticket_id,
        ':seat_number' => $seat_number
    ]);
    return ['success' => true, 'message' => 'Bilet başarıyla alındı! Koltuk: ' . $seat_number, 'ticket_id' => $ticket_id, 'seat_number' => $seat_number];
}
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


function get_user_tickets($user_id) {
    global $db;
    $stmt = $db->prepare('SELECT t.id, tr.departure_city, tr.destination_city, tr.departure_time, t.status, b.name
        FROM Tickets t
        JOIN Trips tr ON t.trip_id = tr.id
        JOIN Bus_Company b ON tr.company_id = b.id
        WHERE t.user_id = :user_id
        ORDER BY t.created_at DESC');
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
         return ['success' => false, 'message' => 'Tum Alanların Doldurulması Gereklidir.'];
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
         return ['success' => false, 'message' => 'Tum Alanların Doldurulması Gereklidir.'];
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
         return ['success' => false, 'message' => 'Tum Alanların Doldurulması Gereklidir.'];
    }

    $stmt = $db->prepare("SELECT id FROM `Coupons` WHERE code = :code AND company_id = :company_id");
    $stmt->execute([':code' => $code, ':company_id' => $company_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'message' => 'Kupun kodu kullanımda.'];
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
            return ['success' => true, 'message' => 'Kupon başarıyla eklendi.'];
        } else {
            return ['success' => false, 'message' => 'Kupon eklenirken bir hata oluştu.'];
        }
    }
}

function add_admin_coupon($code, $discount, $usage_limit, $expire_date) {
    global $db;
    $id = uuid();
    $code = trim($code ?? '');
    $discount = trim($discount ?? '');
    $usage_limit = trim($usage_limit ?? '');
    $expire_date = trim($expire_date ?? '');
    $company_id = null;

    if (!$code || !$discount || !$usage_limit || !$expire_date) {
         return ['success' => false, 'message' => 'Tum Alanların Doldurulması Gereklidir.'];
    }

    $stmt = $db->prepare("SELECT id FROM `Coupons` WHERE code = :code");
    $stmt->execute([':code' => $code]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'message' => 'Kupon kodu kullanımda.'];
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
            return ['success' => true, 'message' => 'Kupın başarıyla eklendi.'];
        } else {
            return ['success' => false, 'message' => 'Kupon eklenirken bir hata oluştu.'];
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
function get_admin_coupons(){
    global $db;
    $stmt = $db->prepare('SELECT *
    FROM Coupons
    WHERE company_id  IS NULL
    ORDER BY code ASC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_coupons(){
    global $db;
    $stmt = $db->prepare('SELECT * FROM Coupons');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function edit_coupon($coupon_id, $code, $discount, $usage_limit, $expire_date, $company_id = null) {
    global $db;

    $coupon_id = trim($coupon_id ?? '');
    $code = trim($code ?? '');
    $discount = trim($discount ?? '');
    $usage_limit = trim($usage_limit ?? '');
    $expire_date = trim($expire_date ?? '');
    $company_id = trim($company_id ?? '');

    if (!$coupon_id || !$code || !$discount || !$usage_limit || !$expire_date || !$company_id) {
         return ['success' => false, 'message' => 'Tum Alanların Doldurulması Gereklidir.'];
    }
    if (isCompany() && $company_id) {
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
        }}
    elseif (isAdmin()) {
        $stmt = $db->prepare("UPDATE Coupons 
        SET code = :code,
            discount = :discount,
            usage_limit = :usage_limit,
            expire_date = :expire_date
        WHERE id = :id AND company_id IS NULL");

        $stmt->bindValue(':code', $code, PDO::PARAM_STR);
        $stmt->bindValue(':discount', $discount, PDO::PARAM_INT);
        $stmt->bindValue(':usage_limit', $usage_limit, PDO::PARAM_INT);
        $stmt->bindValue(':expire_date', $expire_date, PDO::PARAM_STR);
        $stmt->bindValue(':id', $coupon_id, PDO::PARAM_STR);

        $result = $stmt->execute();

        if ($result) {
            return ['success' => true, 'message' => 'Kupon başarıyla güncellendi.'];
        } else {
            return ['success' => false, 'message' => 'Kupon güncellenirken bir hata oluştu.'];}
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

function get_tickets(){
    global $db;
    $stmt = $db->prepare('SELECT T.*, Tr.departure_time 
    FROM Tickets T
    JOIN Trips Tr ON T.trip_id = Tr.id');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cancel_ticket($id) {
    global $db;
    $db->beginTransaction();

    try {
        $stmt_check = $db->prepare('SELECT T.departure_time
        FROM Trips T
        JOIN Tickets Tc ON T.id = Tc.trip_id
        WHERE Tc.id = :id');
        $stmt_check->bindValue('id', $id);
        $stmt_check->execute();
        $time = $stmt_check->fetch(PDO::FETCH_ASSOC);
        date_default_timezone_set('Europe/Istanbul');
        $departure_raw = $time['departure_time'];
        $departure_time = str_replace('T', ' ', $departure_raw);
        $departure_timestamp = strtotime($departure_time);
        $now = time();
        $diff = $departure_timestamp - $now;
        if ($diff <= 0) {
            return ['success' => false, 'message' => 'Bilet iptal edilemedi. Sefer gerçekleşti.'];
        }
        elseif ($diff <= 3600) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Bilet iptal edilemedi. Kalkış tarihinie 1 saatten az bulunuyor.'];
        }
        else {
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
    $stmt = $db->prepare('SELECT id, name FROM Bus_Company ORDER BY created_at DESC');
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
         return ['success' => false, 'message' => 'Tum Alanların Doldurulması Gereklidir.'];
    }
    $stmt = $db->prepare('INSERT INTO Bus_Company (id, name, logo_path) VALUES (:id, :name, :logo_path)');
    $result = $stmt->execute([
        ':id' => $id_b,
        ':name' => $name,
        ':logo_path' => $target_path]);
    if ($result) {
        $role = 'company';
        $result = registerUser($full_name, $email, $password, $id_b, $role);
        if ($result['success']) {
            return ['success'=> true,'message'=> 'Şirket başarıyla eklendi.'];
        } else {
            return ['success'=> false,'message'=> 'Şirket eklenirken bir hata oluştu.'];
        }
    }

}

function get_users1(){
    global $db;
    $stmt = $db->prepare('SELECT U.*, B.name 
    FROM User U
    JOIN Bus_Company B on U.company_id = B.id
    ORDER BY U.full_name ASC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_users() {
    global $db;
    $stmt = $db->prepare('SELECT U.*, B.name AS company_name
        FROM User U
        LEFT JOIN Bus_Company B ON U.company_id = B.id
        ORDER BY U.role ASC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function cancel_company($id) {
    global $db;

    try {
        $db->beginTransaction();

        $stmt = $db->prepare('UPDATE User SET company_id = NULL, role = "user" WHERE company_id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();


        $stmt = $db->prepare("DELETE FROM Bus_Company WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $db->commit();

        return ['success' => true, 'message' => 'Şirket başarıyla kaldırıldı. Şirket kullanıcıları güncellendi.'];

    } catch (Exception $e) {
        $db->rollBack();
        return ['success' => false, 'message' => 'Şirket kaldırılırken bir hata meydana geldi.'];
    }
}

function set_company_admin($company_id, $user_id) {
    global $db;
    try {
        $db->beginTransaction();
        $stmt = $db->prepare('UPDATE User SET company_id = :company_id, role = "company" WHERE id = :user_id');
        $stmt->bindValue(':company_id', $company_id);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
        $db->commit();

        return ['success'=> true, 'message'=> 'Şirket yöneticisi başarıyla atandı'];
    }
    catch (Exception $e) {
        $db->rollBack();
        return ['success'=> false, 'message' => 'Şirket yöneticisi atanırken bir hata meydana geldi.'];
    }
}
function getBookedSeats($trip_id) {
    global $db;
    $query = $db->prepare("SELECT S.seat_number 
    FROM Tickets T
    Join Booked_seats S ON T.id = S.ticket_id
    WHERE trip_id = :trip_id");
    $query->execute([$trip_id]);
    return $query->fetchAll(PDO::FETCH_COLUMN);
}

function updateCouponStatus($id){
    global $db;
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("DELETE FROM Coupons WHERE id = :id");
        $stmt->bindValue("id", $id);
        $stmt->execute();
        $db->commit();
        return ["success"=> true,"message"=> "Kupon suresi doldu"];

    }
    catch (Exception $e) {
        $db->rollBack();
        return ["success"=> false,"message"=> "Veri Tabanı Hatası"];
    }
}

function updateTicketStatus($id){
    global $db;
    try {
        $db->beginTransaction();
        $stmt = $db->prepare('UPDATE Tickets SET status = "expired" WHERE id = :id');
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $db->commit();
        return ['success'=> true,'message'=> 'Bilet süresi doldu'];
    }
    catch (Exception $e) {
        $db->rollBack();
        return ['success'=> false,'message'=> 'Veri Tabanı hatası'];
    }
}

function get_company_logo($id) {
    global $db;
    $stmt = $db->prepare('SELECT logo_path FROM Bus_Company WHERE id = :id');
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['logo_path'] : null;
}

?>