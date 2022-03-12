/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

(function ($, Drupal, CKEDITOR) {
  CKEDITOR.plugins.add('day_blockquote', {
    init: function (editor) {

      editor.addCommand('create_day_blockquote', {
        exec: function (editor) {
          var selectedHtml = "";
          var selection = editor.getSelection();
          if (selection) {
            selectedHtml = getSelectionHtml(selection);
          }

          editor.insertHtml('<blockquote><span>' + selectedHtml + '</span></blockquote>');
        }
      });

      editor.ui.addButton('day_blockquote', {
        label: Drupal.t("Day blockquote"),
        command: 'create_day_blockquote',
        toolbar: 'insert',
        icon: this.path + 'images/icon.png'
      });

    }
  });

  /**
   Get HTML of a selection.
   */
  function getSelectionHtml(selection) {
    var ranges = selection.getRanges();
    var html = '';
    for (var i = 0; i < ranges.length; i++) {
      var content = ranges[i].extractContents();
      html += content.getHtml();
    }
    return html;
  }
})(jQuery, Drupal, CKEDITOR);
