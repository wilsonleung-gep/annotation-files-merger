/*global YUI */
YUI.add('gep_upload_form_module',
function(Y) {
  "use strict";

  var uploadFormModule = function(options) {
    var GEP = Y.GEP,
        Util = GEP.util,
        EM = GEP.eventManager,
        settings,
        form;

    settings = Util.mergeSettings(options, {
      fieldLimits: { min: 1, max: 50 },
      service: GEP.data.uploadURL,
      formID: "uploadForm",
      numFieldsID: "numFiles",
      uploadButtonID: "uploadFilesButton"
    });

    form = GEP.multiUploadForm(settings.formID, settings.service);

    Y.byID(settings.numFieldsID).on("change",
      function(event) {
        var count = parseInt(event.target.get("value"), 10);

        if (Y.Lang.isNumber(count) &&
            count >= settings.fieldLimits.min &&
            count <= settings.fieldLimits.max) {

          EM.fire("resetMessage");

          EM.fire("updateFilesCount", {
            numFields: count
          });

        } else {
          EM.fire("showMessage", {
            type: "error",
            "message": Y.Lang.substitute(
              "Error: Number of file field should be between {min} and {max}",
              settings.fieldLimits)
          });
        }
    });

    Y.byID(settings.uploadButtonID).on("click", function() {
      EM.fire("uploadFiles");
    });

    return form;
  };

  Y.namespace("GEP").uploadFormModule = uploadFormModule;
},
"0.0.1", {
  requires: ["gep_message_panel", "gep_multi_upload_form"]
});
