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
})();