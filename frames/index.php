<?php
include_once('knproxy_i18n.php');
include_once('../conf.php');
include_once('../includes/general_functions.php');
$_LANG = KNPROXY_LANGUAGE;
$_KNPROXY_NAVIGATOR_PAGE='navigator_bar.php';

$url_para = (isset($_GET["____url"])) ? "?____url=".$_GET["____url"] : "";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
<?php
if (isset($_GET["____clear_cookies"]))
	clearcookies();
?>
<script language="javascript" type="text/javascript">
//<!--
function url_change(URL){fixed.KN_BFORM.url.value=URL;}
//-->
</script>
<TITLE><?php echo knproxy_i18n('title',$_LANG)?></TITLE>
</HEAD>
<FRAMESET rows="45,*" FRAMEBORDER="0" FRAMESPACING="0" BORDER="0" name="KNBROWSER_MAIN">
  <FRAME name="fixed" src="<?php echo $_KNPROXY_NAVIGATOR_PAGE.$url_para;?>" marginwidth="2" marginheight="6" frameborder="0" noresize scrolling="no" >
  <FRAME name="dynamic" src="dynami_index.php" frameborder="0" marginwidth="5" marginheight="5" >
</FRAMESET>
</HTML>
