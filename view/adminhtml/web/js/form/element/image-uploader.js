define([
  'Magento_Ui/js/form/element/image-uploader',
  'jquery',
  'mage/adminhtml/browser',
  'mageUtils'
], function (Element, $, browser) {

  /**
   * This component was created just only to provide
   * relative path of the selected image to the field
   */
  return Element.extend({
    defaults: {
      browserOptions: undefined
    },

    /**
     * @inheritDoc
     */
    addFileFromMediaGallery: function (imageUploader, e) {
      var $buttonEl = $(e.target),
        fileSize = $buttonEl.data('size'),
        fileMimeType = $buttonEl.data('mime-type'),
        filePathname = $buttonEl.val(),
        fileBasename,
        fileRelativePathname;

      if (filePathname.indexOf('base64,') === 0) {
        var fileData = JSON.parse(
          Base64.decode(
            $buttonEl.val().slice('base64,'.length)
          )
        );

        filePathname = fileData.url;
        fileRelativePathname = fileData.relative_path;
      } else {
        fileRelativePathname = filePathname;
      }

      fileBasename = filePathname.split('/').pop();

      this.addFile({
        type: fileMimeType,
        name: fileBasename,
        size: fileSize,
        url: filePathname,
        relative_path: fileRelativePathname
      });
    },

    /**
     * @inheritDoc
     */
    openMediaBrowserDialog: function (imageUploader, e) {
      this._super(imageUploader, e);

      var browserOptions = this.browserOptions;

      // we would like to change options in the media browser without
      // overriding phtml of the component, so here is a great crutch!
      browser.modal.one(
        'contentUpdated',
        function (e) {
          var $browser = $('.media-gallery-modal', arguments[0].target);
          var browserConfig = $browser.data('mage-init');
          if (browserConfig) {
            $browser.attr(
              'data-mage-init',
              JSON.stringify(
                $.extend(true, browserConfig, {mediabrowser: browserOptions})
              )
            );
          }
        }
      )
    }
  })
})
