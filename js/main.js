/* Trek Ways — front-end interactions */
(function() {
    'use strict';

    /* 1. Hero video: load the right source for the screen (mirrors Desire Adventure) */
    var v = document.querySelector('.tw-hero__video');
    if (v) {
        var mq = window.matchMedia('(min-width: 1024px)');
        var pick = function() {
            var want = mq.matches ? v.dataset.srcDesktop : v.dataset.srcMobile;
            if (!want) { // no video for this screen -> show poster
                if (v.getAttribute('src')) {
                    v.pause();
                    v.removeAttribute('src');
                    v.load();
                }
                v.style.display = 'none';
                return;
            }
            v.style.display = '';
            if (v.getAttribute('src') === want) return;
            v.src = want;
            v.load();
            var p = v.play();
            if (p) { p.catch(function() {}); }
        };
        pick();
        mq.addEventListener('change', pick);
    }

    /* 2. Mobile burger toggle (no hover on touch) */
    var burger = document.querySelector('.tw-burger');
    var nav = document.querySelector('.tw-nav');
    if (burger && nav) {
        burger.addEventListener('click', function() {
            var open = nav.classList.toggle('tw-open');
            burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }
    /* 3. Solid nav once scrolled past the hero (readable over light sections) */
    var dock = document.querySelector('.tw-navdock');
    if (dock) {
        var onScroll = function() {
            if (window.scrollY > 40) { dock.classList.add('tw-scrolled'); } else { dock.classList.remove('tw-scrolled'); }
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }
    /* 4. Mega menu: click-to-open panels + category switching (desktop panel / mobile accordion) */
    var megaOverlay = document.getElementById('tw-mega-overlay');
    var megaButtons = document.querySelectorAll('.tw-topbtn');
    var navDockEl = document.querySelector('.tw-navdock');

    function tw_closeAllMega() {
        megaButtons.forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.tw-mega').forEach(function(m) { m.classList.remove('open'); });
        if (megaOverlay) { megaOverlay.classList.remove('show'); }
        if (navDockEl) { navDockEl.classList.remove('tw-menu-open'); }
    }
    megaButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var slug = btn.getAttribute('data-menu');
            var panel = document.getElementById('tw-mega-' + slug);
            if (!panel) { return; }
            var wasOpen = panel.classList.contains('open');
            tw_closeAllMega();
            if (!wasOpen) {
                btn.classList.add('active');
                panel.classList.add('open');
                if (megaOverlay) { megaOverlay.classList.add('show'); }
            }
        });
    });
    if (megaOverlay) {
        megaOverlay.addEventListener('click', tw_closeAllMega);
    }
    document.querySelectorAll('.tw-mega__cat').forEach(function(cat) {
        cat.addEventListener('click', function() {
            var mega = cat.closest('.tw-mega');
            if (!mega) { return; }
            mega.querySelectorAll('.tw-mega__cat').forEach(function(c) { c.classList.remove('active'); });
            mega.querySelectorAll('.tw-mega__pane').forEach(function(p) { p.classList.remove('active'); });
            cat.classList.add('active');
            var pane = mega.querySelector('.tw-mega__pane[data-pane="' + cat.getAttribute('data-pane') + '"]');
            if (pane) { pane.classList.add('active'); }
            if (mqMobile.matches) { mega.classList.add('tw-mega--drilled'); }
        });
    });

    /* 5. Mobile drill-down: Back button returns to the category list */
    var mqMobile = window.matchMedia('(max-width:1100px)');
    document.querySelectorAll('.tw-mega__back').forEach(function(back) {
        back.addEventListener('click', function() {
            var mega = back.closest('.tw-mega');
            if (mega) { mega.classList.remove('tw-mega--drilled'); }
        });
    });
    /* 6. Featured Trips carousel */
    var twStage = document.getElementById('tw-stage');
    if (twStage) {
        var twCards = [].slice.call(twStage.querySelectorAll('.tw-tc'));
        var twN = twCards.length,
            twCur = Math.floor(twN / 2);
        var twDots = document.getElementById('tw-dots');
        twCards.forEach(function(c, i) {
            var d = document.createElement('button');
            d.className = 'tw-dot';
            d.setAttribute('aria-label', 'Go to trip ' + (i + 1));
            d.addEventListener('click', function() {
                twCur = i;
                twRender();
            });
            twDots.appendChild(d);
            c.addEventListener('click', function(e) {
                if (i !== twCur) {
                    e.preventDefault();
                    twCur = i;
                    twRender();
                }
            });
        });

        function twRender() {
            twCards.forEach(function(c, i) {
                var off = i - twCur;
                if (off > twN / 2) { off -= twN; }
                if (off < -twN / 2) { off += twN; }
                c.setAttribute('data-pos', Math.abs(off) <= 4 ? off : 'hide');
            });
            [].slice.call(twDots.children).forEach(function(d, i) {
                d.classList.toggle('on', i === twCur);
            });
        }
        document.getElementById('tw-next').addEventListener('click', function() {
            twCur = (twCur + 1) % twN;
            twRender();
        });
        document.getElementById('tw-prev').addEventListener('click', function() {
            twCur = (twCur - 1 + twN) % twN;
            twRender();
        });
        twRender();
    }
    /* 7. Destinations showcase */
    var dSlides = [].slice.call(document.querySelectorAll('.tw-dslide'));
    if (dSlides.length) {
        var dBgs = [].slice.call(document.querySelectorAll('.tw-dbg'));
        var dDots = [].slice.call(document.querySelectorAll('.tw-ddot'));
        var dN = dSlides.length,
            dCur = 0;

        function dGo(i) {
            dCur = (i + dN) % dN;
            dSlides.forEach(function(s, k) {
                s.classList.remove('anim');
                s.classList.toggle('on', k === dCur);
            });
            void dSlides[dCur].offsetWidth;
            dSlides[dCur].classList.add('anim');
            dBgs.forEach(function(b, k) { b.style.opacity = k === dCur ? 1 : 0; });
            dDots.forEach(function(d, k) { d.classList.toggle('on', k === dCur); });
            if (dDots[dCur]) { dDots[dCur].scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' }); }
        }
        dDots.forEach(function(d, i) { d.addEventListener('click', function() { dGo(i); }); });
        var dNext = document.getElementById('tw-dnext');
        var dPrev = document.getElementById('tw-dprev');
        if (dNext) { dNext.addEventListener('click', function() { dGo(dCur + 1); }); }
        if (dPrev) { dPrev.addEventListener('click', function() { dGo(dCur - 1); }); }
    }
    /* 8. Why Trek Ways — region tabs + scroll intro */
    var whySec = document.getElementById('tw-why');
    if (whySec) {
        var wSl = [].slice.call(whySec.querySelectorAll('.tw-pkslide'));
        var wTb = [].slice.call(whySec.querySelectorAll('.tw-pktab'));
        var wN = wSl.length,
            wCur = 0;

        function wGo(i) {
            wCur = (i + wN) % wN;
            wSl.forEach(function(s, k) {
                s.classList.remove('anim');
                s.classList.toggle('on', k === wCur);
            });
            void wSl[wCur].offsetWidth;
            wSl[wCur].classList.add('anim');
            wTb.forEach(function(t, k) { t.classList.toggle('on', k === wCur); });
            if (wTb[wCur]) { wTb[wCur].scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' }); }
        }
        wTb.forEach(function(t, i) { t.addEventListener('click', function() { wGo(i); }); });
        var wNext = document.getElementById('tw-pknext');
        var wPrev = document.getElementById('tw-pkprev');
        if (wNext) { wNext.addEventListener('click', function() { wGo(wCur + 1); }); }
        if (wPrev) { wPrev.addEventListener('click', function() { wGo(wCur - 1); }); }
        if ('IntersectionObserver' in window) {
            var wIo = new IntersectionObserver(function(es) {
                es.forEach(function(e) {
                    if (e.isIntersecting) {
                        whySec.classList.add('seen');
                        wIo.disconnect();
                    }
                });
            }, { threshold: 0.15 });
            wIo.observe(whySec);
        } else {
            whySec.classList.add('seen');
        }
    }
    /* 9. Region packages: region tabs, arrows, mobile progress, scroll intro, cloud pause */
    var pkSec = document.getElementById('tw-pkgs');
    if (pkSec) {
        var pkScroll = document.getElementById('tw-rscroll');
        var pkTabs = Array.prototype.slice.call(pkSec.querySelectorAll('.tw-rtab'));
        var pkPanels = Array.prototype.slice.call(pkSec.querySelectorAll('.tw-pgrid'));
        var pkPrev = document.getElementById('tw-rprev');
        var pkNext = document.getElementById('tw-rnext');
        var pkBar = document.getElementById('tw-pbar');
        var pkCur = 0;
        var pkBusy = false;

        var pkUpdateBar = function() {
            var g = pkPanels[pkCur];
            if (!pkBar || !g) { return; }
            var max = g.scrollWidth - g.clientWidth;
            var vis = g.scrollWidth ? g.clientWidth / g.scrollWidth : 1;
            var pos = max > 0 ? g.scrollLeft / max : 0;
            var w = Math.max(vis, 0.12);
            pkBar.style.width = (w + (1 - w) * pos) * 100 + '%';
        };

        var pkArrows = function() {
            if (!pkScroll || !pkPrev || !pkNext) { return; }
            var max = pkScroll.scrollWidth - pkScroll.clientWidth - 2;
            pkPrev.disabled = pkScroll.scrollLeft <= 2;
            pkNext.disabled = pkScroll.scrollLeft >= max;
        };

        var pkGo = function(i, focus) {
            if (i === pkCur || i < 0 || i >= pkTabs.length || pkBusy) { return; }
            pkBusy = true;
            var from = pkPanels[pkCur];
            var to = pkPanels[i];
            pkTabs.forEach(function(t, k) {
                t.setAttribute('aria-selected', k === i ? 'true' : 'false');
                t.tabIndex = k === i ? 0 : -1;
            });
            pkTabs[i].scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
            if (focus) { pkTabs[i].focus({ preventScroll: true }); }
            from.classList.add('swap');
            setTimeout(function() {
                from.hidden = true;
                from.classList.remove('swap', 'play');
                to.classList.remove('play');
                to.hidden = false;
                to.scrollLeft = 0;
                void to.offsetWidth; /* restart the card stagger */
                to.classList.add('play');
                pkCur = i;
                pkUpdateBar();
                pkBusy = false;
            }, 220);
        };

        pkTabs.forEach(function(t, i) {
            t.addEventListener('click', function() { pkGo(i, false); });
            t.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    pkGo(Math.min(i + 1, pkTabs.length - 1), true);
                }
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    pkGo(Math.max(i - 1, 0), true);
                }
            });
        });

        if (pkScroll && pkPrev && pkNext) {
            pkPrev.addEventListener('click', function() { pkScroll.scrollBy({ left: -pkScroll.clientWidth * 0.7 }); });
            pkNext.addEventListener('click', function() { pkScroll.scrollBy({ left: pkScroll.clientWidth * 0.7 }); });
            pkScroll.addEventListener('scroll', pkArrows, { passive: true });
        }
        pkPanels.forEach(function(g) { g.addEventListener('scroll', pkUpdateBar, { passive: true }); });
        window.addEventListener('resize', function() {
            pkArrows();
            pkUpdateBar();
        });

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function(es) {
                es.forEach(function(e) {
                    if (e.isIntersecting) { pkSec.classList.add('in'); }
                    pkSec.classList.toggle('off', !e.isIntersecting);
                });
            }, { threshold: 0.15 }).observe(pkSec);
        } else {
            pkSec.classList.add('in');
        }

        pkArrows();
        pkUpdateBar();
    }
    /* 10. About: team slider arrows + entrance fallback for browsers without scroll-driven animations */
    var abSec = document.getElementById('tw-about');
    if (abSec) {
        var abTeam = abSec.querySelector('.tw-team');
        var abList = document.getElementById('tw-team-list');
        var abPrev = document.getElementById('tw-team-prev');
        var abNext = document.getElementById('tw-team-next');

        if (abTeam && abList && abPrev && abNext) {
            var abUpdate = function() {
                var max = abList.scrollWidth - abList.clientWidth;
                abTeam.classList.toggle('fits', max <= 2);
                abPrev.disabled = abList.scrollLeft <= 2;
                abNext.disabled = abList.scrollLeft >= max - 2;
            };
            var abStep = function() {
                var item = abList.querySelector('.tw-team__item');
                if (!item) { return abList.clientWidth * 0.8; }
                var w = item.offsetWidth + (parseFloat(getComputedStyle(abList).columnGap) || 0);
                return w * Math.max(1, Math.floor(abList.clientWidth / w) - 1);
            };
            abPrev.addEventListener('click', function() { abList.scrollBy({ left: -abStep() }); });
            abNext.addEventListener('click', function() { abList.scrollBy({ left: abStep() }); });
            abList.addEventListener('scroll', abUpdate, { passive: true });
            window.addEventListener('resize', abUpdate);
            window.addEventListener('load', abUpdate);
            abUpdate();
        }

        var abScrollDriven = window.CSS && CSS.supports && CSS.supports('animation-timeline: view()');
        var abReduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!abScrollDriven && !abReduce && 'IntersectionObserver' in window) {
            abSec.classList.add('io');
            var abIo = new IntersectionObserver(function(es) {
                es.forEach(function(e) {
                    if (e.isIntersecting) {
                        abSec.classList.add('in');
                        abIo.disconnect();
                    }
                });
            }, { threshold: 0.2 });
            abIo.observe(abSec);
        }
    }
    /* 11. Reviews: read more, slider arrows, one-time intro */
    var rvSec = document.getElementById('tw-rev');
    if (rvSec) {
        var rvList = document.getElementById('tw-rev-list');
        var rvPrev = document.getElementById('tw-rev-prev');
        var rvNext = document.getElementById('tw-rev-next');

        /* show "Read more" only where the text is actually cut off */
        var rvMore = function() {
            Array.prototype.forEach.call(rvSec.querySelectorAll('.tw-pc'), function(card) {
                var t = card.querySelector('.tw-pc__text');
                var b = card.querySelector('.tw-pc__more');
                if (!t || !b || card.classList.contains('open')) { return; }
                b.hidden = t.scrollHeight <= t.clientHeight + 2;
            });
        };
        rvSec.addEventListener('click', function(e) {
            var b = e.target.closest('.tw-pc__more');
            if (!b) { return; }
            var card = b.closest('.tw-pc');
            var open = card.classList.toggle('open');
            b.textContent = open ? (b.getAttribute('data-less') || 'Show less') : (b.getAttribute('data-more') || 'Read more');
            b.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        if (rvList && rvPrev && rvNext) {
            var rvUpdate = function() {
                var max = rvList.scrollWidth - rvList.clientWidth;
                rvPrev.disabled = rvList.scrollLeft <= 2;
                rvNext.disabled = rvList.scrollLeft >= max - 2;
            };
            var rvStep = function() {
                var c = rvList.querySelector('.tw-pc');
                return c ? c.offsetWidth + (parseFloat(getComputedStyle(rvList).columnGap) || 0) : rvList.clientWidth * 0.8;
            };
            rvPrev.addEventListener('click', function() { rvList.scrollBy({ left: -rvStep() }); });
            rvNext.addEventListener('click', function() { rvList.scrollBy({ left: rvStep() }); });
            rvList.addEventListener('scroll', rvUpdate, { passive: true });
            window.addEventListener('resize', function() {
                rvUpdate();
                rvMore();
            });
            window.addEventListener('load', function() {
                rvUpdate();
                rvMore();
            });
            rvUpdate();
        }
        rvMore();

        var rvReduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!rvReduce && 'IntersectionObserver' in window) {
            rvSec.classList.add('io');
            var rvIo = new IntersectionObserver(function(es) {
                es.forEach(function(e) {
                    if (e.isIntersecting) {
                        rvSec.classList.add('in');
                        rvIo.disconnect();
                    }
                });
            }, { threshold: 0.25 });
            rvIo.observe(rvSec);
        }
    }
    /* 12. Plan your trek: region row, calendar, departures filter, booking popup, intro */
    var ctSec = document.getElementById('tw-cta');
    if (ctSec) {
        var fd = document.getElementById('tw-fd');
        var bk = document.getElementById('tw-bk');
        var ctReduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* ---- departures ---- */
        if (fd) {
            var T = {};
            try { T = JSON.parse(fd.getAttribute('data-i18n') || '{}'); } catch (err) { T = {}; }
            var fmt = function(s, a, b) { return String(s || '').replace('%1$s', a).replace('%2$s', b).replace('%s', a).replace('%d', a); };
            var lang = document.documentElement.lang || 'en';
            var tp = (fd.getAttribute('data-today') || '').split('-');
            var today = tp.length === 3 ? new Date(+tp[0], +tp[1] - 1, +tp[2]) : new Date();
            var list = document.getElementById('tw-fd-list');
            var rows = Array.prototype.slice.call(list.querySelectorAll('.tw-dep'));
            var empty = list.querySelector('.tw-fd__empty');
            var tabs = document.getElementById('tw-fd-tabs');
            var grid = document.getElementById('tw-cal-grid');
            var calTitle = document.getElementById('tw-cal-title');
            var calPrev = document.getElementById('tw-cal-prev');
            var calNext = document.getElementById('tw-cal-next');
            var calClear = document.getElementById('tw-cal-clear');
            var filterLine = document.getElementById('tw-fd-filter');
            var countLine = document.getElementById('tw-fd-count');

            var parse = function(s) { var p = s.split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); };
            var key = function(d) { return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); };
            var monthName = new Intl.DateTimeFormat(lang, { month: 'long', year: 'numeric' });
            var longDate = new Intl.DateTimeFormat(lang, { day: 'numeric', month: 'short', year: 'numeric' });
            var dows = [];
            for (var w = 0; w < 7; w++) { dows.push(new Intl.DateTimeFormat(lang, { weekday: 'short' }).format(new Date(2024, 0, 1 + w)).slice(0, 2)); }

            var st = { r: 'all', day: null, month: null };
            var inR = function(row) { return st.r === 'all' || row.getAttribute('data-r') === st.r; };
            var regionName = function() {
                if (!tabs || st.r === 'all') { return ''; }
                var t = tabs.querySelector('[data-r="' + st.r + '"]');
                return t ? t.textContent.replace(/\s*\(\d+\)\s*$/, '') : '';
            };
            var firstMonthFor = function() {
                var r = rows.filter(inR)[0];
                var d = r ? parse(r.getAttribute('data-date')) : today;
                return new Date(d.getFullYear(), d.getMonth(), 1);
            };
            st.month = firstMonthFor();

            /* list height: first four rows (two on phones), then scroll */
            var fade = function() { list.classList.toggle('more', list.scrollHeight - list.scrollTop - list.clientHeight > 4); };
            var fitList = function() {
                var vis = rows.filter(function(r) { return !r.hidden; });
                var n = window.matchMedia('(max-width: 600px)').matches ? 2 : 4;
                if (vis.length > n) {
                    var last = vis[n - 1];
                    list.classList.remove('all');
                    list.style.setProperty('--tw-fd-h', (last.offsetTop + last.offsetHeight - vis[0].offsetTop + 4) + 'px');
                } else {
                    list.classList.add('all');
                }
                fade();
                return vis.length > n;
            };
            list.addEventListener('scroll', fade, { passive: true });

            var drawList = function(animate) {
                var shown = 0;
                rows.forEach(function(r) {
                    var on = inR(r) && (!st.day || r.getAttribute('data-date') === st.day);
                    r.hidden = !on;
                    if (on) { r.style.setProperty('--i', Math.min(shown, 8));
                        shown++; }
                });
                if (empty) { empty.hidden = shown > 0; }
                var rn = regionName();
                var lead = st.day ? fmt(T.departing, '<b>' + longDate.format(parse(st.day)) + '</b>') : (T.next || '');
                filterLine.innerHTML = lead + (rn ? ' ' + fmt(T['in'], '<b>' + rn.replace(/</g, '&lt;') + '</b>') : '');
                list.scrollTop = 0;
                var scrolls = fitList();
                countLine.textContent = (shown === 1 ? (T.one || '') : fmt(T.many, shown)) + (scrolls ? ', ' + (T.scroll || '') : '');
                if (animate && !ctReduce) { list.classList.remove('play');
                    void list.offsetWidth;
                    list.classList.add('play'); }
            };

            var drawCal = function() {
                var m = st.month,
                    y = m.getFullYear(),
                    mo = m.getMonth();
                calTitle.textContent = monthName.format(m);
                var counts = {};
                rows.forEach(function(r) { if (inR(r)) { var k = r.getAttribute('data-date');
                        counts[k] = (counts[k] || 0) + 1; } });
                var html = dows.map(function(d) { return '<span class="tw-cal__dow" aria-hidden="true">' + d + '</span>'; }).join('');
                var lead = (new Date(y, mo, 1).getDay() + 6) % 7;
                for (var i = 0; i < lead; i++) { html += '<span></span>'; }
                var days = new Date(y, mo + 1, 0).getDate();
                for (var d = 1; d <= days; d++) {
                    var dt = new Date(y, mo, d),
                        k = key(dt),
                        n = counts[k] || 0;
                    var cls = 'tw-cal__day' + (n ? ' has' : '') + (k === st.day ? ' sel' : '') + (k === key(today) ? ' today' : '');
                    html += n ?
                        '<button type="button" class="' + cls + '" data-d="' + k + '" aria-pressed="' + (k === st.day) + '" aria-label="' + longDate.format(dt) + ', ' + fmt(T.dayLabel, n) + '">' + d + '</button>' :
                        '<span class="' + cls + '" aria-hidden="true">' + d + '</span>';
                }
                grid.innerHTML = html;
                var firstM = new Date(today.getFullYear(), today.getMonth(), 1);
                var lastRow = rows.filter(inR).slice(-1)[0];
                var lastD = lastRow ? parse(lastRow.getAttribute('data-date')) : today;
                calPrev.disabled = m <= firstM;
                calNext.disabled = y > lastD.getFullYear() || (y === lastD.getFullYear() && mo >= lastD.getMonth());
                calClear.hidden = !st.day;
            };

            var draw = function(animate) { drawCal();
                drawList(animate); };

            grid.addEventListener('click', function(e) {
                var b = e.target.closest('button.tw-cal__day');
                if (!b) { return; }
                var k = b.getAttribute('data-d');
                st.day = st.day === k ? null : k;
                draw(true);
            });
            calPrev.addEventListener('click', function() { st.month = new Date(st.month.getFullYear(), st.month.getMonth() - 1, 1);
                drawCal(); });
            calNext.addEventListener('click', function() { st.month = new Date(st.month.getFullYear(), st.month.getMonth() + 1, 1);
                drawCal(); });
            calClear.addEventListener('click', function() { st.day = null;
                draw(true); });

            /* region row */
            if (tabs) {
                var rWrap = tabs.parentNode;
                var rPrev = document.getElementById('tw-fd-rprev');
                var rNext = document.getElementById('tw-fd-rnext');
                var rUpdate = function() {
                    var max = tabs.scrollWidth - tabs.clientWidth;
                    var atStart = tabs.scrollLeft <= 2,
                        atEnd = tabs.scrollLeft >= max - 2;
                    rWrap.classList.toggle('fits', max <= 2);
                    rWrap.classList.toggle('l', !atStart);
                    rWrap.classList.toggle('r', !atEnd);
                    rPrev.disabled = atStart;
                    rNext.disabled = atEnd;
                };
                rPrev.addEventListener('click', function() { tabs.scrollBy({ left: -tabs.clientWidth * 0.7 }); });
                rNext.addEventListener('click', function() { tabs.scrollBy({ left: tabs.clientWidth * 0.7 }); });
                tabs.addEventListener('scroll', rUpdate, { passive: true });
                window.addEventListener('resize', rUpdate);
                rUpdate();

                tabs.addEventListener('click', function(e) {
                    var b = e.target.closest('.tw-fd__tab');
                    if (!b) { return; }
                    st.r = b.getAttribute('data-r');
                    Array.prototype.forEach.call(tabs.children, function(t) { t.setAttribute('aria-selected', t === b ? 'true' : 'false'); });
                    b.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
                    if (st.day && !rows.some(function(r) { return inR(r) && r.getAttribute('data-date') === st.day; })) { st.day = null; }
                    var m = st.month;
                    var hasHere = rows.some(function(r) { if (!inR(r)) { return false; } var d = parse(r.getAttribute('data-date')); return d.getFullYear() === m.getFullYear() && d.getMonth() === m.getMonth(); });
                    if (!hasHere) { st.month = firstMonthFor(); }
                    draw(true);
                });
                tabs.addEventListener('keydown', function(e) {
                    if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') { return; }
                    var all = Array.prototype.slice.call(tabs.children);
                    var i = all.indexOf(document.activeElement);
                    if (i < 0) { return; }
                    e.preventDefault();
                    var nx = all[Math.max(0, Math.min(all.length - 1, i + (e.key === 'ArrowRight' ? 1 : -1)))];
                    nx.focus();
                    nx.click();
                });
            }

            window.addEventListener('resize', fitList);
            draw(false);
        }

        /* ---- booking popup (posts to the existing trekways_booking handler) ---- */
        if (bk && typeof bk.showModal === 'function') {
            var f = bk.querySelector('form');
            var bTitle = document.getElementById('tw-bk-title');
            var bSub = document.getElementById('tw-bk-sub');
            var bErr = document.getElementById('tw-bk-error');
            var tripIn = f.elements.trip_name,
                dateIn = f.elements.departure_date,
                trav = f.elements.travellers;
            var enquireTitle = bTitle.textContent;
            var tripLbl = tripIn.closest('label').querySelector('span');
            var dateLbl = dateIn.closest('label').querySelector('span');
            var tripLblDef = tripLbl.textContent,
                dateLblDef = dateLbl.textContent;
            var lastBtn = null;

            var openBk = function(btn) {
                lastBtn = btn;
                var dep = btn.getAttribute('data-tw-book') === 'departure';
                f.elements.booking_type.value = dep ? 'departure' : 'custom';
                f.elements.trip_id.value = dep ? btn.getAttribute('data-trip-id') : '';
                tripIn.value = dep ? btn.getAttribute('data-trip') : '';
                tripIn.readOnly = dep;
                dateIn.value = dep ? btn.getAttribute('data-date') : '';
                dateIn.readOnly = dep;
                var max = dep ? btn.getAttribute('data-max') : '';
                if (max) { trav.max = max; if (+trav.value > +max) { trav.value = max; } } else { trav.removeAttribute('max'); }
                var T2 = {};
                try { T2 = JSON.parse((document.getElementById('tw-fd') || bk).getAttribute('data-i18n') || '{}'); } catch (err) { T2 = {}; }
                bTitle.textContent = dep && T2.book ? T2.book.replace('%1$s', btn.getAttribute('data-trip')).replace('%2$s', btn.getAttribute('data-date-label')) : enquireTitle;
                bSub.textContent = '';
                tripLbl.textContent = dep && T2.trek ? T2.trek : tripLblDef;
                dateLbl.textContent = dep && T2.depdate ? T2.depdate : dateLblDef;
                bk.showModal();
                (f.elements.full_name.value ? f.elements.message : f.elements.full_name).focus();
            };

            document.addEventListener('click', function(e) {
                var b = e.target.closest('[data-tw-book]');
                if (b && ctSec.contains(b)) { e.preventDefault();
                    openBk(b); return; }
                if (e.target.closest('[data-tw-close]') || e.target === bk) { bk.close(); }
            });
            bk.addEventListener('close', function() { if (lastBtn) { lastBtn.focus(); } });

            /* the handler sends people back with ?booking=error: reopen the form with a note */
            if (/[?&]booking=error\b/.test(window.location.search)) {
                bErr.hidden = false;
                var opener = ctSec.querySelector('[data-tw-book="custom"]');
                if (opener) { ctSec.scrollIntoView();
                    openBk(opener); }
            }
        }

        /* ---- intro ---- */
        if (!ctReduce && 'IntersectionObserver' in window) {
            ctSec.classList.add('io');
            var ctIo = new IntersectionObserver(function(es) {
                es.forEach(function(e) {
                    if (e.isIntersecting) {
                        ctSec.classList.add('in');
                        var l = document.getElementById('tw-fd-list');
                        if (l) { l.classList.add('play'); }
                        ctIo.disconnect();
                    }
                });
            }, { threshold: 0.15 });
            ctIo.observe(ctSec);
        }
    }
})();