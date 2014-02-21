var oldOpen= XMLHttpRequest.prototype.open;
XMLHttpRequest.prototype.open = function(method, url, async, username, password){
  console.log(url);
  var o = _kn$origin;
  var remoteUrl = o.proto + "//" + o.host;
  if (url.length == 0 || url.charAt(0) == "?")
    remoteUrl += o.path + o.file + url;
  else if (url.charAt(0) === "/")
    remoteUrl += url;
  else
    remoteUrl += o.path + url;
  var newUrl = "/index.php?url=" + remoteUrl;
	oldOpen.call(this, method, newUrl, async, username, password);
}
