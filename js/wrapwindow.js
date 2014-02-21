/*
 * Created by Sunicy.Tao
 * Mar. 2nd, 2014
 */

var p$ = {
  // utils
  //
  is: function (type, obj) {
      var clas = Object.prototype.toString.call(obj).slice(8, -1);
      return obj !== undefined && obj !== null && clas === type;
  },
  
  isFunction: function (f) {
    return p$.is('Function', f);
  },
  
  defValIfUndefined: function (v, defVal) {
    return (typeof(v) === "undefined") ? defVal : v;
  }
};

var p$FakeLocation = function(fakeWindow, oldWindow) {
  var self = this;

  return self;
};

var p$FakeWindow = function(oldWindow) {
  var self = this;
  o = oldWindow; // oldWindow!
  var location = new p$FakeLocation(self, oldWindow);

  // Helper functions for binding/exporting properties/methods
  self.bindDirectMethod = function(obj, name, originObj, originName) {
    originObj = p$.defValIfUndefined(originObj, o);
    originName = p$.defValIfUndefined(originName, name);

    obj[name] = (function(obj, method) {
      function callF() {
        return obj[method].apply(obj, arguments);
      }
      return callF;
    })(originObj, originName);
  };

  self.bindDirectProperty = function(obj, name, originObj, originName) {
    originObj = p$.defValIfUndefined(originObj, o);
    originName = p$.defValIfUndefined(originName, name);

    Object.defineProperty(obj, name, {
      get: (function(obj, prop) {
        function getter() {
          return obj[prop];
        }
        return getter;
      })(originObj, originName),
      set: (function(obj, prop) {
        function setter(newVal) {
          console.log("setter fired. newVal="+newVal);
          obj[prop] = newVal;
        }
        return setter;
      })(originObj, originName)
    });
  };

  self.bindCustomProperty = function(obj, name, getter, setter) {
    Object.defineProperty(obj, name, {
      get: getter,
      set: setter
    });
  };

  function bindNow() {
    var method = function(names) {
      for (var i = 0, l = names.length; i < l; i++)
        self.bindDirectMethod(self, names[i]);
    };
    var prop = function(names) {
      for (var i = 0, l = names.length; i < l; i++)
        self.bindDirectProperty(self, names[i]);
    };
    // Methods from window object
    var functions = [
      "postMessage", "close", "blur", "focus", "getSelection", "print",
      "stop", "open", "showModalDialog", "alert", "confirm", "prompt",
      "find", "scrollBy", "scrollTo", "scroll", "moveBy", "moveTo", "resizeBy",
      "resizeTo", "matchMedia", "requestAnimationFrame", "cancelAnimationFrame",
      "webkitRequestAnimationFrame", "webkitCancelAnimationFrame",
      "webkitCancelRequestAnimationFrame", "captureEvents", "releaseEvents",
      "atob", "btoa", "setTimeout", "clearTimeout", "setInterval",
      "clearInterval", "getComputedStyle", "getMatchedCSSRules",
      "webkitConvertPointFromPageToNode", "webkitConvertPointFromNodeToPage",
      "webkitRequestFileSystem", "webkitResolveLocalFileSystemURL",
      "openDatabase", "addEventListener", "removeEventListener",
      "dispatchEvent"
    ];
    method(functions);

    // Properties from window object
    var properties = [
      /*"top"*//*, "window"*//*, "location"*/, "external", "chrome", "document",
      "speechSynthesis", "webkitNotifications", "localStorage",
      "sessionStorage", "applicationCache", "webkitStorageInfo",
      "indexedDB", "webkitIndexedDB", "crypto", "CSS", "performance",
      "console", "devicePixelRatio", "styleMedia", "parent", "opener",
      "frames"/*, "self"*/, "defaultstatus", "defaultStatus", "status", "name",
      "length", "closed", "pageYOffset", "pageXOffset", "scrollY", "scrollX",
      "screenTop", "screenLeft", "screenY", "screenX", "innerWidth",
      "innerHeight", "outerWidth", "outerHeight", "offscreenBuffering",
      "frameElement", "clientInformation", "navigator", "toolbar", "statusbar",
      "scrollbars", "personalbar", "menubar", "locationbar", "history",
      "screen", "ondeviceorientation", "ondevicemotion", "onunload",
      "onstorage", "onresize", "onpopstate", "onpageshow", "onpagehide",
      "ononline", "onoffline", "onmessage", "onhashchange", "onbeforeunload",
      "onwaiting", "onvolumechange", "ontimeupdate", "onsuspend", "onsubmit",
      "onstalled", "onshow", "onselect", "onseeking", "onseeked", "onscroll",
      "onreset", "onratechange", "onprogress", "onplaying", "onplay",
      "onpause", "onmousewheel", "onmouseup", "onmouseover", "onmouseout",
      "onmousemove", "onmouseleave", "onmouseenter", "onmousedown",
      "onloadstart", "onloadedmetadata", "onloadeddata", "onload", "onkeyup",
      "onkeypress", "onkeydown", "oninvalid", "oninput", "onfocus", "onerror",
      "onended", "onemptied", "ondurationchange", "ondrop", "ondragstart",
      "ondragover", "ondragleave", "ondragenter", "ondragend", "ondrag",
      "ondblclick", "oncuechange", "oncontextmenu", "onclose", "onclick",
      "onchange", "oncanplaythrough", "oncanplay", "oncancel", "onblur",
      "onabort", "onwheel", "onwebkittransitionend", "onwebkitanimationstart",
      "onwebkitanimationiteration", "onwebkitanimationend",
      "ontransitionend", "onsearch", "TEMPORARY", "PERSISTENT"
    ];
    prop(properties);

    // Bind fake window to itself!
    self.bindCustomProperty(self, 'window', function() {
      return self;
    }, function() {
      // ReadOnly!
    });
    self.bindCustomProperty(self, 'self', function() {
      return self;
    }, function() {
      // ReadOnly!
    });
    // Shhhhh, don't tell the page that his in a frame!
    self.bindCustomProperty(self, 'top', function() {
      return self;
    }, function() {
      // ReadOnly!
    });
    // Bind fake window.location!
    self.bindCustomProperty(self, 'location', function() {
      console.log("window.location is requested to get...");
      return location;
    }, function(newLoc) {
      console.log("window.location="+newLoc);
    });
  }

  bindNow();
  return self;
};

fakeWindow = p$FakeWindow();
