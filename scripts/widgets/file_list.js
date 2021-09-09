/*global YUI */
YUI.add(
'gep_file_list',

function(Y) {
  "use strict";

  var fileList = function(fieldID, options) {
    var GEP = Y.GEP,
        Util = GEP.util,
        EM = GEP.eventManager,
        settings,
        fileListField = Y.byID(fieldID),
        emptyRow = fieldID + "-empty",
        tableID = fieldID + "-table",
        fileTable = Y.one("#" + tableID + " tbody"),
        filesToUpload = [];

    settings = Util.mergeSettings(options, {
      rowTpl: "<tr id='{id}_row'>"+
        "<td class='filename'>{name}</td><td class='filesize'>{size}</td>"+
        "<td class='percentdone'>Queued</td>",

      emptyMsg: "<tr id='"+emptyRow+"'><td colspan='3'>No files selected</td></tr>"
    });

    fileTable.append(settings.emptyMsg);

    function updateRowStatus(event, message) {
      var fileRow = Y.byID(event.file.get("id") + "_row");

      if (fileRow) {
        fileRow.one(".percentdone").set("text", message);
      }
    }

    EM.subscribe("addFiles", function(filesInfo) {
      var filesToAdd = filesInfo.fileList;

      if (filesToAdd.length > 0 && Y.byID(emptyRow)) {
        Y.byID(emptyRow).remove(true);
      }

      Y.each(filesToAdd, function (newFile) {
        fileTable.append(Y.Lang.sub(settings.rowTpl,
                                           newFile.getAttrs(["id", "name", "size"])));

        filesToUpload.push(newFile);
      });
    });

    EM.subscribe("showMergeResults", function() {
      fileTable.setContent(settings.emptyMsg);
    });

    EM.subscribe("uploadInProgress", function(event) {
      updateRowStatus(event, event.percentLoaded + "%");
    });

    EM.subscribe("uploadFinished", function(event) {
      updateRowStatus(event, "Completed");
    });

    EM.subscribe("uploadError", function(event) {
      updateRowStatus(event, "Upload failed: " + event.statusText);
    });

    EM.subscribe("changeFileType", function() {
      filesToUpload = [];
      fileTable.setContent(settings.emptyMsg);
    });

    EM.subscribe("filterFileList", function(args) {
      EM.fire("uploadFiles", {
        event: args.event,
        postVars: args.postVars,
        fileList: filesToUpload
      });
    });

    return fileListField;
  };

  Y.namespace("GEP").fileList = fileList;
},
"0.0.1", {
  requires: ["gep_event_manager", "uploader"]
});
