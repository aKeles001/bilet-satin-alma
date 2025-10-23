<?php
session_start();
require_once '../src/tripsearch.php';
require_once '../src/helper.php';


$trip_id = $_POST['trip_id'] ?? null;
if (!$trip_id) die("Trip not found");

$trip = get_company_trip($trip_id);
$bookedSeats = getBookedSeats($trip_id);
$capacity = $trip['capacity'];
?>

<?php include 'header.php'; ?>
<body>
<div class="container mt-5 text-center">
    <h3><?= htmlspecialchars($trip['departure_city']) ?> → <?= htmlspecialchars($trip['destination_city']) ?></h3>
    <p><strong>Koltuk Seçiniz</strong></p>

    <div class="d-flex justify-content-center align-items-start gap-5">

        <div class="bus-container mx-auto" style="position: relative; width: 350px;">
            <img src="/bus_front.png" class="img-fluid" style="width: 100%;" alt="Bus">

            <div class="seat-grid" style="position: absolute; top: 200px; left: 50%; transform: translateX(-50%); width: 80%;">
                <?php
                $seatNumber = 1;
                for ($row = 1; $seatNumber <= $capacity; $row++): ?>
                    
                    <div class="d-flex justify-content-center mb-2">

                        <?php for ($i = 0; $i < 2 && $seatNumber <= $capacity; $i++, $seatNumber++): 
                            $isBooked = in_array($seatNumber, $bookedSeats);
                        ?>
                            <button 
                                class="seat btn <?= $isBooked ? 'btn-danger disabled' : 'btn-success' ?> mx-1"
                                style="width:55px"
                                data-seat="<?= $seatNumber ?>"
                                <?= $isBooked ? 'disabled' : '' ?>>
                                <?= $seatNumber ?>
                            </button>
                        <?php endfor; ?>

                        <div style="width: 40px;"></div>

                        <?php for ($i = 0; $i < 2 && $seatNumber <= $capacity; $i++, $seatNumber++):
                            $isBooked = in_array($seatNumber, $bookedSeats);
                        ?>
                            <button 
                                class="seat btn <?= $isBooked ? 'btn-danger disabled' : 'btn-success' ?> mx-1"
                                style="width:55px"
                                data-seat="<?= $seatNumber ?>"
                                <?= $isBooked ? 'disabled' : '' ?>>
                                <?= $seatNumber ?>
                            </button>
                        <?php endfor; ?>

                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <form id="bookingForm" action="book_ticket.php" method="POST" style="position :absolute; top: 200px; left: 65%; width:300px, text-align:left;">
            <input type="hidden" name="trip_id" value="<?= $trip_id ?>">
            <input type="hidden" name="selected_seat" id="selectedSeats">

            <label class="form-label">İndirim Kodu</label>
            <input type="text" name="code" class="form-control mb-3">

            <button type="submit" class="btn btn-primary w-100">Confirm Selection</button>
        </form>

    </div>
</div>

</body>

<script>
let selectedSeats = [];
const seats = document.querySelectorAll('.seat:not(.disabled)');

seats.forEach(btn => {
    btn.addEventListener('click', function () {
        const seat = this.dataset.seat;


        selectedSeats = [];
        seats.forEach(s => {
            s.classList.remove('btn-warning');
            s.classList.add('btn-success');
        });

        selectedSeats.push(seat);
        this.classList.remove('btn-success');
        this.classList.add('btn-warning');
        
        document.getElementById('selectedSeats').value = seat;
    });
});
</script>


<style>
.seat { border-radius: 8px; }
</style>

<?php include 'footer.php'; ?>