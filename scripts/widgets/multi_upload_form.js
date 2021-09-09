/*global YUI */
YUI.add(
'gep_multi_upload_form',

function(Y) {
  "use strict";

  var multiUploadForm = function(fieldID, service, options) {
    var GEP = Y.GEP,
        Util = GEP.util,
        EM = GEP.eventManager,
        settings,
        form,
        inputTpl;

    settings = Util.mergeSettings(options, {
      numFilesField: "numFiles",
      template: '<label>File {idx}:</label>' +
                '<input name="{name}" type="file" size=50" class="{fileCSS}">',
      fileID: "ufile",
      fileCSS: "ufiles",
      fileUploadFields: "fileUploadFields",
      errorCSS: "error",
      formCfg: {
        method: "POST",
        form: {
          id: fieldID,
          upload: true
        }
      }
    });

    form = Y.byID(fieldID);

    inputTpl = Y.Lang.sub(settings.template, {
      name: settings.fileID + "[]",
      fileCSS: settings.fileCSS
    });

    function completeUpload(id, o, args) {
      try {
        var json = Y.JSON.parse(o.responseText);
        EM.fire("showMergeResults", json);

      } catch (e) {
        EM.fire("showMessage",
          { 
            type: "error", 
            message: e.message || e,
            transaction: {
              id: id, 
              args: args
            }
        });
      }
    }

    Y.on("io:complete", completeUpload);

    function hasEmptyFields() {
      var fields = Y.all("." + settings.fileCSS),
          isEmpty;

      if (fields.isEmpty()) {
        throw "No file fields matched selector";
      }

      fields.removeClass(settings.errorCSS);

      isEmpty = fields.some(function(f) {
        if (f.get("value") === "") {
          f.addClass(settings.errorCSS);
          return true;
        }

        return false;
      });

      return isEmpty;
    }

    EM.subscribe("uploadFiles", function() {
      var data;

      if (hasEmptyFields()) {
        EM.fire("showMessage", {
          type: "error",
          message: "File upload fields cannot be empty"
        });

        return;
      }

      data = {
        method: settings.formCfg.method,
        form: settings.formCfg.form
      };

      Y.io(service, data);
    });

    EM.subscribe("updateFilesCount", function(e) {
      var i,
          uploadFields = [];

      for (i=0; i<e.numFields; i+=1) {
        uploadFields.push(Y.Lang.sub(inputTpl, { idx: i+1 }));
      }

      Y.byID(settings.fileUploadFields).setContent(
        "<li>" + uploadFields.join("</li><li>") + "</li>");
    });

    EM.fire("showMessage", {
      type: "warning",
      message:
        'To enable multiple files selection and upload progress tracking, please ' +
        'use a more <a href="https://caniuse.com/fileapi">modern web browser</a>.'
    });

    return form;
  };

  Y.namespace("GEP").multiUploadForm = multiUploadForm;
},
"0.0.1", {
  requires: ["gep_event_manager", "io-upload-iframe"]
});
