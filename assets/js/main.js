/**
* Template Name: CoreBiz
* Template URL: https://bootstrapmade.com/corebiz-bootstrap-business-template/
* Updated: Aug 30 2025 with Bootstrap v5.3.8
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/

(function() {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  let hasScrolledClass = false;

  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;

    const enterAt = 120;
    const exitAt = 80;
    const scrollY = window.scrollY;

    if (!hasScrolledClass && scrollY > enterAt) {
      selectBody.classList.add('scrolled');
      hasScrolledClass = true;
    } else if (hasScrolledClass && scrollY < exitAt) {
      selectBody.classList.remove('scrolled');
      hasScrolledClass = false;
    }
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);

  /**
   * Mobile nav toggle
   */
  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavToggleBtn.classList.toggle('bi-list');
    mobileNavToggleBtn.classList.toggle('bi-x');
  }
  if (mobileNavToggleBtn) {
    mobileNavToggleBtn.addEventListener('click', mobileNavToogle);
  }

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navmenu a').forEach(navmenu => {
    navmenu.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
      }
    });

  });

  /**
   * Toggle mobile nav dropdowns
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentNode.classList.toggle('active');
      this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
      e.stopImmediatePropagation();
    });
  });

  /**
   * Preloader
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove();
    });
  }

  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
  }
  scrollTop.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  }
  window.addEventListener('load', aosInit);

  /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: '.glightbox'
  });

  /**
   * Initiate Pure Counter
   */
  new PureCounter();

  /**
   * Animate the skills items on reveal
   */
  let skillsAnimation = document.querySelectorAll('.skills-animation');
  skillsAnimation.forEach((item) => {
    new Waypoint({
      element: item,
      offset: '80%',
      handler: function(direction) {
        let progress = item.querySelectorAll('.progress .progress-bar');
        progress.forEach(el => {
          el.style.width = el.getAttribute('aria-valuenow') + '%';
        });
      }
    });
  });

  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", initSwiper);

  /**
   * Init isotope layout and filters
   */
  document.querySelectorAll('.isotope-layout').forEach(function(isotopeItem) {
    let layout = isotopeItem.getAttribute('data-layout') ?? 'masonry';
    let filter = isotopeItem.getAttribute('data-default-filter') ?? '*';
    let sort = isotopeItem.getAttribute('data-sort') ?? 'original-order';

    let initIsotope;
    imagesLoaded(isotopeItem.querySelector('.isotope-container'), function() {
      initIsotope = new Isotope(isotopeItem.querySelector('.isotope-container'), {
        itemSelector: '.isotope-item',
        layoutMode: layout,
        filter: filter,
        sortBy: sort
      });
    });

    isotopeItem.querySelectorAll('.isotope-filters li').forEach(function(filters) {
      filters.addEventListener('click', function() {
        isotopeItem.querySelector('.isotope-filters .filter-active').classList.remove('filter-active');
        this.classList.add('filter-active');
        initIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
        if (typeof aosInit === 'function') {
          aosInit();
        }
      }, false);
    });

  });

  /*
   * Pricing Plan Toggle (Daily / Weekly / Monthly)
   */
  const pricingContainers = document.querySelectorAll('.pricing-toggle-container');

  pricingContainers.forEach(function(container) {
    const planOptions = container.querySelectorAll('.pricing-toggle .plan-option');
    const cards = container.parentElement.querySelectorAll('.pricing-item');

    if (!planOptions.length || !cards.length) {
      return;
    }

    function applyPlan(selectedPlan) {
      planOptions.forEach((option) => {
        option.classList.toggle('active', option.dataset.plan === selectedPlan);
      });

      cards.forEach((card) => {
        const showCard = card.dataset.plan === selectedPlan;
        const cardColumn = card.closest('div[class*="col-lg-"]');
        if (cardColumn) {
          cardColumn.classList.remove('col-lg-4', 'col-lg-10', 'col-xl-9');
          cardColumn.classList.add(showCard ? 'col-lg-10' : 'col-lg-4');
          if (showCard) {
            cardColumn.classList.add('col-xl-9');
          }
          cardColumn.style.display = showCard ? '' : 'none';
        }
        card.classList.toggle('landscape-layout', showCard);
      });
    }

    planOptions.forEach((option) => {
      option.addEventListener('click', function() {
        applyPlan(this.dataset.plan);
      });
    });

    const defaultPlan = container.querySelector('.plan-option.active')?.dataset.plan || 'daily';
    applyPlan(defaultPlan);
  });

  /*
   * Reveal payment options only after plan button click
   */
  const paymentOptionsSection = document.querySelector('#payment-options');
  const paymentButtons = document.querySelectorAll('.pricing .price-card .cta a[href="#payment-options"]');

  paymentButtons.forEach((button) => {
    button.addEventListener('click', function(event) {
      if (!paymentOptionsSection) {
        return;
      }

      event.preventDefault();
      paymentOptionsSection.classList.remove('payment-options-hidden');
      paymentOptionsSection.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    });
  });

  /**
   * Frequently Asked Questions Toggle
   */
  document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle, .faq-item .faq-header').forEach((faqItem) => {
    faqItem.addEventListener('click', () => {
      faqItem.parentNode.classList.toggle('faq-active');
    });
  });

  /**
   * Correct scrolling position upon page load for URLs containing hash links.
   */
  window.addEventListener('load', function(e) {
    if (window.location.hash) {
      if (document.querySelector(window.location.hash)) {
        setTimeout(() => {
          let section = document.querySelector(window.location.hash);
          let scrollMarginTop = getComputedStyle(section).scrollMarginTop;
          window.scrollTo({
            top: section.offsetTop - parseInt(scrollMarginTop),
            behavior: 'smooth'
          });
        }, 100);
      }
    }
  });

  /**
   * Navmenu Scrollspy
   */
  let navmenulinks = document.querySelectorAll('.navmenu a');

  function navmenuScrollspy() {
    navmenulinks.forEach(navmenulink => {
      if (!navmenulink.hash) return;
      let section = document.querySelector(navmenulink.hash);
      if (!section) return;
      let position = window.scrollY + 200;
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        document.querySelectorAll('.navmenu a.active').forEach(link => link.classList.remove('active'));
        navmenulink.classList.add('active');
      } else {
        navmenulink.classList.remove('active');
      }
    })
  }
  window.addEventListener('load', navmenuScrollspy);
  document.addEventListener('scroll', navmenuScrollspy);

  /*
   * Newsletter form helpers
   */
  document.querySelectorAll('form.php-email-form[action="forms/newsletter.php"]').forEach((form) => {
    const emailInput = form.querySelector('input[name="email"]');
    if (emailInput) {
      emailInput.setAttribute('required', 'required');
    }
  });

  /*
   * Risk disclosure banner (fixed bottom, dismissible)
   */
  function initRiskDisclosureBanner() {
    const storageKey = 'kachingfx_risk_disclosure_dismissed_v1';

    try {
      if (localStorage.getItem(storageKey) === 'true') {
        return;
      }
    } catch (error) {
      // If storage is blocked, still show the banner for this session.
    }

    const banner = document.createElement('div');
    banner.id = 'risk-disclosure-banner';
    banner.className = 'risk-disclosure-banner';
    banner.setAttribute('role', 'region');
    banner.setAttribute('aria-label', 'Risk Disclosure');
    banner.innerHTML = `
      <div class="risk-disclosure-inner">
        <p class="risk-disclosure-text">
          <strong>Risk Warning:</strong> Your capital is at risk. Leveraged products may not be suitable for everyone.
          Please consider our
          <a href="risk-disclosure.html">Risk Disclosure</a>.
        </p>
        <button type="button" class="risk-disclosure-close" aria-label="Close risk disclosure">×</button>
      </div>
    `;

    const closeButton = banner.querySelector('.risk-disclosure-close');
    closeButton.addEventListener('click', () => {
      banner.remove();
      document.body.classList.remove('has-risk-disclosure');
      try {
        localStorage.setItem(storageKey, 'true');
      } catch (error) {
        // no-op
      }
    });

    document.body.appendChild(banner);
    document.body.classList.add('has-risk-disclosure');
  }

  initRiskDisclosureBanner();

})();