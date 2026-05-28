<?php

$base = 'index.php';
function apiUrl($params)
{
    global $base;
    return $base . '?' . http_build_query($params);
}

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title>Mazlíčci – API playground</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        pre {
            background: #f5f5f5;
            padding: 1rem;
            overflow: auto;
            max-height: 400px;
        }

        a, button {
            margin-right: 0.5rem;
        }

        label {
            display: inline-block;
            min-width: 80px;
        }
    </style>
</head>
<body>
<h1>API playground</h1>
<p>Výsledek se načte níže (fetch). Nebo otevři odkaz v novém tabu — uvidíš čisté JSON.</p>

<section>
    <h2>Katalog</h2>
    <p>
        <a href="#" data-url="<?= htmlspecialchars(apiUrl(array('action' => 'categories'))) ?>">Kategorie</a>
        <a href="#" data-url="<?= htmlspecialchars(apiUrl(array('action' => 'productList'))) ?>">Všechny produkty</a>
        <a href="#"
           data-url="<?= htmlspecialchars(apiUrl(array('action' => 'productList', 'category' => 'krmivo'))) ?>">Produkty:
            krmivo</a>
        <a href="#"
           data-url="<?= htmlspecialchars(apiUrl(array('action' => 'productList', 'category' => 'hracky'))) ?>">Produkty:
            hračky</a>
        <a href="#"
           data-url="<?= htmlspecialchars(apiUrl(array('action' => 'productDetail', 'id' => 'miss-piskaci-micek'))) ?>">Detail:
            pískací míček</a>
    </p>
</section>

<section>
    <h2>Košík</h2>
    <form id="cartAdd">
        <label>ID produktu</label>
        <input name="id" value="miss-piskaci-micek" size="30">
        <label>Množství</label>
        <input name="qty" type="number" value="1" min="1" max="999">
        <button type="submit">Přidat (cartAdd)</button>
    </form>
    <form id="cartUpdate" style="margin-top:0.5rem">
        <label>ID produktu</label>
        <input name="id" value="miss-piskaci-micek" size="30">
        <label>Množství</label>
        <input name="qty" type="number" value="2" min="1" max="999">
        <button type="submit">Změnit množství (cartUpdate)</button>
    </form>
    <p style="margin-top:1rem">
        <a href="#" data-url="<?= htmlspecialchars(apiUrl(array('action' => 'cartView'))) ?>">Zobrazit košík</a>
        <a href="#"
           data-url="<?= htmlspecialchars(apiUrl(array('action' => 'cartRemove', 'id' => 'miss-piskaci-micek'))) ?>">Odebrat
            míček</a>
    </p>
</section>

<section>
    <h2>Objednávka</h2>
    <form id="placeOrder">
        <label>Jméno</label>
        <input name="name" value="Jan Novák">
        <label>Email</label>
        <input name="email" value="jan@example.cz">
        <button type="submit">Odeslat objednávku (placeOrder)</button>
    </form>
    <p style="margin-top:1rem">
        <a href="#" data-url="<?= htmlspecialchars(apiUrl(array('action' => 'adminOrders'))) ?>">Admin: seznam objednávek</a>
    </p>
    <form id="orderDetail" style="margin-top:0.5rem">
        <label>Číslo objednávky</label>
        <input name="order" placeholder="ORD-20250522-ABC123" size="28">
        <button type="submit">Admin: detail (adminOrderDetail)</button>
    </form>
</section>

<pre id="out">Klikni na odkaz nebo odešli formulář…</pre>

<script>
    function show(url) {
        var out = document.getElementById('out');
        out.textContent = 'Načítám… ' + url;
        fetch(url, {credentials: 'same-origin'})
            .then(function (r) {
                return r.text();
            })
            .then(function (t) {
                try {
                    out.textContent = JSON.stringify(JSON.parse(t), null, 2);
                } catch (e) {
                    out.textContent = t;
                }
            })
            .catch(function (e) {
                out.textContent = 'Chyba: ' + e;
            });
    }

    document.querySelectorAll('a[data-url]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            show(a.getAttribute('data-url'));
        });
    });

    function bindForm(id, action) {
        document.getElementById(id).addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = new FormData(e.target);
            show('index.php?action=' + action + '&id=' + encodeURIComponent(fd.get('id')) + '&qty=' + encodeURIComponent(fd.get('qty')));
        });
    }

    document.getElementById('placeOrder').addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(e.target);
        show('index.php?action=placeOrder&name=' + encodeURIComponent(fd.get('name')) +
            '&email=' + encodeURIComponent(fd.get('email')));
    });
    document.getElementById('orderDetail').addEventListener('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(e.target);
        show('index.php?action=adminOrderDetail&order=' + encodeURIComponent(fd.get('order')));
    });

    bindForm('cartAdd', 'cartAdd');
    bindForm('cartUpdate', 'cartUpdate');
</script>
</body>
</html>