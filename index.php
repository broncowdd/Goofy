<?php
#############################
## HANDLE TEMP DIR & FILES ##
#############################
if (!is_dir('temp')){mkdir('temp');file_put_contents('temp/index.html', '');}
// Clean old temp files
$temp=glob('temp/*.zip');
foreach ($temp as $file){
	$howold=time()-@date('s',filemtime($file));
	if ($howold>240){
		$dir=str_replace('.zip','',$file);
		unlink($file);
		$sub=glob($dir.'/*');
		foreach($sub as $subfile){unlink($subfile);}
		rmdir($dir);
	}
}
	
define('DIR',uniqid());
mkdir('temp/'.DIR);


#############################
## INIT #####################
#############################
$version='v1.0';
$icon='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAADAFBMVEUBAAAAAAAAgACAgAAAAICAAIAAgIDAwMDA3MCmyvD/8NT/4rH/1I7/xmv/uEj/qiX/qgDckgC5egCWYgBzSgBQMgD/49T/x7H/q47/j2v/c0j/VyX/VQDcSQC5PQCWMQBzJQBQGQD/1NT/sbH/jo7/a2v/SEj/JSX+AADcAAC5AACWAABzAABQAAD/1OP/scf/jqv/a4//SHP/JVf/AFXcAEm5AD2WADFzACVQABn/1PD/seL/jtT/a8b/SLj/Jar/AKrcAJK5AHqWAGJzAEpQADL/1P//sf//jv//a///SP//Jf/+AP7cANy5ALmWAJZzAHNQAFDw1P/isf/Ujv/Ga/+4SP+qJf+qAP+SANx6ALliAJZKAHMyAFDj1P/Hsf+rjv+Pa/9zSP9XJf9VAP9JANw9ALkxAJYlAHMZAFDU1P+xsf+Ojv9ra/9ISP8lJf8AAP4AANwAALkAAJYAAHMAAFDU4/+xx/+Oq/9rj/9Ic/8lV/8AVf8ASdwAPbkAMZYAJXMAGVDU8P+x4v+O1P9rxv9IuP8lqv8Aqv8AktwAerkAYpYASnMAMlDU//+x//+O//9r//9I//8l//8A/v4A3NwAubkAlpYAc3MAUFDU//Cx/+Ku/9Rr/8ZI/7gl/6oA/6oA3JIAuXoAlmIAc0oAUDLU/+Ox/8eO/6tr/49I/3Ml/1cA/1UA3EkAuT0AljEAcyUAUBnU/9Sx/7GO/45r/2tI/0gl/yUA/gAA3AAAuQAAlgAAcwAAUADj/9TH/7Gr/46P/2tz/0hX/yVV/wBJ3AA9uQAxlgAlcwAZUADw/9Ti/7HU/47G/2u4/0iq/yWq/wCS3AB6uQBilgBKcwAyUAD//9T//7H//47//2v//0j//yX+/gDc3AC5uQCWlgBzcwBQUADy8vLm5uba2trOzs7CwsK2traqqqqenp6SkpKGhoZ6enpubm5iYmJWVlZKSko+Pj4yMjImJiYaGhoODg7/+/CgoKSAgID/AAAA/wD//wAAAP//AP8A//////8sxNfNAAAAAXRSTlMAQObYZgAAAN5JREFUeNqlktutxCAMRGfSA0ozlH+bQfSAFwfCYzHsx7VEAvgwNtjED+NuW44A/+DlAGT/vwDd6gSt414JX/LcAQ8iO0DN7wDiDJS15ggzByZcPf4KEAqkC+2lZ0D9uHQIbCDVyQHQAGkD5GlJUb9GDnSI2ZVUQ7gChIsuoggIuFyT2Y9HQ1fSWopdQH8qoZPYgnDyV4t6nGM/ZEGORFZ4CVY/DAAyAm8w14AiwTKkvuMdao7TOfbK3wjPLWECLt5A6JUYI6MRoQlYQH4BF4cyyHe53ZtfXcvachDM9gHtLWghksFQ+gAAAABJRU5ErkJggg==';
$me='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
$bookmarklet='<a class="btn" href="javascript:javascript:(function(){var content = document.querySelector(\'.code\');var reg=new RegExp(\'(http://[^\\\']+)\',\'gi\');url=reg.exec(content.innerHTML);window.open(\''.$me.'?q=\'+ encodeURIComponent(url),\'_blank\',\'menubar=yes,height=600,width=1000,toolbar=yes,scrollbars=yes,status=yes\');})();" >Goofy</a>';
ini_set('allow_url_fopen', '1');

#############################
## GET from bookmarklet #####
#############################
if (!empty($_GET['q'])){
	$adresse=explode(',',strip_tags($_GET['q']));
	define('URL',$adresse[0]);
	if ($css=file_curl_contents($adresse[0])){
		//extract font path & name
		$r=preg_match_all('#local\(\'([^\']+).+url\(([^\)]+)#',$css,$fonts);

		$localzip=array();
		foreach ($fonts[2] as $key=>$path){
			//download in temp
			$content=file_curl_contents($path);
			$local='temp/'.DIR.'/'.str_replace(' ','_',$fonts[1][$key]).'.woff';
			file_put_contents($local,$content);
			// add in files list for zip
			$localzip[]=$local;
			// prepare the local css file version
			$css=str_replace($path,basename($local),$css);
		}
		// create local css file
		$local='temp/'.DIR.'/font-faces.css';
		file_put_contents($local,$css);
		$localzip[]=$local;
		// create zip file
		$zipfile='temp/'.DIR.'.zip';
		create_zip($localzip,$zipfile);
		header('location: '.$zipfile);
	}else{echo 'Error, impossible to download file';}
}else{define('URL','None');}


#############################
## FUNCTIONS ################
#############################
function aff($a,$stop=true,$line='?'){echo 'Arret a la ligne '.$line.' du fichier '.__FILE__.'<pre>';print_r($a);echo '</pre>';if ($stop){exit();}}	
function file_curl_contents($url,$pretend=true){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Charset: UTF-8'));
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	if (!ini_get("safe_mode") && !ini_get('open_basedir') ) {curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);}
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
	if ($pretend){curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:19.0) Gecko/20100101 Firefox/19.0');}    
	//curl_setopt($ch, CURLOPT_REFERER, random_referer());// notez le referer "custom"
	$data = curl_exec($ch);
	$response_headers = curl_getinfo($ch);
	// Google seems to be sending ISO encoded page + htmlentities, why??
	if($response_headers['content_type'] == 'text/html; charset=ISO-8859-1') $data = html_entity_decode(iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $data)); 
	curl_close($ch);
	return $data;
}
function curl_get_file_size( $url ) {
	 $ch = curl_init($url);

		 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		 curl_setopt($ch, CURLOPT_HEADER, TRUE);
		 curl_setopt($ch, CURLOPT_NOBODY, TRUE);

		 $data = curl_exec($ch);
		 $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

		 curl_close($ch);
		 if ($size!=-1){return $size;}else{return false;}
	}
function fuck_slashes($string){return preg_replace('#(?<=[^:])//#','/',stripslashes($string));}

function create_zip($files = array(),$destination = '',$overwrite = false) {  
	if(file_exists($destination) && !$overwrite) { return false; } 
	$valid_files = array();  
	if(is_array($files)) {  
		foreach($files as $file) {  
			if(file_exists($file)) {  
				$valid_files[] = $file;  
			}  
		}  
	}  
	if(count($valid_files)) {  
		$zip = new ZipArchive();  
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {  
			return false;  
		}  
		foreach($valid_files as $file) {  
			$zip->addFile($file,basename($file));  
		}  	        
		$zip->close();  	          
		return file_exists($destination);  
	}else{ return false; }  
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr" charset="UTF-8">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="UTF-8">
	<title>Goofying <?php echo URL;?></title>
	<link rel="shortcut icon" href="<?php echo $icon;?>" />
    
	<style>
	*{box-sizing: border-box}
		html,body{padding:0;margin:0;font-family: Palatino, Georgia, Helvetica, sans-serif;}
		body{min-width:320px;min-height:320px; #eee;}
		header{width:100%;vertical-align:middle;margin:0;padding:10px; font-size:24px;color:#fff; text-shadow:0 1px 2px black;background:rgba(0,0,0,0.5);box-shadow:0 1px 2px rgba(0,0,0,0.5);}
		header img{vertical-align:middle;}
		footer{position:fixed;bottom:0;margin:0;margin-top:10px;padding:10px;width:100%; font-size:20px;color:#fff; text-shadow:0 -1px 2px black;background:url('<?php echo $noise;?>') rgba(0,0,0,0.5);box-shadow:0 1px 2px rgba(0,0,0,0.5);}
		footer a,header a{text-decoration:none;color:#bbd;padding-bottom:2px;}
		header a:hover{border-bottom:2px dashed #cce;color:#cce;}
		header form {display:inline-block;width:100%;}
		header form a{font-size:24;font-style:normal;}
		header form a:hover{text-decoration:none;}
		header form input[type=text]{display:inline-block;width:50%;padding:2px;font-size:20px; border-radius: 3px;margin-left:10px;}
		header form input[type=submit]{display:inline-block;width:50px;padding:2px;font-size:20px; border-radius: 3px;margin-left:10px;}
		header form select{display:inline-block;padding:2px;font-size:20px; border-radius: 3px;margin-left:10px;}
		h1{font-size:20;color:#888;text-shadow:0 1px 1px #fff;margin-left:20px;}
		li{padding:5px;margin-left:20px;}
		li:hover{background-color:#DDD;}
		li label{border-radius:3px;cursor:pointer;padding:4px;}
		input[type=checkbox]:checked+label{background-color:rgba(0,0,0,0.2);box-shadow:inset 0 1px 2px black;text-shadow:0 1px 1px white;}
		li a{display:inline-block;width:64px;text-align:center;font-family:courier;text-decoration:none;border-radius:2px; border:1px solid rgba(0,0,0,0.2);background-color:#EEE;color:#555;text-shadow:0 1px 1px white;box-shadow:0 1px 2px #444;padding:3px;}

		.error{text-align:center;color:red;}
		footer .btn,form input[type=submit] {
			margin:1px;
		  background: #3498db;
		  background-image: linear-gradient(to bottom, #3498db, #2980b9);
		  -webkit-border-radius: 3;
		  -moz-border-radius: 3;
		  border-radius: 3px;
		  font-family: Arial;
		  color: #ffffff;
		  font-size: 14px;
		  padding: 3px 10px 3px 10px;
		  text-decoration: none;
		  box-shadow:0 1px 1px blue;
		}

		.btn:hover {
		  background: #3cb0fd;
		  background-image: linear-gradient(to bottom, #3cb0fd, #3498db);
		  text-decoration: none;
		}
		form input.tipiakselected{margin:auto;display:block;font-size:20px;}
		hr{border: 1px solid #888;}
	</style>
</head>
<body><header> <img src="<?php echo $icon;?>"/> Goofy </header>
<h1> How to use Goofy?</h1>
<ol>
<li>Drop this bookmarklet into your fav bar <?php echo $bookmarklet;?></li>
<li>go to google font page : <a href="https://www.google.com/fonts">HERE</a> and select all the google fonts you'd like to use on your own server</li>
<li>Click on the "Use" button (in the footer)</li>
<li>Then click on the goofy bookmarklet and receive a zip with the woff fonts and the local css file to use them</li>

</ol>
</body>
</html>
