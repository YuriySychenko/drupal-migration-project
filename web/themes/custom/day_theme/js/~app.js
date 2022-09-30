import { tns } from "tiny-slider/src/tiny-slider"
// import GLightbox from 'glightbox';
import { bindTabs } from "./lib/tabs";
// import { bindTabsSvg } from "./lib/tabsSvg";
import { selectFunction } from "./lib/selectFunction"

document.addEventListener("DOMContentLoaded", () => {
  const body = document.querySelector("body");
  const topBanner = document.querySelector('.branding-top');
  const topBannerBtn = document.querySelector('.branding-top-btn');
  // const header = document.querySelector("header");
  const burgerTop = document.getElementById("burgerTop");
  const subNav = document.querySelector(".header__nav-bottom");
  const select = document.querySelectorAll(".header__dropdown-text");
  const option = document.querySelectorAll(".header__dropdown-content a");
  const dropdownSelectLang = document.querySelector(
    ".header__dropdown-content"
  );

  const themeBtn = document.querySelector(".themes__btn");
  const themeBox = document.querySelector(".themes");
  const themeBtnIcon = document.querySelector(".themes__btn .icon-arrow-down");

  const modalTriggers = document.querySelectorAll(".popup-trigger");
  const modalCloseTrigger = document.querySelector(".popup__btn-close");
  const popupOverlay = document.querySelector(".popup__overlay");
  const modalInner = document.querySelector(".popup__inner");

  // const headerHeight = header.offsetHeight;
  // document.documentElement.style.setProperty('--header-height', '152px');

  // Menu
  const openTopBurger = () => {
    subNav.classList.toggle("active");
    burgerTop.classList.toggle("burger-top-active");
    body.classList.toggle("body-freeze");
  };

  const closeTopBurger = () => {
    burgerTop.classList.remove("burger-top-active");
    subNav.classList.remove("active");
    body.classList.remove("body-freeze");
  };

  burgerTop.addEventListener("click", function () {
    openTopBurger();
  });

  document.addEventListener(
    "click",
    (e) => {
      if (
        // e.target.matches(".header__nav-bottom a") ||
        e.target.closest(".header__nav-bottom.active a") ||
        e.target.closest(".header__nav-bottom.active") ||
        e.target.closest(".main")
      ) {
        closeTopBurger();
      }
    },
    false
  );

  // Theme
  if (themeBtn) {
    const openThemelist = () => {
      themeBtn.classList.add("active");
      themeBox.classList.add("active");
      themeBtnIcon.classList.add("active");
    };

    const closeThemelist = () => {
      themeBtn.classList.remove("active");
      themeBox.classList.remove("active");
      themeBtnIcon.classList.remove("active");
    };

    themeBtn.addEventListener("click", () => {
      if (themeBox.classList.contains("active")) {
        closeThemelist();
      } else {
        openThemelist();
      }
    });

    // Close theme if click outside
    document.addEventListener("click", (e) => {
      const isClickInsideElement = themeBox.contains(e.target);

      if (themeBox.classList.contains("active") && !isClickInsideElement) {
        closeThemelist();
      }
    });
  }

  // Select lang
  select.forEach((a) => {
    a.addEventListener("click", (b) => {
      const next = b.target.nextElementSibling;
      next.classList.toggle("active");
    });
  });

  option.forEach((a) => {
    a.addEventListener("click", (b) => {
      b.target.parentElement.parentElement.classList.remove("active");

      const parent = b.target.closest(".header__dropdown").children[0];
      parent.setAttribute("data-type", b.target.getAttribute("data-type"));
      parent.innerText = b.target.innerText;
    });
  });

  document.addEventListener(
    "click",
    (e) => {
      if (
        e.target.matches(".header__dropdown-content a") ||
        e.target.closest(".header__nav-bottom.active") ||
        e.target.closest(".main")
      ) {
        dropdownSelectLang.classList.remove("active");
      }
    },
    false
  );

  // Tabs
  bindTabs();
  // bindTabsSvg();

  // Modals

  modalTriggers.forEach((trigger) => {
    trigger.addEventListener("click", () => {
      const { popupTrigger } = trigger.dataset;
      const popupModal = document.querySelector(
        `[data-popup-modal="${popupTrigger}"]`
      );

      popupModal.classList.add("is--visible");
      popupOverlay.classList.add("is-blacked-out");
      body.classList.add("body-freeze");

      modalCloseTrigger.addEventListener("click", () => {
        popupModal.classList.remove("is--visible");
        popupOverlay.classList.remove("is-blacked-out");
        body.classList.remove("body-freeze");
      });

      popupOverlay.addEventListener("click", () => {
        popupModal.classList.remove("is--visible");
        popupOverlay.classList.remove("is-blacked-out");
        body.classList.remove("body-freeze");
      });

      popupModal.addEventListener("click", (e) => {
        const isClickInsideElement = modalInner.contains(e.target);

        if (body.classList.contains("body-freeze") && !isClickInsideElement) {
          popupModal.classList.remove("is--visible");
          popupOverlay.classList.remove("is-blacked-out");
          body.classList.remove("body-freeze");
        }
      });
    });
  });

  // Cursor
  const cursor = document.querySelector(".cursor");
  const cursorSwipe = document.querySelector(".cursor-swipe");
  // const hoverLinks = document.querySelectorAll(".news a img");
  const hoverImg = document.querySelectorAll("._cursor img");
  const hoverImg2 = document.querySelectorAll("._cursor-overlay");
  const hoverSwipe = document.querySelectorAll("._cursor-swipe");

  document.addEventListener("mousemove", function (e) {
    const x = e.clientX;
    const y = e.clientY;
    cursor.style.transform = `translate3d(calc(${e.clientX}px - 50%), calc(${e.clientY}px - 50%), 0)`;
  });

  document.addEventListener("mousemove", function (e) {
    const x = e.clientX;
    const y = e.clientY;
  });

  document.addEventListener("mousedown", function () {
    cursor.classList.add("click");
  });

  document.addEventListener("mouseup", function () {
    cursor.classList.remove("click");
  });


  hoverImg.forEach((item) => {
    item.addEventListener("mouseover", () => {
      cursor.classList.add("cursor-hover");
    });
    item.addEventListener("mouseleave", () => {
      cursor.classList.remove("cursor-hover");
    });
  });

  hoverImg2.forEach((item) => {
    item.addEventListener("mouseover", () => {
      cursor.classList.add("cursor-hover");
    });
    item.addEventListener("mouseleave", () => {
      cursor.classList.remove("cursor-hover");
    });
  });




  document.addEventListener("mousemove", function (e) {
    const x = e.clientX;
    const y = e.clientY;
    cursorSwipe.style.transform = `translate3d(calc(${e.clientX}px - 50%), calc(${e.clientY}px - 50%), 0)`;
  });

  document.addEventListener("mousedown", function () {
    cursorSwipe.classList.add("click");
  });

  document.addEventListener("mouseup", function () {
    cursorSwipe.classList.remove("click");
  });

  hoverSwipe.forEach((item) => {
    item.addEventListener("mouseover", () => {
      cursorSwipe.classList.add("cursor-swipe-hover");
    });
    item.addEventListener("mouseleave", () => {
      cursorSwipe.classList.remove("cursor-swipe-hover");
    });
  });



  //  Sliders
  const photoSlider = document.querySelector('.photo__slider');
  const videoSlider = document.querySelector('.video__slider');
  const sidebarPhotoSlider = document.querySelector('.sidebar__photo-slider');
  const sidebarPhotoSliders = document.querySelectorAll('.sidebar__photo-slider');
  const photoPageSlider = document.querySelector('.photoPage__slider1');
  const photoPageSlider2 = document.querySelector('.photoPage__slider2');
  const photoPageSliders2 = document.querySelectorAll('.photoPage__slider2');

  const bookSlider = document.querySelector('.book-slider');
  const bookSliders = document.querySelectorAll('.book-slider');

  const mobSlider = document.querySelector('._mob-slider');
  const mobSlider3 = document.querySelector('._mob-slider3');


  const photoArticle = document.querySelector('.photoArticle');
  const videoPageSlider = document.querySelector('.videoPage__slider1');
  const videoPageVideos = document.querySelector('.videoPage__videos');


  if (bookSlider) {
    bookSliders.forEach((slider) => {
      tns({
        container: slider,
        slideBy: "page",
        arrowKeys: false,
        controls: false,
        navPosition: "bottom",
        mouseDrag: true,
        loop: false,
        responsive: {
          320: {
            items: 2,
            slideBy: 2,
          },

          768: {
            items: 3,
            slideBy: 3,
          },

          1024: {
            items: 2,
            slideBy: 2,
          },

          1050: {
            items: 3,
            slideBy: 3,
          },
        },
      });
    });
  }


  if (photoSlider) {
    const slider = tns({
      container: ".photo__slider",
      slideBy: "page",
      arrowKeys: true,
      controls: false,
      mouseDrag: true,
      loop: true,
      navPosition: "bottom",

      responsive: {
        320: {
          items: 2,
          slideBy: 2,
          gutter: 16,
          fixedWidth: 260
        },

        768: {
          items: 3,
          slideBy: 3,
          gutter: 22,
          fixedWidth: false
        },
      },
    });
  }


  if (videoSlider) {
    const slider2 = tns({
      container: ".video__slider",
      slideBy: "page",
      arrowKeys: true,
      controls: false,
      mouseDrag: true,
      loop: true,
      navPosition: "bottom",

      responsive: {
        320: {
          items: 2,
          slideBy: 2,
          gutter: 16,
          fixedWidth: 240
        },

        768: {
          items: 3,
          slideBy: 3,
          gutter: 22,
          fixedWidth: false
        },
      },
    });
  }


  if (sidebarPhotoSlider) {
    sidebarPhotoSliders.forEach((slider) => {
      tns({
        container: slider,
        items: 1,
        slideBy: "page",
        arrowKeys: true,
        controls: false,
        mouseDrag: true,
        loop: true,
        navPosition: "bottom",
      });
    });
  }


  // Find adds
  const partnerSlider = document.querySelector('.partners__slider');
  const partnerSliders = document.querySelectorAll('.partners__slider');


  if (partnerSlider) {
    partnerSliders.forEach((slider) => {
      tns({
        container: slider,
        slideBy: "page",
        gutter: 16,
        arrowKeys: true,
        mouseDrag: true,
        loop: true,
        nav: false,
        controls: false,

        responsive: {
          320: {
            items: 2,
            fixedWidth: 260
          },

          768: {
            items: 3,
            fixedWidth: false
          },

          1025: {
            items: 4,
            gutter: 38,
            controls: true,
          },
        },
      });
    });
  }


  if (photoPageSlider) {
    const slider = tns({
      container: photoPageSlider,
      slideBy: "page",
      mouseDrag: true,
      loop: false,
      controls: false,
      nav: false,
      gutter: 16,

      responsive: {
        320: {
          items: 2,
          fixedWidth: 220,
        },

        768: {
          items: 4,
          fixedWidth: 310,
          center: true,
        },
      },
    });
  }


  if (photoPageSlider2) {
    photoPageSliders2.forEach((slider) => {
      tns({
        container: slider,
        slideBy: "page",
        mouseDrag: true,
        loop: true,
        controls: false,
        nav: false,
        gutter: 24,
        rewind: true,

        responsive: {
          320: {
            items: 2,
            fixedWidth: 200,
            edgePadding: 20,
          },

          468: {
            items: 4,
            fixedWidth: 310,
            gutter: 24,
            center: true,
          }
        },
      });
    });
  }


  if (videoPageSlider) {
    const slider = tns({
      container: videoPageSlider,
      slideBy: "page",
      mouseDrag: true,
      loop: true,
      controls: false,
      nav: false,
      rewind: true,
      center: true,
      gutter: 24,

      responsive: {
        320: {
          items: 2,
          fixedWidth: 167,
          center: false
        },

        768: {
          items: 4,
          fixedWidth: 310,
          center: true,
        },
      },
    });
  }


  // Scroll to top
  const scrollToTopBtn = document.querySelector(".scrollToTopBtn");

  function handleScroll() {
    const scrollableHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const GOLDEN_RATIO = 0.1;

    if ((document.documentElement.scrollTop / scrollableHeight ) > GOLDEN_RATIO) {
      if(!scrollToTopBtn.classList.contains("showScrollBtn"))
      scrollToTopBtn.classList.add("showScrollBtn")
    } else if(scrollToTopBtn.classList.contains("showScrollBtn"))
    scrollToTopBtn.classList.remove("showScrollBtn")
  }

  function scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: "smooth"
    });
  }

  document.addEventListener("scroll", handleScroll);
  scrollToTopBtn.addEventListener("click", scrollToTop);


  // copyLink
  const copyLink = document.querySelectorAll(".copyLink");

  function copyUrl() {
    // if (!window.getSelection) {
    //   alert('Please copy the URL from the location bar.');
    //   return;
    // }

    const dummy = document.createElement("p");
    dummy.textContent = window.location.href;
    document.body.appendChild(dummy);

    const range = document.createRange();
    range.setStartBefore(dummy);
    range.setEndAfter(dummy);

    const selection = window.getSelection();

    selection.removeAllRanges();
    selection.addRange(range);

    document.execCommand("copy");
    document.body.removeChild(dummy);
  }


  copyLink.forEach((links) => {
    links.addEventListener("click", (event) => {
      event.preventDefault();
      copyUrl();
      const currentURL = () => window.location.href;

      alert(`Посилання ${currentURL()} скопійовано`);
    });
  });


  // init slider on 768px
  const mediaQuery = window.matchMedia("(max-width: 768px)");

  if (mediaQuery.matches) {

    if (mobSlider) {
      const slider3 = tns({
        container: "._mob-slider",
        items: 2,
        gutter: 22,
        slideBy: 'page',
        arrowKeys: false,
        controls: false,
        nav: false,
        mouseDrag: true,
        loop: false,
        fixedWidth: 250,
        // center: true
      });
    }

    if (mobSlider3) {
      const slider5 = tns({
        container: "._mob-slider3",
        items: 1,
        slideBy: 'page',
        arrowKeys: false,
        controls: false,
        nav: true,
        navPosition: "bottom",
        mouseDrag: true,
        loop: false,
        fixedWidth: 270
      });
    }
  }



  // for demonstrate load more buttons
  const newsLoadMore = document.querySelector(".article-grouped-wrap");

  if (newsLoadMore) {
    const items = Array.from(newsLoadMore.querySelectorAll(".article-grouped"));
    const loadMore = document.querySelector(".load-more-news");
    const maxItems = 2;
    const loadItems = 2;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      // console.log(item.innerText, index);
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  }


  const newsLoadMore2 = document.querySelector(".article-grouped-wrap-3");

  if (newsLoadMore2) {
    const items = Array.from(newsLoadMore2.querySelectorAll(".article-grouped-3"));
    const loadMore = document.querySelector(".load-more-news");
    const maxItems = 3;
    const loadItems = 3;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  }


  const newsLoadMore3 = document.querySelector(".article-grouped-wrap-12");

  if (newsLoadMore3) {
    const items = Array.from(newsLoadMore3.querySelectorAll(".article-grouped-12"));
    const loadMore = document.querySelector(".load-more-news");
    const maxItems = 12;
    const loadItems = 18;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  }


  const newsLoadMore4 = document.querySelector(".article-grouped-wrap-20");

  if (newsLoadMore4) {
    const items = Array.from(newsLoadMore4.querySelectorAll(".article-grouped-20"));
    const loadMore = document.querySelector(".more-video");
    const maxItems = 20;
    const loadItems = 8;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  }



  const newsLoadMore5 = document.querySelector(".article-grouped-wrap-2");

  if (newsLoadMore5) {
    const items = Array.from(newsLoadMore5.querySelectorAll(".article-grouped-2"));
    const loadMore = document.querySelector(".load-more-news");
    const maxItems = 2;
    const loadItems = 2;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  }


  const newsLoadMore8 = document.querySelector(".article-grouped-wrap-3-mob");

  if (newsLoadMore8) {
    const items = Array.from(newsLoadMore8.querySelectorAll(".article-grouped-3-mob"));
    const loadMore = document.querySelector(".load-more-mob");
    const maxItems = 3;
    const loadItems = 3;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  };


  const newsLoadMore9 = document.querySelector(".article-grouped-wrap-6");

  if (newsLoadMore9) {
    const items = Array.from(newsLoadMore9.querySelectorAll(".article-grouped-6"));
    const loadMore = document.querySelector(".btn__more");
    const maxItems = 6;
    const loadItems = 6;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  };




  const newsLoadMore6 = document.querySelector("._rel-article-archive .article-grouped-wrap-2");

  if (newsLoadMore6) {
    const items = Array.from(newsLoadMore5.querySelectorAll(".article-grouped-2"));
    const loadMore = document.querySelector(".load-more-news22");
    const maxItems = 1;
    const loadItems = 1;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  };



  const newsLoadMoreArchive = document.querySelector(".archivePage__search.archivePage__box");
  if (newsLoadMoreArchive) {
    const items = Array.from(
      newsLoadMoreArchive.querySelectorAll(".article-grouped-2")
    );
    const loadMore = document.querySelector(".load-more-news");
    const maxItems = 16;
    const loadItems = 16;
    const hiddenClass = "hiddenStyle";

    items.forEach(function (item, index) {
      if (index > maxItems - 1) {
        item.classList.add(hiddenClass);
      }
    });

    loadMore.addEventListener("click", function () {
      [].forEach.call(
        document.querySelectorAll(`.${hiddenClass}`),
        function (item, index) {
          if (index < loadItems) {
            item.classList.remove(hiddenClass);
          }

          if (document.querySelectorAll(`.${hiddenClass}`).length === 0) {
            loadMore.style.display = "none";
          }
        }
      );
    });
  };


  // end demonstate load more buttons


  // select on archive
  const selectForm = document.querySelector('.dropdown-form');

  if(selectForm) {
    selectFunction()
  }

  const archivePageBtn = document.querySelector('.archivePage__btn');
  const archivePageForm = document.querySelector('.archivePage__form');

  if (archivePageBtn) {
    const openArchiveForm = () => {
      archivePageForm.classList.add("active");
      archivePageBtn.classList.add('active')
    };

    const closeArchiveForm = () => {
      archivePageForm.classList.remove("active");
      archivePageBtn.classList.remove('active')
    };

    archivePageBtn.addEventListener("click", () => {
      if (archivePageForm.classList.contains("active")) {
        closeArchiveForm();
      } else {
        openArchiveForm();
      }
    });

    // Close theme if click outside

    document.addEventListener("click", (e) => {
      const isClickInsideElement = archivePageForm.contains(e.target);

      if (
        archivePageForm.classList.contains("active") && !isClickInsideElement && !e.target.closest('.archivePage__btn')
      ) {
        closeArchiveForm();
      }
    });
  }
});


