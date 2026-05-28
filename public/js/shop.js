(function () {
    'use strict';

    var API = 'index.php';

    var CATEGORY_META = {
        krmivo: { icon: '🍖', label: 'Krmivo' },
        hracky: { icon: '🎾', label: 'Hračky' },
        doplnky: { icon: '🛏️', label: 'Doplňky' }
    };

    var DEFAULT_ICON = '🐾';
    var MAX_QTY = 999;

    function maxQtyForStock(stock) {
        return Math.min(MAX_QTY, Math.max(0, stock || 0));
    }

    function validateQty(qty) {
        if (isNaN(qty) || qty < 1) {
            return 'Quantity must be at least 1';
        }
        if (qty > MAX_QTY) {
            return 'Quantity cannot be over 999';
        }
        return null;
    }

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatPrice(amount) {
        return Number(amount).toLocaleString('cs-CZ') + ' Kč';
    }

    function productIcon(product) {
        var cat = product.category_id || '';
        return (CATEGORY_META[cat] && CATEGORY_META[cat].icon) || DEFAULT_ICON;
    }

    function api(action, params) {
        var qs = new URLSearchParams();
        qs.set('action', action);
        if (params) {
            Object.keys(params).forEach(function (key) {
                if (params[key] !== undefined && params[key] !== null && params[key] !== '') {
                    qs.set(key, params[key]);
                }
            });
        }
        return fetch(API + '?' + qs.toString(), { credentials: 'same-origin' })
            .then(function (res) {
                return res.text().then(function (text) {
                    var data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid server response');
                    }
                    if (!res.ok || data.error) {
                        throw new Error(data.error || 'Server error');
                    }
                    return data;
                });
            });
    }

    function showToast(message, isError) {
        var el = document.getElementById('toast');
        if (!el) return;
        el.textContent = message;
        el.className = 'toast is-visible' + (isError ? ' is-error' : '');
        clearTimeout(showToast._timer);
        showToast._timer = setTimeout(function () {
            el.classList.remove('is-visible');
        }, 3200);
    }

    function setCartBadge(count) {
        var badge = document.getElementById('cart-badge');
        if (!badge) return;
        var n = count || 0;
        badge.textContent = n > 0 ? String(n) : '';
        badge.setAttribute('data-count', String(n));
    }

    function cartItemCount(cart) {
        if (!cart || !cart.items) return 0;
        return cart.items.reduce(function (sum, item) {
            return sum + (item.qty || 0);
        }, 0);
    }

    function updateCartBadgeFromApi() {
        return api('cartView').then(function (cart) {
            setCartBadge(cartItemCount(cart));
        }).catch(function () {
            setCartBadge(0);
        });
    }

    function parseRoute() {
        var hash = (location.hash || '#home').replace(/^#/, '');
        var parts = hash.split('/').filter(Boolean);
        return {
            page: parts[0] || 'home',
            id: parts[1] || null
        };
    }

    function navigate(page, id) {
        var hash = page === 'home' ? '#home' : '#' + page + (id ? '/' + encodeURIComponent(id) : '');
        if (location.hash === hash) {
            render();
        } else {
            location.hash = hash;
        }
    }

    function setActiveNav(page) {
        document.querySelectorAll('[data-nav]').forEach(function (el) {
            el.classList.toggle('is-active', el.getAttribute('data-nav') === page);
        });
    }

    function renderLoading() {
        document.getElementById('app').innerHTML = '<div class="loading">Načítám…</div>';
    }

    function renderHome() {
        setActiveNav('home');
        renderLoading();
        api('categories').then(function (categories) {
            var cards = categories.map(function (cat) {
                var meta = CATEGORY_META[cat.id] || { icon: DEFAULT_ICON, label: cat.name };
                return (
                    '<article class="card card-clickable" data-category="' + escapeHtml(cat.id) + '">' +
                    '<div class="card-media cat-' + escapeHtml(cat.id) + '">' + meta.icon + '</div>' +
                    '<div class="card-body">' +
                    '<h3>' + escapeHtml(cat.name) + '</h3>' +
                    '<p>' + escapeHtml(cat.description) + '</p>' +
                    '</div></article>'
                );
            }).join('');

            document.getElementById('app').innerHTML =
                '<section class="hero">' +
                '<h1>Vše pro vaše mazlíčky</h1>' +
                '<p>Krmivo, hračky a doplňky na jednom místě. Vyberte kategorii a přidejte do košíku.</p>' +
                '</section>' +
                '<h2 class="section-title">Kategorie</h2>' +
                '<div class="grid-categories">' + cards + '</div>';

            document.querySelectorAll('[data-category]').forEach(function (el) {
                el.addEventListener('click', function () {
                    navigate('category', el.getAttribute('data-category'));
                });
            });
        }).catch(handleRenderError);
    }

    function renderCategory(categoryId) {
        setActiveNav('shop');
        renderLoading();
        api('categories').then(function (categories) {
            var cat = categories.filter(function (c) { return c.id === categoryId; })[0];
            var catName = cat ? cat.name : categoryId;
            return api('productList', { category: categoryId }).then(function (products) {
                var meta = CATEGORY_META[categoryId] || { icon: DEFAULT_ICON };
                var cards = products.length ? products.map(function (p) {
                    var inStock = p.stock > 0;
                    return (
                        '<article class="card card-clickable" data-product="' + escapeHtml(p.id) + '">' +
                        '<div class="card-media cat-' + escapeHtml(categoryId) + '">' + productIcon(p) + '</div>' +
                        '<div class="card-body">' +
                        '<h3>' + escapeHtml(p.name) + '</h3>' +
                        '<p>' + (inStock ? 'Skladem: ' + p.stock + ' ks' : 'Vyprodáno') + '</p>' +
                        '<div class="card-footer">' +
                        '<span class="price">' + formatPrice(p.price) + '</span>' +
                        '<span class="badge ' + (inStock ? 'badge-in' : 'badge-out') + '">' +
                        (inStock ? 'Skladem' : 'Vyprodáno') + '</span>' +
                        '</div></div></article>'
                    );
                }).join('') : '<div class="empty-state"><p>V této kategorii zatím nic není.</p></div>';

                document.getElementById('app').innerHTML =
                    '<nav class="breadcrumb">' +
                    '<a href="#home" data-nav-home>Domů</a><span class="sep">/</span>' +
                    '<span>' + escapeHtml(catName) + '</span></nav>' +
                    '<h2 class="section-title">' + escapeHtml(catName) + '</h2>' +
                    '<div class="grid-products">' + cards + '</div>';

                document.querySelectorAll('[data-product]').forEach(function (el) {
                    el.addEventListener('click', function () {
                        navigate('product', el.getAttribute('data-product'));
                    });
                });
                var homeLink = document.querySelector('[data-nav-home]');
                if (homeLink) {
                    homeLink.addEventListener('click', function (e) {
                        e.preventDefault();
                        navigate('home');
                    });
                }
            });
        }).catch(handleRenderError);
    }

    function renderProduct(productId) {
        setActiveNav('shop');
        renderLoading();
        api('productDetail', { id: productId }).then(function (p) {
            var inStock = p.stock > 0;
            var catLabel = (CATEGORY_META[p.category_id] && CATEGORY_META[p.category_id].label) || p.category_id;

            document.getElementById('app').innerHTML =
                '<nav class="breadcrumb">' +
                '<a href="#home" data-nav-home>Domů</a><span class="sep">/</span>' +
                '<a href="#category/' + encodeURIComponent(p.category_id) + '" data-nav-cat>' + escapeHtml(catLabel) + '</a>' +
                '<span class="sep">/</span><span>' + escapeHtml(p.name) + '</span></nav>' +
                '<div class="product-detail">' +
                '<div class="product-detail-visual">' + productIcon(p) + '</div>' +
                '<div>' +
                '<h1>' + escapeHtml(p.name) + '</h1>' +
                '<p class="price" style="font-size:1.5rem;margin-bottom:0.75rem">' + formatPrice(p.price) + '</p>' +
                '<span class="badge ' + (inStock ? 'badge-in' : 'badge-out') + '">' +
                (inStock ? 'Skladem (' + p.stock + ' ks)' : 'Vyprodáno') + '</span>' +
                '<p class="desc">' + escapeHtml(p.description) + '</p>' +
                '<div class="qty-row">' +
                '<label for="qty">Množství</label>' +
                '<input type="number" id="qty" min="1" max="' + maxQtyForStock(p.stock) + '" value="1" ' + (inStock ? '' : 'disabled') + '>' +
                '</div>' +
                '<button type="button" class="btn btn-primary" id="btn-add" ' + (inStock ? '' : 'disabled') + '>Přidat do košíku</button> ' +
                '<button type="button" class="btn btn-secondary" id="btn-back">Zpět</button>' +
                '</div></div>';

            document.getElementById('btn-add').addEventListener('click', function () {
                var qty = parseInt(document.getElementById('qty').value, 10);
                var qtyErr = validateQty(qty);
                if (qtyErr) {
                    showToast(qtyErr, true);
                    return;
                }
                if (qty > p.stock) {
                    showToast('Na skladě je jen ' + p.stock + ' ks', true);
                    return;
                }
                api('cartAdd', { id: productId, qty: qty }).then(function (cart) {
                    setCartBadge(cartItemCount(cart));
                    showToast('Přidáno do košíku');
                }).catch(function (err) {
                    showToast(err.message, true);
                });
            });
            document.getElementById('btn-back').addEventListener('click', function () {
                navigate('category', p.category_id);
            });
            var homeLink = document.querySelector('[data-nav-home]');
            if (homeLink) {
                homeLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    navigate('home');
                });
            }
            var catLink = document.querySelector('[data-nav-cat]');
            if (catLink) {
                catLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    navigate('category', p.category_id);
                });
            }
        }).catch(handleRenderError);
    }

    function renderCart() {
        setActiveNav('cart');
        renderLoading();
        api('cartView').then(function (cart) {
            if (!cart.items || !cart.items.length) {
                document.getElementById('app').innerHTML =
                    '<div class="empty-state">' +
                    '<div class="icon">🛒</div>' +
                    '<h2>Košík je prázdný</h2>' +
                    '<p>Přidejte něco hezkého pro svého mazlíčka.</p>' +
                    '<a href="#home" class="btn btn-primary">Prohlédnout katalog</a></div>';
                setCartBadge(0);
                return;
            }

            setCartBadge(cartItemCount(cart));

            var itemsHtml = cart.items.map(function (item) {
                return (
                    '<div class="cart-item" data-pid="' + escapeHtml(item.productId) + '">' +
                    '<div class="cart-item-icon">' + DEFAULT_ICON + '</div>' +
                    '<div><h4>' + escapeHtml(item.name) + '</h4>' +
                    '<div class="meta">' + formatPrice(item.price) + ' / ks · řádek ' + formatPrice(item.lineTotal) + '</div></div>' +
                    '<div class="cart-item-actions">' +
                    '<input type="number" min="1" max="' + MAX_QTY + '" value="' + item.qty + '" data-qty-input aria-label="Množství">' +
                    '<button type="button" class="btn-link" data-remove>Odebrat</button></div></div>'
                );
            }).join('');

            document.getElementById('app').innerHTML =
                '<h2 class="section-title">Košík</h2>' +
                '<div class="cart-layout">' +
                '<div class="cart-items">' + itemsHtml + '</div>' +
                '<aside class="summary-panel">' +
                '<h3>Shrnutí</h3>' +
                '<div id="summary-lines">' +
                '<div class="summary-row"><span>Mezisoučet</span><span id="sum-sub">' + formatPrice(cart.subtotal) + '</span></div>' +
                '</div>' +
                '<div class="coupon-box">' +
                '<label for="coupon">Slevový kód</label>' +
                '<div class="row">' +
                '<input type="text" id="coupon" placeholder="např. MAZLICEK50">' +
                '<button type="button" class="btn btn-secondary" id="btn-coupon">Použít</button></div>' +
                '<p class="coupon-hint">MAZLICEK50 (−50 Kč) · SLEVA10 (−10 %)</p></div>' +
                '<form class="checkout-form" id="checkout-form">' +
                '<label for="cust-name">Jméno</label>' +
                '<input type="text" id="cust-name" required value="">' +
                '<label for="cust-email">E-mail</label>' +
                '<input type="email" id="cust-email" required value="">' +
                '<button type="submit" class="btn btn-accent">Objednat</button></form></aside></div>';

            bindCartEvents(cart);
            refreshSummary('');
        }).catch(handleRenderError);
    }

    function refreshSummary(couponCode) {
        api('cartPreview', { coupon: couponCode }).then(function (data) {
            var lines = document.getElementById('summary-lines');
            if (!lines) return;
            var html =
                '<div class="summary-row"><span>Mezisoučet</span><span>' + formatPrice(data.subtotal) + '</span></div>';
            if (data.discount > 0) {
                html += '<div class="summary-row discount"><span>Sleva' +
                    (data.coupon_code ? ' (' + escapeHtml(data.coupon_code) + ')' : '') +
                    '</span><span>−' + formatPrice(data.discount) + '</span></div>';
            }
            html += '<div class="summary-row total"><span>Celkem</span><span>' + formatPrice(data.total) + '</span></div>';
            lines.innerHTML = html;
        }).catch(function (err) {
            showToast(err.message, true);
        });
    }

    function bindCartEvents(cart) {
        document.querySelectorAll('.cart-item').forEach(function (row) {
            var pid = row.getAttribute('data-pid');
            row.querySelector('[data-remove]').addEventListener('click', function () {
                api('cartRemove', { id: pid }).then(function () {
                    renderCart();
                }).catch(function (err) {
                    showToast(err.message, true);
                });
            });
            var input = row.querySelector('[data-qty-input]');
            input.addEventListener('change', function () {
                var qty = parseInt(input.value, 10);
                var qtyErr = validateQty(qty);
                if (qtyErr) {
                    showToast(qtyErr, true);
                    renderCart();
                    return;
                }
                api('cartUpdate', { id: pid, qty: qty }).then(function (c) {
                    setCartBadge(cartItemCount(c));
                    renderCart();
                }).catch(function (err) {
                    showToast(err.message, true);
                });
            });
        });

        document.getElementById('btn-coupon').addEventListener('click', function () {
            refreshSummary(document.getElementById('coupon').value.trim());
        });

        document.getElementById('checkout-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var name = document.getElementById('cust-name').value.trim();
            var email = document.getElementById('cust-email').value.trim();
            var coupon = document.getElementById('coupon').value.trim();
            api('placeOrder', { name: name, email: email, coupon: coupon }).then(function (res) {
                setCartBadge(0);
                navigate('success', res.order_number);
            }).catch(function (err) {
                showToast(err.message, true);
            });
        });
    }

    function renderSuccess(orderNumber) {
        setActiveNav('');
        document.getElementById('app').innerHTML =
            '<div class="success-box">' +
            '<div class="icon">✓</div>' +
            '<h1>Děkujeme za objednávku!</h1>' +
            '<p>Vaše objednávka byla přijata. Uložte si číslo objednávky:</p>' +
            '<div class="order-id">' + escapeHtml(orderNumber) + '</div>' +
            '<p>Celková částka a detaily najdete v administraci.</p>' +
            '<a href="#home" class="btn btn-primary">Zpět do obchodu</a></div>';
    }

    function handleRenderError(err) {
        document.getElementById('app').innerHTML =
            '<div class="empty-state"><h2>Něco se pokazilo</h2><p>' + escapeHtml(err.message) + '</p>' +
            '<a href="#home" class="btn btn-secondary">Domů</a></div>';
    }

    function render() {
        var route = parseRoute();
        switch (route.page) {
            case 'home':
                renderHome();
                break;
            case 'category':
                renderCategory(route.id);
                break;
            case 'product':
                renderProduct(route.id);
                break;
            case 'cart':
                renderCart();
                break;
            case 'success':
                renderSuccess(route.id);
                break;
            default:
                renderHome();
        }
    }

    document.getElementById('btn-cart').addEventListener('click', function () {
        navigate('cart');
    });

    document.querySelectorAll('[data-nav]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var nav = el.getAttribute('data-nav');
            if (nav === 'home') {
                e.preventDefault();
                navigate('home');
            }
        });
    });

    window.addEventListener('hashchange', render);

    updateCartBadgeFromApi().then(render);
})();
