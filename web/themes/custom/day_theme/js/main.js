(function ($, Drupal) {
  Drupal.behaviors.dayTheme = {
    attach: function (context, settings) {

      // Fix for sticky class parents overflow.
      let fixedBlock = $('.sticky')

      // if ($(window).width() > 1023) {
      //   $(window).scroll(function () {
      //     let currentScroll = $(window).scrollTop();
      //     let offSetFixedBlock = ''
      //
      //     if (fixedBlock.length) {
      //       offSetFixedBlock = fixedBlock.offset().top
      //     }

          // if (currentScroll >= (offSetFixedBlock - fixedBlock.height() * 2)) {
          //   fixedBlock.parent().css('overflow', 'visible')
          //   $('main.main').css('overflow', 'visible')
          // } else {
          //   fixedBlock.parent().css('overflow', 'hidden')
          //   $('main.main').css('overflow', 'hidden')
          // }
      //   });
      // }

      // Wrap all photo reports images to Lightbox gallery.
      let photoArticleImgs = $('article.photoArticle__box.with-gallery img').not('.photoArticle__photo img').not('.photoArticle__gallery img')

      photoArticleImgs.once().each(function () {
        let photoArticleImgSrc = $(this).attr('src')

        $(this).wrap(`<a href="${photoArticleImgSrc}" class="lightbox" data-group="gallery1">`);

        let reInitTobii = new Tobii({
          captionText: true,
          zoom: false,
        })

      })

      // Unwrap longread images.
      $('.longread p a.lightbox').unwrap();

    }
  }
}(jQuery, Drupal));
