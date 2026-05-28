<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrace — Mazlíčci</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Fraunces:opsz,wght@9..144,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/shop.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="shop.php" class="logo">
            <span class="logo-icon" aria-hidden="true">🐾</span>
            Mazlíčci
        </a>
        <nav class="nav-actions">
            <a href="shop.php" class="nav-link">← Zpět do obchodu</a>
        </nav>
    </div>
</header>

<main style="max-width:1100px;margin:0 auto;padding:2rem 1.5rem 4rem">
    <h1 class="section-title">Administrace objednávek</h1>
    <p style="color:var(--muted);margin:-0.5rem 0 1.5rem">Přehled uložených objednávek z API.</p>

    <div id="admin-app">
        <div class="loading">Načítám objednávky…</div>
    </div>

    <div id="admin-detail" class="admin-detail" style="display:none"></div>
</main>

<footer class="site-footer">
    <p><a href="shop.php">Obchod</a> · <a href="playground.php">API playground</a></p>
</footer>

<div id="toast" class="toast" role="status"></div>

<script>
(function () {
    'use strict';

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatPrice(n) {
        return Number(n).toLocaleString('cs-CZ') + ' Kč';
    }

    function showToast(msg, err) {
        var t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'toast is-visible' + (err ? ' is-error' : '');
        setTimeout(function () { t.classList.remove('is-visible'); }, 3000);
    }

    function api(action, params) {
        var qs = new URLSearchParams();
        qs.set('action', action);
        if (params) {
            Object.keys(params).forEach(function (k) {
                if (params[k]) qs.set(k, params[k]);
            });
        }
        return fetch('index.php?' + qs, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.error) throw new Error(d.error);
                return d;
            });
    }

    function renderDetail(orderNumber) {
        api('adminOrderDetail', { order: orderNumber }).then(function (o) {
            var items = (o.items || []).map(function (it) {
                return '<li>' + escapeHtml(it.name) + ' × ' + it.qty + ' — ' + formatPrice(it.lineTotal) + '</li>';
            }).join('');
            document.getElementById('admin-detail').style.display = 'block';
            document.getElementById('admin-detail').innerHTML =
                '<h3>Detail: <span class="mono">' + escapeHtml(o.order_number) + '</span></h3>' +
                '<p><strong>Zákazník:</strong> ' + escapeHtml(o.customer.name) + ', ' + escapeHtml(o.customer.email) + '</p>' +
                '<p><strong>Vytvořeno:</strong> ' + escapeHtml(o.created_at) + '</p>' +
                '<ul>' + items + '</ul>' +
                '<p>Mezisoučet: ' + formatPrice(o.subtotal) +
                (o.discount > 0 ? ' · Sleva: −' + formatPrice(o.discount) + ' (' + escapeHtml(o.coupon_code || '') + ')' : '') +
                ' · <strong>Celkem: ' + formatPrice(o.total) + '</strong></p>' +
                '<button type="button" class="btn btn-secondary" id="close-detail">Zavřít detail</button>';
            document.getElementById('close-detail').onclick = function () {
                document.getElementById('admin-detail').style.display = 'none';
            };
        }).catch(function (e) {
            showToast(e.message, true);
        });
    }

    api('adminOrders').then(function (orders) {
        var app = document.getElementById('admin-app');
        if (!orders.length) {
            app.innerHTML = '<div class="empty-state"><p>Zatím žádné objednávky.</p><a href="shop.php" class="btn btn-primary">Do obchodu</a></div>';
            return;
        }
        var rows = orders.map(function (o) {
            return '<tr data-order="' + escapeHtml(o.order_number) + '">' +
                '<td class="mono">' + escapeHtml(o.order_number) + '</td>' +
                '<td>' + escapeHtml(o.customer.name) + '</td>' +
                '<td>' + escapeHtml(o.customer.email) + '</td>' +
                '<td>' + formatPrice(o.total) + '</td>' +
                '<td>' + (o.coupon_code ? escapeHtml(o.coupon_code) : '—') + '</td>' +
                '<td><button type="button" class="btn btn-secondary btn-sm" data-show-detail>Detail</button></td></tr>';
        }).join('');

        app.innerHTML =
            '<div class="admin-table-wrap"><table class="admin-table">' +
            '<thead><tr><th>Číslo</th><th>Jméno</th><th>E-mail</th><th>Celkem</th><th>Kupon</th><th></th></tr></thead>' +
            '<tbody>' + rows + '</tbody></table></div>';

        app.querySelectorAll('[data-show-detail]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tr = btn.closest('tr');
                renderDetail(tr.getAttribute('data-order'));
            });
        });
    }).catch(function (e) {
        document.getElementById('admin-app').innerHTML =
            '<div class="empty-state"><p>' + escapeHtml(e.message) + '</p></div>';
    });
})();
</script>
</body>
</html>
