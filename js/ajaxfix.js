if (typeof oldOpen === "undefined")
  oldOpen = XMLHttpRequest.prototype.open;
XMLHttpRequest.prototype.open = function(method, url, async, username, password){
  console.log("ajax:"+url);
  var o = _kn$origin;
  var remoteUrl = o.proto + "//" + o.host;
  if (url.length == 0 || url.charAt(0) == "?")
    remoteUrl += o.path + o.file + url;
  else if (url.charAt(0) === "/")
    remoteUrl += url;
  else
    remoteUrl += o.path + url;
  var newUrl = //window.location.protocol + "//" + 
    //window.location.host +
    window.location.pathname + "?____url=" + remoteUrl;
  if (url.search("____url") >= 0) {
    newUrl = url; // it's our friend, pass!
    console.log("Url not changed.");
  }
  console.log("ajax-new: " + newUrl);
  return oldOpen.call(this, method, newUrl, async, username, password);
}
