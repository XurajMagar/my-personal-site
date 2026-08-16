/* Trek Ways — front-end interactions */
(function () {
	'use strict';

	/* 1. Hero video: load the right source for the screen (mirrors Desire Adventure) */
	var v = document.querySelector('.tw-hero__video');
	if (v) {
		var mq = window.matchMedia('(min-width: 1024px)');
		var pick = function () {
			var want = mq.matches ? v.dataset.srcDesktop : v.dataset.srcMobile;
			if (!want) {                     // no video for this screen -> show poster
				if (v.getAttribute('src')) { v.pause(); v.removeAttribute('src'); v.load(); }
				v.style.display = 'none';
				return;
			}
			v.style.display = '';
			if (v.getAttribute('src') === want) return;
			v.src = want; v.load();
			var p = v.play(); if (p) { p.catch(function () {}); }
		};
		pick();
		mq.addEventListener('change', pick);
	}

	/* 2. Mobile burger toggle (no hover on touch) */
	var burger = document.querySelector('.tw-burger');
	var nav = document.querySelector('.tw-nav');
	if (burger && nav) {
		burger.addEventListener('click', function () {
			var open = nav.classList.toggle('tw-open');
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}
	/* 3. Solid nav once scrolled past the hero (readable over light sections) */
	var dock = document.querySelector('.tw-navdock');
	if (dock) {
		var onScroll = function () {
			if (window.scrollY > 40) { dock.classList.add('tw-scrolled'); }
			else { dock.classList.remove('tw-scrolled'); }
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}
	/* 4. Mega menu: click-to-open panels + category switching (desktop panel / mobile accordion) */
	var megaOverlay = document.getElementById('tw-mega-overlay');
	var megaButtons = document.querySelectorAll('.tw-topbtn');
	var navDockEl   = document.querySelector('.tw-navdock');
	function tw_closeAllMega() {
		megaButtons.forEach(function (b) { b.classList.remove('active'); });
		document.querySelectorAll('.tw-mega').forEach(function (m) { m.classList.remove('open'); });
		if (megaOverlay) { megaOverlay.classList.remove('show'); }
		if (navDockEl) { navDockEl.classList.remove('tw-menu-open'); }
	}
	megaButtons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			var slug  = btn.getAttribute('data-menu');
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
	document.querySelectorAll('.tw-mega__cat').forEach(function (cat) {
		cat.addEventListener('click', function () {
			var mega = cat.closest('.tw-mega');
			if (!mega) { return; }
			mega.querySelectorAll('.tw-mega__cat').forEach(function (c) { c.classList.remove('active'); });
			mega.querySelectorAll('.tw-mega__pane').forEach(function (p) { p.classList.remove('active'); });
			cat.classList.add('active');
			var pane = mega.querySelector('.tw-mega__pane[data-pane="' + cat.getAttribute('data-pane') + '"]');
			if (pane) { pane.classList.add('active'); }
			if (mqMobile.matches) { mega.classList.add('tw-mega--drilled'); }
		});
	});

	/* 5. Mobile drill-down: Back button returns to the category list */
	var mqMobile = window.matchMedia('(max-width:1100px)');
	document.querySelectorAll('.tw-mega__back').forEach(function (back) {
		back.addEventListener('click', function () {
			var mega = back.closest('.tw-mega');
			if (mega) { mega.classList.remove('tw-mega--drilled'); }
		});
	});
})();