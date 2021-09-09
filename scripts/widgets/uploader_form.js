/*global YUI */
YUI.add(
'gep_uploader_form',

function(Y) {
  "use strict";

  var uploaderForm = function(fieldID, options) {
    var GEP = Y.GEP,
        Util = GEP.util,
        EM = GEP.eventManager,
        settings,
        uploader,
        postVars;

    settings = Util.mergeSettings(options, {
      uploaderCSS: "uploader",
      uploaderCfg: {
        width: "150px",
        height: "30px",
        multipleFiles: true,
        errorAction: Y.Uploader.Queue.RESTART_AFTER,
        simLimit: 1,
        swfURL:  GEP.data.swfURL,
        uploadURL: GEP.data.uploadURL
      },
      fileDragDropCfg: {
        id: "fileListDDmessage"
      },
      fileExtension: "gff",
      genericExtension: "txt"
    });

    function isFileExtensionValid(fileName) {
      var isVcfFile = fileName.match(/\.vcf\.txt$/),
          extension = (isVcfFile === null) ?
            fileName.substr(fileName.lastIndexOf('.') + 1) : "vcf";

      return ( (extension === settings.fileExtension) ||
               (extension === settings.genericExtension) );
    }

    uploader = new Y.Uploader(settings.uploaderCfg);


    if (Y.Uploader.TYPE === "html5") {
      GEP.fileDragDrop(settings.fileDragDropCfg.id,
                       uploader,
                       settings.fileDragDropCfg);

      Y.all(".html5uploader").removeClass("hidden");
    }

    EM.subscribe("changeFileType", function(event) {
      settings.fileExtension = event.fileExtension;
      uploader.set("fileList", []);
    });

    uploader.after("fileselect", function(event) {
      var fileList = event.fileList,
          skippedFileList = [],
          filteredFileList = [],
          errorMessages = [];

      EM.fire("resetMessage");

      Y.each(fileList, function(newFile) {
        var newFileName = newFile.get("name");

        if (isFileExtensionValid(newFileName)) {
          filteredFileList.push(newFile);
        } else {
          skippedFileList.push(newFileName);
        }
      });

      if (skippedFileList.length > 0) {
        errorMessages.push("Expected file extension: <b>" + settings.fileExtension +
           "</b>. The following files with invalid extensions have been skipped:");

        errorMessages.push(skippedFileList.join(", "));
      }

      if (filteredFileList.length === 0) {
        errorMessages.unshift("None of the selected files passed the filter.");
      } else {
        EM.fire("addFiles",
          { fileList: filteredFileList });
      }

      if (errorMessages.length > 0) {
        EM.fire("showMessage",
                { type: "error", message: errorMessages.join("<br>") });
      }
    });

    uploader.render("#" + fieldID);

    uploader.on("uploadstart", function() {
      uploader.set("enabled", false);

      EM.fire("resetMessage");
    });

    function requestStatusCompleted(id, response, args) {
      try {
        var results = Y.JSON.parse(response.responseText);

        if (results.status === "failure") {
          throw new Error("Upload error: " + results.message);
        }

        uploader.set("enabled", true);
        uploader.set("fileList", []);

        EM.fire("showMergeResults", results);

      } catch (e) {
        EM.fire("showMessage", {
          type: "error",
          message: e.message || "The GEP service is down. Please try again later.",
          transaction: {
            id: id,
            args: args
          }
        });
      }
    }

    function postVarsToString(postVariables) {
      var str = [],
          item;

      for (item in postVariables) {
        if (postVariables.hasOwnProperty(item)) {
          str.push(item + "=" + postVariables[item]);
        }
      }

      return str.join("&");
    }

    uploader.on("alluploadscomplete", function() {
      Y.on('io:complete', requestStatusCompleted);

      postVars.checkStatus = true;

      Y.io(settings.uploaderCfg.uploadURL, {
        method: "POST",
        data: postVarsToString(postVars)
      });
    });

    uploader.on("uploadprogress", function (event) {
      EM.fire("uploadInProgress", event);
    });

    uploader.on("uploadcomplete", function (event) {
      try {
        var results = Y.JSON.parse(event.data);

        if (results.status === "failure") {
          throw new Error("Upload error: " + results.message);
        }

        EM.fire("uploadFinished", event);

      } catch (e) {
        EM.fire("showMessage", {
          type: "error",
          message: e.message || "The GEP service is down. Please try again later."
        });
      }
    });

    EM.subscribe("uploadFiles", function(event) {
      uploader.set("fileList", event.fileList);

      event.postVars.uploadType = Y.Uploader.TYPE;

      postVars = event.postVars;

      if (uploader.get("fileList").length > 0) {
        uploader.uploadAll(GEP.data.uploadURL, event.postVars);

      } else {
        EM.fire("showMessage", { type: "error", message: "No files to merge" });
      }
    });

    return uploader;
  };

  Y.namespace("GEP").uploaderForm = uploaderForm;
},
"0.0.1", {
  requires: ["gep_event_manager", "uploader", "json"]
});
