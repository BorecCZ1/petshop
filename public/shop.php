<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mazlíčci — E-shop pro mazlíčky</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Fraunces:opsz,wght@9..144,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/shop.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="#home" class="logo" data-nav="home">
            <span class="logo-icon" aria-hidden="true">🐾</span>
            Mazlíčci
        </a>
        <nav class="nav-actions">
            <a href="#home" class="nav-link" data-nav="home">Domů</a>
            <a href="#home" class="nav-link" data-nav="shop">Katalog</a>
            <button type="button" class="btn-cart" id="btn-cart" aria-label="Košík">
                Košík
                <span class="cart-badge" id="cart-badge" data-count="0"></span>
            </button>
        </nav>
    </div>
</header>

<main id="app">
    <div class="loading">Načítám obchod…</div>
</main>

<footer class="site-footer">
    <p>Mazlíčci · testovací e-shop · <a href="admin.php">Administrace objednávek</a> · <a href="playground.php">API playground</a></p>
</footer>

<div id="toast" class="toast" role="status" aria-live="polite"></div>

<script src="js/shop.js"></script>
</body>
</html>
