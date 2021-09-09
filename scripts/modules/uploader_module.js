/*global YUI */
YUI.add('gep_uploader_module',
function(Y) {
  "use strict";
  
  var uploaderModule = function(options) {
    var GEP = Y.GEP,
        Util = GEP.util,
        EM = GEP.eventManager,
        settings,
        uploader;

    settings = Util.mergeSettings(options, {
      uploaderID: "selectFilesButtonContainer",
      uploaderType: Y.Uploader.TYPE,
      fileListID: "filelist",
      fileTypeID: "fileType",
      mergeButtonID: "uploadFilesButton",
      simpleUploadCSS: "simpleuploader",
      uploadCSS: "uploader"
    });

    function initFileTypeSelectBox() {
      var selectBox = Y.byID(settings.fileTypeID);

      EM.fire("changeFileType", { fileExtension: selectBox.get("value") });

      selectBox.on("change", function() {
        EM.fire("changeFileType", { fileExtension: selectBox.get("value") });
      });

      return selectBox;
    }

    uploader = GEP.uploaderForm(settings.uploaderID,
                                settings.uploaderType);

    function init() {
      GEP.fileList(settings.fileListID);
      GEP.mergeButton("uploadFilesButton");
      initFileTypeSelectBox();
    }

    init();

    Y.all("." + settings.simpleUploadCSS).addClass("hidden");
    Y.all("." + settings.uploadCSS).removeClass("hidden");

    return uploader;
  };

  Y.namespace("GEP").uploaderModule = uploaderModule;
},
"0.0.1", {
  requires: ["gep_message_panel",
             'gep_uploader_form', 'gep_file_dragdrop',
             'gep_file_list', 'gep_merge_button']
});
