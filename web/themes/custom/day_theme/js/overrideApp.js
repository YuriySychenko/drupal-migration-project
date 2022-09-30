(function ($, Drupal) {
  Drupal.behaviors.infiniteScrollVideoTabs = {
    attach: function (context, settings) {

      // Video tags filter
      const videoPageVideos = document.querySelector('.videoPage__videos');

      if (videoPageVideos) {
        const filter = document.querySelector("#filter");
        const filterBtns = [].slice.call(document.querySelectorAll(".filter__btns button"));
        const elements = [].slice.call(filter.children);

        // Not work Array.from in IE11 :ж(((
        // const filterBtns = Array.from(document.querySelectorAll(".filter__btns button"));
        // const elements = Array.from(filter.children);


        function makeChange(result) {
          result.forEach(element => {
            if (element.classList.contains("hide")) {
              element.className = element.className.replace(" hide", "");
            } else if (!element.classList.contains("hide")) {
              element.className += " hide";
            } else if (element.dataset.videoTheme) {
              element.classList.remove("hide");
            }
          });
        }

        function filterCatalogue(e) {
          const result = elements.filter((element) => {
            element.className = element.className.replace("hide", "");
            return e.target.id !== element.dataset.videoTheme;
          });

          makeChange(result);

          if (e.target.id === "all") {
            for (const element of elements) {
              element.classList.remove("hide");
            }
          }
        }

        function currentBtn(e) {
          filterBtns.forEach(button => {
            button.classList.remove("active");
            e.target.classList.add("active")
          });
        }

        function renderClick(e) {
          filterCatalogue(e);
          currentBtn(e);
        }

        filterBtns.forEach((filterBtn => filterBtn.addEventListener("click", renderClick)));
      }
    }
  }

  Drupal.behaviors.cursorApp = {
    attach: function (context, settings) {

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


    }
  }

  Drupal.behaviors.archiveFilter = {
    attach: function (context, settings) {

      function changeArchiveFilterHref(month, year) {
        let archiveLinkFilter = $('#archiveLinkFilter')

        if (month != null) {
          $('.select-year').removeClass('disabled')
          archiveLinkFilter.attr('data-month-id', month).trigger('changeDataArchiveFilterLink');
        }

        if (year != null) {
          archiveLinkFilter.removeClass('disabled')
          archiveLinkFilter.attr('data-year', year).trigger('changeDataArchiveFilterLink');
        }

        archiveLinkFilter.once().on('changeDataArchiveFilterLink', () => {

          const month = archiveLinkFilter.attr('data-month-id')
          const year = archiveLinkFilter.attr('data-year')

          if (month !== '' && year !== '') {
            let currentPath = drupalSettings.path.currentPath

            archiveLinkFilter.attr('href', currentPath + '?archive_date%5Bmin%5D=1.' + month + '.' + year + '&archive_date%5Bmax%5D=31.' + month + '.' + year)
          }
        })
      }

      const selectBlock = $('[data-select]');

      selectBlock.once().each(function () {

        const months = $(this).find('#archiveFilterMonth [data-item="true"]')
        const years = $(this).find('#archiveFilterYear [data-item="true"]')

        months.once().each(function () {
          $(this).on('click', function () {
            const monthId = $(this).data('month-id')

            changeArchiveFilterHref(monthId, null)

          });
        })

        years.once().each(function () {
          $(this).on('click', function () {
            const year = $(this).html()

            changeArchiveFilterHref(null, year)

          });
        })

      })

    }
  }

  // youtube iframe
  Drupal.behaviors.youtubeVideos = {
    attach: function (context, settings) {

      function parseMediaURL(link) {
        const regexp = /https:\/\/youtu\.be\/([a-zA-Z0-9_-]+)/i;
        const url = link.href;
        const match = url.match(regexp);

        return match[1];
      }


      function generateURL(id) {
        const query = '?rel=0&showinfo=0&autoplay=1';

        return `https://www.youtube.com/embed/${id}${query}`;
      }


      function createIframe(id) {
        const iframe = document.createElement('iframe');

        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('allow', 'autoplay');
        iframe.setAttribute('src', generateURL(id));
        iframe.classList.add('video__media');

        return iframe;
      }


      function setupVideo(video) {
        const link = video.querySelector('.video__link');
        // const media = video.querySelector('.video__media');
        const button = video.querySelector('.video__button');
        const id = parseMediaURL(link);

        video.addEventListener('click', () => {
          const iframe = createIframe(id);

          link.remove();
          button.remove();
          video.appendChild(iframe);
        });

        link.removeAttribute('href');
        video.classList.add('video--enabled');
      }

      function findVideos() {
        const videos = document.querySelectorAll('.video');

        for (let i = 0; i < videos.length; i++) {
          setupVideo(videos[i]);
        }
      }

      findVideos()

    }
  }

}(jQuery, Drupal));
