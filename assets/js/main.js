/**
 * Checkmate Daily — Main JS  v2.0
 * Added: keyboard-accessible mega menu, mobile search overlay, back-to-top button
 */
(function($){
'use strict';

/* =====================================================
   MOBILE NAV TOGGLE
===================================================== */
$('#cd-mob-toggle').on('click', function(){
  var $nav = $('#cd-nav');
  var open = $nav.toggleClass('open').hasClass('open');
  $(this).attr('aria-expanded', open);
});

/* Close mobile nav when clicking outside header */
$(document).on('click', function(e){
  if (!$(e.target).closest('#cd-header').length) {
    $('#cd-nav').removeClass('open');
    $('#cd-mob-toggle').attr('aria-expanded', false);
  }
});

/* =====================================================
   KEYBOARD-ACCESSIBLE MEGA MENU
   — Adds aria-haspopup / aria-expanded to parent items
   — Enter / Space toggles submenu on keyboard
   — Escape closes open submenus
   — CSS :focus-within (in style.css) handles the
     visual show/hide automatically for all keyboard
     and touch interactions
===================================================== */
(function initAccessibleNav() {
  /* Find all top-level li items that have a child <ul> */
  $('#cd-nav > div > ul > li').each(function(){
    var $li   = $(this);
    var $sub  = $li.children('ul');
    if (!$sub.length) return;            /* no dropdown — skip */

    var $link = $li.children('a');

    /* Mark the parent link as a menu trigger */
    $link.attr({
      'aria-haspopup': 'true',
      'aria-expanded': 'false'
    });

    /* Update aria-expanded while keyboard focus is inside this item */
    $li.on('focusin', function(){
      $link.attr('aria-expanded', 'true');
    }).on('focusout', function(e){
      /* Only collapse if focus is moving outside this <li> entirely */
      if (!$li.has(e.relatedTarget).length){
        $link.attr('aria-expanded', 'false');
      }
    });

    /*
     * Enter / Space on the parent link:
     *   — if menu is closed, prevent navigation and open it
     *   — if menu is open, let Enter navigate; Space closes
     */
    $link.on('keydown', function(e){
      if (e.key === 'Enter'){
        if ($link.attr('aria-expanded') === 'false'){
          e.preventDefault();
          $link.attr('aria-expanded', 'true');
          /* Focus first child link */
          $sub.find('a').first().focus();
        }
        /* If already open, let Enter follow the link naturally */
      }
      if (e.key === ' '){
        e.preventDefault();
        var isOpen = $link.attr('aria-expanded') === 'true';
        $link.attr('aria-expanded', String(!isOpen));
        if (!isOpen) $sub.find('a').first().focus();
      }
    });

    /* Arrow-Down on parent link opens and focuses first child */
    $link.on('keydown', function(e){
      if (e.key === 'ArrowDown'){
        e.preventDefault();
        $link.attr('aria-expanded', 'true');
        $sub.find('a').first().focus();
      }
    });

    /* Arrow keys within the open submenu */
    $sub.on('keydown', 'a', function(e){
      var $items = $sub.find('a');
      var idx    = $items.index(this);
      if (e.key === 'ArrowDown'){
        e.preventDefault();
        $items.eq(idx + 1).focus();
      }
      if (e.key === 'ArrowUp'){
        e.preventDefault();
        if (idx === 0) { $link.focus(); }          /* back to parent */
        else           { $items.eq(idx - 1).focus(); }
      }
    });
  });

  /* Escape closes any open submenu and returns focus to parent link */
  $(document).on('keydown', function(e){
    if (e.key === 'Escape'){
      var $open = $('#cd-nav a[aria-expanded="true"]');
      if ($open.length){
        $open.attr('aria-expanded', 'false').focus();
      }
    }
  });
})();

/* =====================================================
   MOBILE SEARCH BAR
   — Button in header opens/closes a full-width search
     bar that sits inside the sticky header
   — Escape or clicking outside closes it
   — Input is auto-focused when opened
===================================================== */
(function initMobileSearch(){
  var $btn   = $('#cd-mob-search-btn');
  var $bar   = $('#cd-mob-search-bar');
  var $input = $('#cd-mob-search-input');
  var $close = $('#cd-mob-search-close');

  function openSearch(){
    $bar.addClass('open').attr('aria-hidden', 'false');
    $btn.attr('aria-expanded', 'true');
    /* Small delay lets the CSS transition start before focus fires */
    setTimeout(function(){ $input.focus(); }, 60);
  }

  function closeSearch(){
    $bar.removeClass('open').attr('aria-hidden', 'true');
    $btn.attr('aria-expanded', 'false').focus();
  }

  $btn.on('click', function(){
    if ($bar.hasClass('open')){ closeSearch(); }
    else                       { openSearch();  }
  });

  $close.on('click', closeSearch);

  /* Escape closes the bar */
  $(document).on('keydown', function(e){
    if (e.key === 'Escape' && $bar.hasClass('open')){
      closeSearch();
    }
  });

  /* Click outside the bar + toggle button closes it */
  $(document).on('click', function(e){
    if ($bar.hasClass('open') &&
        !$(e.target).closest('#cd-mob-search-bar, #cd-mob-search-btn').length){
      closeSearch();
    }
  });
})();

/* =====================================================
   NEWSLETTER FORM
===================================================== */
$('#cd-newsletter-form').on('submit', function(e){
  e.preventDefault();
  var email = $(this).find('input[type=email]').val();
  var $msg  = $('#cd-nl-msg'), $btn = $(this).find('button');
  $btn.text('...').prop('disabled', true);
  $.post(cdAjax.ajaxurl, {
    action:'cd_newsletter', email:email, nonce:cdAjax.nonce
  }, function(r){
    $btn.prop('disabled',false).text('Subscribe');
    $msg.show().css('color', r.success ? '#6ee7a0' : '#f87171').text(r.data.msg);
  }).fail(function(){ $btn.prop('disabled',false).text('Subscribe'); });
});

/* =====================================================
   AJAX FILTER — Format + State combined
===================================================== */
if ($('#cd-india-results, #cd-news-results').length) {
  var state = { cat:'all', st:'all', paged:1, total:0 };

  function runFilter(replace) {
    var $wrap = $('#cd-india-results, #cd-news-results').first();
    $wrap.css('opacity','0.5');
    $('.cd-filter-info').text('Loading...');

    $.post(cdAjax.ajaxurl, {
      action:'cd_filter_posts', nonce:cdAjax.nonce,
      category:state.cat, state:state.st, paged:state.paged
    }, function(r){
      $wrap.css('opacity','1');
      if (!r.success) return;
      state.total = parseInt(r.data.found)||0;

      if (replace) {
        $wrap.html(r.data.html);
      } else {
        $wrap.find('.cd-load-more-wrap').remove();
        $wrap.find('.cd-news-grid').append($(r.data.html).find('.cd-news-card'));
      }

      $wrap.find('.cd-load-more-wrap').remove();
      if (state.paged * 8 < state.total) {
        $wrap.append('<div class="cd-load-more-wrap"><button class="cd-load-more-btn">Load More</button></div>');
      }

      var cl = $('[data-filter="cat"].active').text().trim()||'All';
      var sl = $('[data-filter="state"].active').text().trim()||'All States';
      $('.cd-filter-info').text(state.total + ' articles — ' + cl + ' / ' + sl);
    }).fail(function(){
      $wrap.css('opacity','1');
      $('.cd-filter-info').text('Could not load. Please refresh.');
    });
  }

  $(document).on('click','[data-filter="cat"]', function(){
    $('[data-filter="cat"]').removeClass('active');
    $(this).addClass('active');
    state.cat = $(this).data('value');
    state.paged = 1;
    updateClear(); runFilter(true);
  });

  $(document).on('click','[data-filter="state"]', function(){
    $('[data-filter="state"]').removeClass('active');
    $(this).addClass('active');
    state.st = $(this).data('value');
    state.paged = 1;
    updateClear(); runFilter(true);
  });

  $(document).on('click','.cd-filter-clear', function(){
    state.cat='all'; state.st='all'; state.paged=1;
    $('[data-filter]').removeClass('active');
    $('[data-value="all"]').addClass('active');
    $(this).removeClass('visible');
    runFilter(true);
  });

  $(document).on('click','.cd-load-more-btn', function(){
    if (state.paged*8 < state.total){ state.paged++; runFilter(false); }
  });

  function updateClear(){
    if (state.cat!=='all'||state.st!=='all') $('.cd-filter-clear').addClass('visible');
    else $('.cd-filter-clear').removeClass('visible');
  }
}

/* =====================================================
   BACK TO TOP BUTTON
   — Fades in after scrolling 400px
   — Smooth scroll to top on click
   — Also accessible via keyboard (button in DOM)
===================================================== */
(function initBackToTop(){
  var $btn       = $('#cd-back-top');
  var threshold  = 400;
  var ticking    = false;

  function updateVisibility(){
    if ($(window).scrollTop() > threshold){
      $btn.addClass('visible');
    } else {
      $btn.removeClass('visible');
    }
    ticking = false;
  }

  $(window).on('scroll.backtop', function(){
    if (!ticking){
      window.requestAnimationFrame(updateVisibility);
      ticking = true;
    }
  });

  $btn.on('click', function(){
    $('html, body').animate({ scrollTop: 0 }, 380, 'swing');
  });

  /* Keyboard: Enter already works (button), Space triggers click natively */
})();

/* =====================================================
   READING PROGRESS BAR (single posts only)
===================================================== */
(function(){
  var bar     = document.getElementById('cd-reading-progress-fill');
  var wrap    = document.getElementById('cd-reading-progress');
  if (!bar) return;

  var article = document.querySelector('.cd-single-content') || document.body;
  var ticking = false;

  function updateProgress(){
    var articleTop    = article.getBoundingClientRect().top + window.pageYOffset;
    var articleBottom = articleTop + article.offsetHeight;
    var viewportH     = window.innerHeight;
    var scrolled      = window.pageYOffset + viewportH - articleTop;
    var total         = articleBottom - articleTop;
    var pct           = Math.min(100, Math.max(0, Math.round((scrolled / total) * 100)));
    bar.style.width = pct + '%';
    wrap.setAttribute('aria-valuenow', pct);
  }

  window.addEventListener('scroll', function(){
    if (!ticking){
      window.requestAnimationFrame(function(){ updateProgress(); ticking = false; });
      ticking = true;
    }
  }, { passive: true });

  updateProgress();
})();

})(jQuery);