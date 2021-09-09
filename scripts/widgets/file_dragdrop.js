/*global YUI */
YUI.add(
'gep_file_dragdrop',

function(Y) {
  "use strict";
  
  var fileDragDrop = function(fieldID, uploader, options) {
    var GEP = Y.GEP,
        Util = GEP.util,
        settings,
        ddField = Y.byID(fieldID);

    settings = Util.mergeSettings(options, {
      highlightCSS: "highlightRegion",
      instruction: "Drag and drop the files you want to merge here",
      dragDetectedMessage: "Drop the selected files here.",
      dropTarget: "body"
    });

    uploader.set("dragAndDropArea", settings.dropTarget);

    ddField.setContent(settings.instruction);

    function reset() {
      ddField.setContent(settings.instruction);
      ddField.removeClass(settings.highlightCSS);
    }

    function showDropMessage() {
      ddField.setContent(settings.dragDetectedMessage);
      ddField.addClass(settings.highlightCSS);
    }

    uploader.on(["dragenter", "dragover"], showDropMessage);
    uploader.on(["dragleave", "drop"], reset);

    uploader.on("fileselect", reset);

    return ddField;
  };

  Y.namespace("GEP").fileDragDrop = fileDragDrop;
},
"0.0.1", {
  requires: ["gep_event_manager", "uploader"]
});
