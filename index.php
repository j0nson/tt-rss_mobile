<?php
//get preferences
$pref_Feed = "-3"; //Default: fresh feed
$pref_Article = 'unread'; //default: unread articles only
$pref_number = 30; //TODO: add a dropdown selector to menu
$pref_textType = "content"; //default: full articles
$pref_attachments = 0; //TODO: add this option to the menu.  Actually, I'm not sure anyone would use this.
$login = 0;//indicator for log in status
$error = '';
if ( isset($_COOKIE['mobile_ttrss_number']) ){
	$pref_number = $_COOKIE['mobile_ttrss_number'];
}
if ( isset($_COOKIE['mobile_ttrss_textType']) ){
	$pref_textType = $_COOKIE['mobile_ttrss_textType'];
}
if ( isset($_COOKIE['mobile_ttrss_attachments']) ){
	$pref_attachments = $_COOKIE['mobile_ttrss_attachments'];
}
if ( isset($_COOKIE['mobile_ttrss_feed']) ){
	$pref_Feed = $_COOKIE['mobile_ttrss_feed'];
} 
if ( isset($_COOKIE['mobile_ttrss_article']) ){
	$pref_Article = $_COOKIE['mobile_ttrss_article'];
}
//override unread for special feeds
if ($pref_Feed == "-2" || $pref_Feed == "-1"){
	$pref_Article = "all";
}

//get session id
if ( ! isset($_COOKIE['mobile_ttrss_sid']) && ! isset($_POST["username"]) ){
	$login = 1;
} elseif ( ! isset($_COOKIE['mobile_ttrss_sid']) && isset($_POST["username"]) ) {
	$data = json_decode(get('{"op":"login","user":"' . $_POST["username"] . '","password":"' . $_POST["password"] . '"}'), TRUE);
	//print_r ($data);  //debugging logins
	$sessionID = $data['content']['session_id'];
	setcookie('mobile_ttrss_sid',$sessionID,time() + (86400 * 30)); // 86400 = 1 day
}else{
	$sessionID = $_COOKIE['mobile_ttrss_sid'];
}

if (isset($_GET['cmd']) && $_GET['cmd'] == "markRead"){
	$json = get('{"sid":"' . $sessionID . '","op":"updateArticle","article_ids":"' . $_GET['ids'] . '","mode":"0","field":"2"}');
	//print '<script type="text/javascript">window.location = "' . $_SERVER['PHP_SELF'] . '"</script>';
	header('Location: ' . $_SERVER['PHP_SELF'] );
}

?>
<html>
<head>
<title>RSS Mobile</title>
<link rel="shortcut icon" href="../images/favicon.png"> 
<meta name = "viewport" content = "initial-scale = 1, user-scalable = yes">
<script>
articleId_keeper=0;
articleOpen_keeper=0;
function showSpinner(option){
	if ( option ){
		document.getElementById("spinner").style.top = document.body.scrollTop + "px"; 
		document.getElementById("spinner").style.display = "block"; 
	}else{
	 	document.getElementById("spinner").style.display = "none";
	} 
}
function toggleStar(id, sid){
	data='{"sid":"' + sid + '","op":"updateArticle","article_ids":"' + id + '","mode":"2","field":"0"}';
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("POST", "../api/");
	xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
	xmlhttp.send(data);
	if (document.getElementById('star_'+id).className == "starOn"){
		document.getElementById('star_'+id).className = "starOff";
	}else{
		document.getElementById('star_'+id).className = "starOn";
	}
}
function updateFeed(id, sid){
	document.getElementById("updateFeed").style.backgroundPosition = "0 0"; 
	document.getElementById("updateFeed").style.backgroundImage = "url('../images/indicator_white.gif')";
	data='{"sid":"' + sid + '","op":"updateFeed","feed_id":"' + id + '"}';
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("POST", "../api/");
	xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
	xmlhttp.send(data);
} 

function toggleMenu(){
	var menuDiv = document.getElementById('menu');
	if (menuDiv.style.display === 'block' || menuDiv.style.display === ''){
		menuDiv.style.display = 'none';
	}else{
		menuDiv.style.display = 'block';
	}
}
function menu(option){
	var today = new Date();
	var expire = new Date();
	expire.setTime(today.getTime() + 3600000*24*10000); //10000 day expiration
	
	if ( option != "showTextType_content" && option != "showTextType_excerpt" ){
		document.getElementById(option).style.backgroundPosition = "-10 -275";
	}
	if (option == "showArticleAll"){
		document.cookie = "mobile_ttrss_article=" + "all" + ";expires="+expire.toGMTString();
		document.getElementById("showArticleUnread").style.backgroundPosition = "10 -275";
	} else if (option == "showArticleUnread"){
		document.cookie = "mobile_ttrss_article=" + "unread" + ";expires="+expire.toGMTString();
		document.getElementById("showArticleAll").style.backgroundPosition = "10 -275";
	}else if (option == "showTextType_excerpt"){
		document.cookie = "mobile_ttrss_textType=" + "content" + ";expires="+expire.toGMTString();
		document.getElementById("showTextType").style.backgroundPosition = "-10 -275";
	}else if (option == "showTextType_content"){
		document.cookie = "mobile_ttrss_textType=" + "excerpt" + ";expires="+expire.toGMTString();
		document.getElementById("showTextType").style.backgroundPosition = "10 -275";
	}else if (option == "showFeedStarred"){
		document.cookie = "mobile_ttrss_feed=" + "-1" + ";expires="+expire.toGMTString();
		document.getElementById("showFeedAll").style.backgroundPosition = "10 -275";
		document.getElementById("showFeedShared").style.backgroundPosition = "10 -275";
		document.getElementById("showFeedFresh").style.backgroundPosition = "10 -275";
	}else if (option == "showFeedAll"){
		document.cookie = "mobile_ttrss_feed=" + "-4" + ";expires="+expire.toGMTString();
		document.getElementById("showFeedStarred").style.backgroundPosition = "10 -275";
		document.getElementById("showFeedShared").style.backgroundPosition = "10 -275";
		document.getElementById("showFeedFresh").style.backgroundPosition = "10 -275";
	}else if (option == "showFeedShared"){
		document.cookie = "mobile_ttrss_feed=" + "-2" + ";expires="+expire.toGMTString();
		document.getElementById("showFeedAll").style.backgroundPosition = "10 -275";
		document.getElementById("showFeedStarred").style.backgroundPosition = "10 -275";
		document.getElementById("showFeedFresh").style.backgroundPosition = "10 -275";
	}else if (option == "showFeedFresh"){
		document.cookie = "mobile_ttrss_feed=" + "-3" + ";expires="+expire.toGMTString();
		document.getElementById("showFeedAll").style.backgroundPosition = "10 -275";
		document.getElementById("showFeedShared").style.backgroundPosition = "10 -275";
		document.getElementById("showFeedStarred").style.backgroundPosition = "10 -275";
	}
	location.reload();
}
function openArticle(url,id,link) {
	sid = "<?php echo $sessionID; ?>";
	if (id == articleOpen_keeper){
		 window.open(link);
	}else{
		document.getElementById(id).style.backgroundColor = "#F7F8FC"; 
		document.getElementById('articleHeader_'+id).style.backgroundColor = "#D6E0FA"; 
		articleId_keeper=id;
		if (articleOpen_keeper){ document.getElementById("articleBody_" + articleOpen_keeper).innerHTML = ''};
		//document.getElementById("articleBody_" + articleId_keeper).innerHTML = "";
		window.location.hash = '#' + articleId_keeper;
		data='{"sid":"' + sid + '","op":"getArticle","article_id":"' + id + '"}';
		if (window.XMLHttpRequest) {
			req = new XMLHttpRequest();
			req.onreadystatechange = processReqChange;
			req.open("POST", "../api/", true);
			req.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			req.send(data);
		} else if (window.ActiveXObject) {
			isIE = true;
			req = new ActiveXObject("Microsoft.XMLHTTP");
			if (req) {
				req.onreadystatechange = processReqChange;
				req.open("GET", url, true);
				req.send();
			}
		}
	}
}
function processReqChange() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			var data = eval('(' + req.responseText + ')');
			//if (articleOpen_keeper){ document.getElementById("articleBody_" + articleOpen_keeper).innerHTML = ''}; 
			document.getElementById("articleBody_" + articleId_keeper).innerHTML = data.content[0].content;
			//window.location.hash = '#' + articleId_keeper;
			articleOpen_keeper = articleId_keeper; 
		} else {
			alert("There was a problem retrieving data:\n" + req.statusText);
		}
	}
}
</script>

<style>
body {
	width : 100%;
	background : white;
	color : black;
	margin : 0px;
	padding : 0px;
	font-family : sans-serif;
	font-size : 12px;
	overflow-x : hidden;
	webkit-tap-highlight-color: red; 
}
#spinner{
 text-align: center;
 background: rgba(0,0,0,.5); 
 width:100%; 
 height:100%; 
 position:fixed;
 top:0; 
 left:0; 
 z-index:999;
 display: none;
}
#spinnerTop{
	width:100%;
	background-color: black;
	color: white;
	font-size: 16px;
}
.articleHeader{
	border-top: thin solid #A5C1F0;
	<?php 
	if ( $pref_textType == "content" ){
		print "	background : #D6E0FA;";
	}else{
		print "	background: white;";
	}
	?>
	color : black;
	font-size : 16px;
}
a.headerlink:link, a.headerlink:visited {
	text-decoration: none;
	color: black;
	font-weight:bold;
}
a.footerLink:link{
	text-decoration: none;
	color : blue;
}
.feedTitle{
	font-size : 12px;
	color : darkgrey;
}
img{
	max-width: 100%;
	height: auto; 
}
.footer, .header{
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#F7F8FC), to(#D6E0FA));
	background: -webkit-linear-gradient(top, #F7F8FC, #D6E0FA);
	background: -moz-linear-gradient(top, #F7F8FC, #D6E0FA);
	background: -ms-linear-gradient(top, #F7F8FC, #D6E0FA);
	background: -o-linear-gradient(top, #F7F8FC, #D6E0FA);
	
	border-top: thin solid #A5C1F0;
	color : black;
	height: 30px;
}
.footer{
	text-align: center;
}
.header{
	position: relative;
}
.starOn, .starOff{
	width:20px;
	height:40px;
	background-image: url(mobile-sprite.png);
	}
.starOff{
	background-position: -140 -30;
	}
.starOn{
	background-position: -160 -30;
	}
#menuButton{
	position: absolute;
	top: 5px;
	right: 10px;
	width: 20px;
	height: 20px;
	background-image: url(mobile-sprite.png);
	background-position: -32 -272;
}
#reloadButton{
	position: absolute;
	top: 5px;
	left: 10px;
	width: 20px;
	height: 20px;
	background-image: url(mobile-sprite.png);
	background-position: -35 -240;
}
#feedsButton{
	position: absolute;
	text-align: center;
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf) );
	background:-moz-linear-gradient( center top, #ededed 5%, #dfdfdf 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf');
	background-color:#ededed;
	-moz-border-radius:6px;
	-webkit-border-radius:6px;
	border-radius:2px;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#777777;
	font-weight:bold;
	text-decoration:none;
	text-shadow:1px 1px 0px #ffffff;
	width: 50px;
	height: 20px;
	top: 5px;
	left: 5px;
}
#pageTitle{
	font-weight:bold;
	font-size : 16px;
	position: absolute;
	top: 5px;
	width: 100%;
	text-align: center;
	}
#menu{
	background-color: #D6E0FA;
	font-weight:bold;
	font-size : 14px;
	position: absolute;
	top: 31px;
	right: 0px;
	width: 110px;
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
	border: thin solid #A5C1F0;
	z-index:100;
	}
ul{
	list-style-type: none;
	margin: 0;
	padding: 5;
}
li {
  margin-top: 1em;
}
.menuImg{
	overflow: hidden;
	position: relative;
	margin-top: -5px;
	height: 20px;
	width: 20px;
	background-image: url(mobile-sprite.png);
	background-position: 10 -275;
	border:0px;
	float:left;
}
iframe{
	max-width: 100%;
	border: 0;
}
<?php 
if ( $pref_Feed == "-1" ){
	print "#showFeedStarred{background-position: -10 -275;}\n";
}elseif ( $pref_Feed == "-2" ){
	print "#showFeedShared{background-position: -10 -275;}\n";
}elseif ( $pref_Feed == "-3" ){
	print "#showFeedFresh{background-position: -10 -275;}\n";
}elseif ( $pref_Feed == "-4" ){
	print "#showFeedAll{background-position: -10 -275;}\n";
}

if ( $pref_Article == "all" ){
	print "#showArticleAll{background-position: -10 -275;}\n";
}elseif ( $pref_Article == "unread" ){
	print "#showArticleUnread{background-position: -10 -275;}\n";
}

if ( $pref_textType == "content" ){
	print "#showTextType{background-position: -10 -275;}\n";
}
?>
</style>

<head>
<body>
<div id="spinner"><div id="spinnerTop">Working...</div></div>
<div class="header">
<!--<div onclick="alert('feeds');" id="feedsButton">Feeds</div> ToDo: feed selection screen.  I don't really have a need for it though... -->
<div onclick="location.reload();" id="reloadButton"></div>
<div id="pageTitle">
<?php
if ( $pref_Feed == "-1" ){
	print "Starred Articles\n";
}elseif ( $pref_Feed == "-2" ){
	print "Shared Feed\n";
}elseif ( $pref_Feed == "-3" ){
	print "Fresh Feed\n";
}elseif ( $pref_Feed == "-4" ){
	print "All Items\n";
}
?>
</div>
<div onclick="toggleMenu();" id="menuButton"></div>
<div id="menu" style="display:none;">
	<ul> 
		<li><div class="menuImg" id="showArticleAll"></div><a onclick="menu('showArticleAll');">Show&nbsp;All</a></li>
		<li><div class="menuImg" id="showArticleUnread"></div><a onclick="menu('showArticleUnread');">Show&nbsp;New</a></li>
		<li><div class="menuImg" id="showTextType"></div><a onclick="menu('showTextType_<?php echo $pref_textType; ?>');">Full&nbsp;Text</a></li>
		<hr>
		<li><div class="menuImg" id="showFeedStarred"></div><a onclick="menu('showFeedStarred');">Starred&nbsp;&nbsp;</a></li>
		<li><div class="menuImg" id="showFeedAll"></div><a onclick="menu('showFeedAll');">All Items</a></li>
		<li><div class="menuImg" id="showFeedShared"></div><a onclick="menu('showFeedShared');">Shared&nbsp;&nbsp;</a></li>
		<li><div class="menuImg" id="showFeedFresh"></div><a onclick="menu('showFeedFresh');">Fresh&nbsp;&nbsp;&nbsp;</a></li>
		<hr>
		<li><div class="menuImg" id="updateFeed"></div><a onclick=" updateFeed('-4','<?php echo $sessionID;?>');">Update All</a></li>
	</ul> 
</div>
</div>

<?php

if ( $login == 1 ){
	print "<a><b>".$error."</b></a>";
 	?>
	<form id='login' action='index.php' method='post' accept-charset='UTF-8'>
		<fieldset>
			<legend>Login</legend>
			<input type='hidden' name='submitted' id='submitted' value='1'/>
			<label for='username' >UserName:</label>
			<input type='text' name='username' id='username'  maxlength="50" /><br>
			<label for='password' >Password:</label>
			<input type='password' name='password' id='password' maxlength="50" /><br>
			<input type='submit' name='Submit' value='Submit' />
		</fieldset>
	</form>
	<?php
	exit;
} 

$json = get('{"op":"getHeadlines","sid":"' . $sessionID . '","feed_id":"' . $pref_Feed .'","limit":' . $pref_number . ',"show_' . $pref_textType . '":"1","include_attachments":' . $pref_attachments . ',"view_mode":"' . $pref_Article . '"}');
//print $json;
//exit;
$data = json_decode($json, TRUE);
$ids = '';

if ( $data['status'] == 1 ){
	setcookie('mobile_ttrss_sid',$sessionID,time() - 3600);
	print '<script type="text/javascript">window.location = "' . $_SERVER['PHP_SELF'] . '"</script>';
	//header('Location: ' . $_SERVER['PHP_SELF'] );
}
foreach ($data['content'] as $item){
	$ids .= $item['id'].",";
	print "<div id='".$item['id']."'>\n";
	print "<div class='articleHeader' id='articleHeader_".$item['id']."'>\n";
	print "<table width='100%'><tr><td width='20px'>\n";
	if ($item['marked'] == true){
		print "<div onclick='toggleStar(\"".$item['id']."\",\"".$sessionID."\")' class='starOn' id='star_".$item['id']."'></div>\n";
	}else{
		print "<div onclick='toggleStar(\"".$item['id']."\",\"".$sessionID."\")' class='starOff' id='star_".$item['id']."'></div>\n";
	}
	print "</td><td>\n";
	if ($pref_textType == "content"){
		print "<a class='headerLink' href='".$item['link']."' target='_blank'>\n"; 
	}else{
		print "<a class='headerLink' onclick='openArticle(\"".$item['link']."\",".$item['id'].",\"".$item['link']."\")'>\n"; 
	}
	//print utf8_decode($item['title'])."\n";
	print iconv("UTF-8", "CP1252", $item['title']);
	print "<div class='feedTitle'>".utf8_decode($item['feed_title'])."</div>\n</a>\n";
	print "</td></tr></table>\n";
	print "</div>\n";
	print "<div class='articleBody' id='articleBody_".$item['id']."'>\n".utf8_decode($item[$pref_textType])."\n</div>\n";
	print "</div>\n";
}

if ( count($data['content']) == 0 ){
	print "<a>No New Articles</a>";
}else{
	print '<div class="footer"><a class="footerLink" href="index.php?cmd=markRead&ids=' . $ids . '"  onclick="showSpinner(1)">Mark these items as read</a></div>';
}


function get($params){
	$url = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$index = strrpos ( $url  , "/" );
	$url = substr ( $url  , 0 , $index );
	$index = strrpos ( $url  , "/" );
	$url = substr ( $url  , 0 , $index + 1 ) . "api/";
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	$output = curl_exec($ch); 
	curl_close($ch);
	return $output;
}
?>
</body>
</html>
