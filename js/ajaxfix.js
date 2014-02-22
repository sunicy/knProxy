/*kn$proxy = {
  proto: window.location.protocol,
  host: window.location.host,
  pathname: window.location.pathname,
  port: window.location.port,
  origin: window.location.origin,
  hostname: window.location.hostname,
  href: window.location.href
};*/
var oldOpen= XMLHttpRequest.prototype.open;
XMLHttpRequest.prototype.open = function(method, url, async, username, password){
  console.log("OPEN");
  var o = _kn$origin;
  var remoteUrl = o.proto + "//" + o.host;
  if (url.length == 0 || url.charAt(0) == "?")
    remoteUrl += o.path + o.file + url;
  else if (url.charAt(0) === "/")
    remoteUrl += url;
  else
    remoteUrl += o.path + url;
  var newUrl = window.location.pathname + "?url=" + remoteUrl;
  newUrl = escape(newUrl);
	oldOpen.call(this, method, newUrl, async, username, password);
}

