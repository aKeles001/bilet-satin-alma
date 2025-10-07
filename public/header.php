<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bilet Satın Alma Platformu</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="view/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container d-flex justify-content-between align-items-center">
    <!-- Brand -->
    <a class="navbar-brand fw-bold ms-auto" href="index.php">🚌 Bilet Platformu</a>
    

    <!-- Search Form -->
    <form class="d-flex w-50 mx-auto" method="GET" action="searchresults.php">
      <input class="form-control me-2" type="text" name="from" placeholder="Kalkış" required>
      <input class="form-control me-2" type="text" name="to" placeholder="Varış" required>
      <input class="form-control me-2" type="date" name="date" required>
      <button class="btn btn-light" type="submit">Ara</button>
    </form>

    <ul class="navbar-nav d-flex flex-row">
      <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item me-3"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      <?php else: ?>
        <li class="nav-item me-3"><a class="nav-link" href="login.php">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
      <?php endif; ?>
    </ul>

  </div>
</nav>

