/*global YUI */
YUI.add('gep_event_manager', function(Y) {
  "use strict";
  
  Y.GEP.eventManager = (function() {
    var events = {};

    function getEventName(type, id) {
      if (Y.Lang.isValue(id)) {
        return (id + ":" + type);
      }
      return type;
    }

    function registerEvent(type) {
      events[type] = 1;
    }

    function removeEvent(type) {
      if (events.hasOwnProperty(type)) {
        delete events[type];
      }
    }

    function fire(type, obj, id) {
      obj = obj || {};

      var eventName = getEventName(type, id);

      if (! events[eventName]) {
        return;
      }

      Y.fire(eventName, obj);
    }

    function subscribe(type, callback, id) {
      var eventName = getEventName(type, id);

      if (!events[eventName]) {
        registerEvent(eventName);
      }

      Y.on(eventName, callback);
    }

    function unsubscribe(type, id) {
      var eventName = getEventName(type, id);

      removeEvent(eventName);
    }

    return {
      fire: fire,
      subscribe: subscribe,
      unsubscribe: unsubscribe
    };
  }());
}, '0.0.1', {
  requires: ['event-custom', 'gep']
});
