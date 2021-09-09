/*global YUI */
YUI.add('gep_message_panel',

function(Y) {
  "use strict";
  
  var messagePanel = function(fieldID, options) {
    var GEP = Y.GEP,
        Util = GEP.util,
        EM = GEP.eventManager,
        settings,
        panel;

    settings = Util.mergeSettings(options, {
      status: ["success", "error", "warning"],
      messageCSS: "message",
      hiddenCSS: "hidden"
    });

    panel = Y.byID(fieldID);

    panel.addClass(settings.messageCSS);
    panel.hide();

    function setStyle(newStyle) {
      Y.each(settings.status, function(s) {
        panel.removeClass(s);
      });

      panel.addClass(newStyle);
      panel.show();
    }

    EM.subscribe("resetMessage",
      function() {
        panel.hide();
    });

    EM.subscribe("showMessage",
      function(e) {
        setStyle(e.type);
        panel.setContent(e.message);
    });

    return panel;
  };

  Y.namespace("GEP").messagePanel = messagePanel;
},

"0.0.1", {
  requires: ["gep_event_manager"]
});
