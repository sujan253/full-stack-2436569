// main.js - Gamestation
// Sujan

'use strict';

// hamburger nav toggle
function toggleNav() {
    document.getElementById('navLinks').classList.toggle('open');
}

// delete modal
function confirmDelete(id, title) {
    document.getElementById('delId').value = id;
    document.getElementById('delTitle').textContent = title;
    var m = document.getElementById('delModal');
    m.style.display = 'flex';
    requestAnimationFrame(function() { m.classList.add('show'); });
}

function closeModal() {
    var m = document.getElementById('delModal');
    if (!m) return;
    m.classList.remove('show');
    setTimeout(function() { m.style.display = 'none'; }, 220);
}

// close modal if you click the background
document.addEventListener('click', function(e) {
    var m = document.getElementById('delModal');
    if (m && e.target === m) closeModal();
});

// close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// rating slider - sync slider and number input
var slider = document.getElementById('rating_slider');
var ratingNum = document.getElementById('rating');
if (slider && ratingNum) {
    slider.addEventListener('input', function() {
        ratingNum.value = slider.value;
    });
    ratingNum.addEventListener('input', function() {
        var v = parseFloat(ratingNum.value);
        if (!isNaN(v) && v >= 0 && v <= 10) slider.value = v;
    });
}

// description char counter
var descArea = document.getElementById('description');
var descCount = document.getElementById('desc-count');
if (descArea && descCount) {
    function updateCount() { descCount.textContent = descArea.value.length; }
    descArea.addEventListener('input', updateCount);
    updateCount();
}

// navbar shadow once the page has scrolled
var navbar = document.querySelector('.navbar');
if (navbar) {
    var onScroll = function() {
        navbar.classList.toggle('scrolled', window.scrollY > 8);
    };
    document.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

// count up the hero stats instead of just showing the number
document.querySelectorAll('.stat-n').forEach(function(el) {
    var target = parseInt(el.textContent, 10);
    if (isNaN(target)) return;
    var start = null;
    var duration = 700;
    function step(ts) {
        if (!start) start = ts;
        var progress = Math.min((ts - start) / duration, 1);
        var eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(eased * target);
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
});

// flash message auto-remove after 4 seconds
var flash = document.getElementById('flashMsg');
if (flash) {
    setTimeout(function() {
        flash.style.transition = 'opacity 0.5s';
        flash.style.opacity = '0';
        setTimeout(function() { if (flash.parentNode) flash.remove(); }, 500);
    }, 4000);
}

// ---- AJAX Autocomplete ----

// MI-Linux serves this app from a /~2436569/games-site/ subfolder, but a
// local dev server runs it at the domain root - detect which one we're on.
var API_BASE = (window.location.hostname.indexOf('mi-linux.wlv.ac.uk') !== -1)
    ? '/~2436569/games-site/' : '/';

// basic debounce
function debounce(fn, ms) {
    var t;
    return function() {
        var args = arguments;
        clearTimeout(t);
        t = setTimeout(function() { fn.apply(null, args); }, ms);
    };
}

// escape html - prevent XSS in dropdown
function esc(s) {
    if (!s) return '';
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// setup autocomplete for a given input + dropdown + type
function setupAC(inputId, dropId, type, onSelect) {
    var input = document.getElementById(inputId);
    var drop  = document.getElementById(dropId);
    if (!input || !drop) return;

    var items = [];
    var activeIdx = -1;

    function show(suggestions) {
        items = suggestions;
        drop.innerHTML = '';
        if (!suggestions.length) { drop.classList.remove('open'); return; }

        suggestions.forEach(function(item, i) {
            var div = document.createElement('div');
            div.className = 'ac-item';
            div.innerHTML = '<div class="ac-label">' + esc(item.label) + '</div>' +
                (item.meta ? '<div class="ac-meta">' + esc(item.meta) + '</div>' : '');
            div.addEventListener('mousedown', function(e) {
                e.preventDefault();
                input.value = item.label;
                hide();
                if (onSelect) onSelect(item);
            });
            drop.appendChild(div);
        });
        activeIdx = -1;
        drop.classList.add('open');
    }

    function hide() {
        drop.classList.remove('open');
        activeIdx = -1;
    }

    function highlight(idx) {
        var els = drop.querySelectorAll('.ac-item');
        els.forEach(function(el, i) { el.classList.toggle('hi', i === idx); });
        if (idx >= 0 && els[idx]) els[idx].scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('keydown', function(e) {
        if (!drop.classList.contains('open')) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, items.length - 1);
            highlight(activeIdx);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, -1);
            highlight(activeIdx);
        } else if (e.key === 'Enter' && activeIdx >= 0) {
            e.preventDefault();
            input.value = items[activeIdx].label;
            hide();
            if (onSelect) onSelect(items[activeIdx]);
        } else if (e.key === 'Escape') {
            hide();
        }
    });

    input.addEventListener('blur', function() {
        setTimeout(hide, 150);
    });

    var fetch = debounce(function(q) {
        if (q.length < 1) { hide(); return; }
        var url = API_BASE + 'api/autocomplete.php?type=' + encodeURIComponent(type) + '&q=' + encodeURIComponent(q);
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    show(data.suggestions || []);
                } catch(e) { /* ignore */ }
            }
        };
        xhr.onerror = function() { /* fail silently */ };
        xhr.send();
    }, 260);

    input.addEventListener('input', function() {
        fetch(input.value.trim());
    });
}

// init autocompletes on add-game page
setupAC('title',     'title-ac', 'title',     function(item) {
    // if user selects a title from the search, optionally navigate to it
    // (only on search page, not add-game page)
    if (item.url && window.location.pathname.indexOf('search') !== -1) {
        window.location.href = item.url;
    }
});
setupAC('genre',     'genre-ac', 'genre');
setupAC('developer', 'dev-ac',   'developer');

// search page main bar
setupAC('mainSearch', 'search-ac', 'title', function(item) {
    if (item.url) window.location.href = item.url;
});
