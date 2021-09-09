/*global YUI */
YUI().use(
  'transition',
  'gep_upload_form_module',
  'gep_uploader_module',
  'gep_results_panel',

function(Y) {
  "use strict";

  var GEP = Y.GEP,
      EM = GEP.eventManager;

  function initUploadForm() {
    var form;

    if (Y.Uploader.TYPE === "none" || Y.UA.ios) {
      form = GEP.uploadFormModule();
    }  else {
      form = GEP.uploaderModule();
    }

    return form;
  }

  function initUploadConfigPanel() {
    EM.subscribe("showMergeResults", function() {
      Y.byID("basicUploadSection").hide(true);
    });
  }

  function init() {
    GEP.messagePanel("systemMessages");
    initUploadForm();
    GEP.resultsPanel("resultsPanel");
    initUploadConfigPanel();
  }

  init();
});
