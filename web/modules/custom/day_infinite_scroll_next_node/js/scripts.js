(function ($, Drupal) {
  Drupal.behaviors.dayInfiniteScrollNextNode = {
    attach: function attach(context) {

      // Auto load next post.
      $(document).ready(function () {
        let $infinitePosts = $('.infinite-scroll-feed').infiniteScroll({
          path: function () {
            return $('.infinite-next-node-url').data('infinite-next-node-url')
          },
          append: '.categoryArticle__box',
          historyTitle: true,
          button: '.load-more-news',
          status: '.scroller-status'
        });

        let $loadMoreButton = $('.load-more-news')

        // get Infinite Scroll instance
        let infScroll = $infinitePosts.data('infiniteScroll');

        $infinitePosts.on('load.infiniteScroll', onPageLoad);

        function onPageLoad() {
          // console.log('infScroll.loadCount: ', infScroll.loadCount)
          if (infScroll.loadCount === 1) {
            // after 2nd page loaded
            // disable loading on scroll
            $infinitePosts.infiniteScroll('option', {
              loadOnScroll: false,
            });
            // show button
            $loadMoreButton.show();
            // remove event listener
            $infinitePosts.off('load.infiniteScroll', onPageLoad);
          }
        }

        $infinitePosts.once().on('append.infiniteScroll', function (event, body, path, items, response) {
          let nextNodeId = items[0].dataset.nextNodeId

          $.ajax({
            url: '/load-next-node/' + nextNodeId,
            success: function (next_node_url) {
              if (next_node_url) {
                $('.infinite-next-node-url').replaceWith('<div class="infinite-next-node-url" data-infinite-next-node-url="' + next_node_url + '"></div>')

                $infinitePosts.infiniteScroll('option', {
                  button: '.load-more-news',
                  scrollThreshold: false,
                });
              } else {
                // $infinitePosts.infiniteScroll('destroy')
                $('.infinite-next-node-url').remove()
                $('.load-more-news').remove()
              }
            }
          });

        });
      });

    }
  }
}(jQuery, Drupal));
