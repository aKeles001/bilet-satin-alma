<?php
session_start();
require_once '../src/tripsearch.php';
require_once '../src/helper.php';


$trip_id = $_GET['trip_id'] ?? null;
if (!$trip_id) die("Trip not found");

$trip = get_company_trip($trip_id);
$bookedSeats = getBookedSeats($trip_id);
$capacity = $trip['capacity'];
?>

<?php include 'header.php'; ?>

<div class="container mt-5 text-center">
    <h3><?= htmlspecialchars($trip['departure_city']) ?> → <?= htmlspecialchars($trip['destination_city']) ?></h3>
    <p><strong>Koltuk Seçiniz</strong></p>

    <img src="images/bus_front.png" class="img-fluid mb-3" style="max-width:200px;" alt="Bus">

    <div class="bus-layout mx-auto p-4" style="width:320px; background:#f8f9fa; border-radius:10px;">
        <div class="seat-grid text-center">

        <?php
        $seatNumber = 1;
        for ($row = 1; $seatNumber <= $capacity; $row++): ?>
            
            <div class="d-flex justify-content-center mb-2">

                <?php for ($i = 0; $i < 2 && $seatNumber <= $capacity; $i++, $seatNumber++): 
                    $isBooked = in_array($seatNumber, $bookedSeats);
                ?>
                    <a class="seat btn <?= $isBooked ? 'btn-danger disabled' : 'btn-success' ?> mx-1"
                       style="width:55px"
                       href="<?= $isBooked ? '#' : 'book_ticket.php?trip_id='.$trip_id.'&seat='.$seatNumber ?>">
                        <?= $seatNumber ?>
                    </a>
                <?php endfor; ?>

                <div style="width: 40px;"></div>

                <?php for ($i = 0; $i < 2 && $seatNumber <= $capacity; $i++, $seatNumber++): 
                    $isBooked = in_array($seatNumber, $bookedSeats);
                ?>
                    <a class="seat btn <?= $isBooked ? 'btn-danger disabled' : 'btn-success' ?> mx-1"
                       style="width:55px"
                       href="<?= $isBooked ? '#' : 'book_ticket.php?trip_id='.$trip_id.'&seat='.$seatNumber ?>">
                        <?= $seatNumber ?>
                    </a>
                <?php endfor; ?>
            </div>

        <?php endfor; ?>
            <div class="mb-3">
                  <label class="form-label">İndirim Kodu</label>
                  <input type="text" name="code" class="form-control" >
              </div>
        </div>
    </div>
</div>

<style>
.seat { border-radius: 8px; }
</style>

<?php include 'footer.php'; ?>