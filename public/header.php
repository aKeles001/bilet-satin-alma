<?php
require_once __DIR__ . '/../src/auth.php';
?>

<!DOCTYPE html>
<html lang="tr">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="view/css/style.css" rel="stylesheet">
    <title>Bilet Satın Alma Platformu</title>
  </head>
  <body class="bg-light">
    <nav id="mainNavbar" class="navbar navbar-expand-lg bg-dark navbar-dark py-3">
      <div class="container">
        <div class="d-flex w-100 align-items-center justify-content-between flex-wrap">
          <a class="navbar-brand fw-bold" href="index.php">🚌 Bilet Platformu</a>
        </div> 
      </div>
      <div class="container">
          <div class="flex-grow-1 d-flex justify-content-center order-3 order-lg-2 my-2 my-lg-0">
            <form class="d-flex w-100" style="max-width:500px;" method="GET" action="searchresults.php">
              <input class="form-control me-2" type="text" name="from" placeholder="Kalkış" required>
              <input class="form-control me-2" type="text" name="to" placeholder="Varış" required>
              <input class="form-control me-2" type="date" name="date" required>
              <button class="btn btn-light" type="submit">Ara</button>
            </form>
          </div>
      </div>,
      <div class="container">
          <div class="order-2 order-lg-3">
            <button
              class="navbar-toggler ms-2"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navmenu"
            >
              <span class="navbar-toggler-icon"></span>
            </button>
            </div>
            <div class="collapse navbar-collapse" id="navmenu">
              <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isset($_SESSION['user_id'])): ?>
                  <?php if (isAdmin()): ?>
                    <li class="nav-item me-3"><a class="nav-link" href="admin_panel.php">Admin Paneli</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış Yap</a></li>
                  <?php elseif (isCompany()): ?>
                    <li class="nav-item me-3"><a class="nav-link" href="company_panel.php">Firma Paneli</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış Yap</a></li>
                  <?php else: ?>
                    <li class="nav-item me-3"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış Yap</a></li>
                  <?php endif; ?>
                <?php else: ?>
                  <li class="nav-item me-3"><a class="nav-link" href="login.php">Giriş Yap</a></li>
                  <li class="nav-item"><a class="nav-link" href="register.php">Kayıt Ol</a></li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </body>
</html>