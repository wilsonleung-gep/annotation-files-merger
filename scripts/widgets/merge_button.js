/*global YUI*/
YUI.add('gep_merge_button',
function(Y) {
  "use strict";
  
    var mergeButton = function(fieldID, options) {
    var GEP = Y.GEP,
        Util = GEP.util,
        EM = GEP.eventManager,
        settings,
        button;

      settings = Util.mergeSettings(options, {
        postVarsFields: ["fileType", "sessionid"]
      });

      button = Y.byID(fieldID);
      button.set("disabled", true);

      function buildPostVar() {
        var pv = {};

        Y.each(settings.postVarsFields, function(fieldName) {
          pv[fieldName] = Y.byID(fieldName).get("value");
        });

        return pv;
      }

      button.on("click", function(event) {
        var postVars = buildPostVar();

        EM.fire("filterFileList", {
          event: event,
          postVars: postVars
        });

        button.set("disabled", true);
      });

      EM.subscribe("showMergeResults", function() {
        button.set("disabled", false);
      });

      EM.subscribe("addFiles", function() {
        button.set("disabled", false);
      });

      return button;
    };

  Y.namespace("GEP").mergeButton = mergeButton;
},
"0.0.1", {
  requires: ["gep_event_manager"]
});
