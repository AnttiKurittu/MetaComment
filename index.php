<?php
if (empty($_GET['meta'])) {
    header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?meta=default');
    die;
}

function random() {
	return $rand = substr(md5(random_bytes(64)),rand(0,5),10);
    }

function encrypt($in, $key) {
    // Encrypt a string with AES-256-CBC
    $iv = trim(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16));
    $key = base64_encode($key);
    $in = gzencode($in);
    $encrypted = openssl_encrypt($in, 'AES-256-CBC', $key, 0, $iv);
    return $iv.$encrypted;
}

function decrypt($in, $key) {
    // Decrypt a string.
    $iv = substr($in, 0, 16);
    $key = base64_encode($key);
    $decrypted = openssl_decrypt(substr($in, 16), 'AES-256-CBC', $key, 0, $iv);
    $decrypted = gzdecode($decrypted);
    return $decrypted;
}

function linkify($text) {
    $text = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_BLANK">$1</a>', $text);
	$text = preg_replace('/(\#\w+)/', '<a href="'. $_SERVER['SCRIPT_NAME'] . '?meta=$1"><span style="color:red;">$1</span></a>', $text);
	$text = str_replace('?meta=#', '?meta=', $text); // Parse hashtags
	return $text;
}

$lines = "";

$filename = hash("sha256", $_GET['meta']) . "-lines.json";
if (file_exists($filename)) {
    $lines = decrypt(file_get_contents($filename), $_GET['meta']);
}

if (empty($lines)) {
    $lines = '{ "0":{"MetaComment":"Welcome. MetaComment shows the 50 latest comments for this reference." }}';
    echo '<div class="well well-sm col-sm-12 col-md-12"><b>You created a new page for <a href="' . $_SERVER['REQUEST_URI'] .'">#'.$_GET['meta'].'</a></b></div>';
}

$lines = json_decode($lines, true);

if (!empty($_COOKIE)) {
    $author_cookie = substr(strip_tags(base64_decode($_COOKIE['MetaComment'])),0,30);
	setcookie("MetaComment", base64_encode($author_cookie));
} else {
	setcookie("MetaComment", "");
	$author_cookie = "";
}

if (isset($_POST)) {
    if (empty($_POST['author']) || empty($_POST['comment'])) {
        #--;
    } else {
		setcookie("MetaComment", base64_encode($_POST['author']));
        $newline['<small style="color:#888;">[' . 
		date("Y-m-d H:i") . ']</small> ' . 
		trim(substr(strip_tags($_POST['author']), 0, 30))] = trim(substr(strip_tags($_POST['comment']), 0, 5000));
        $lines[] = $newline;
        if (count($lines) >= 51) {
            array_splice($lines, 0, 1);
        }
		file_put_contents($filename, encrypt(json_encode($lines), $_GET['meta']));
		unset($_POST);
		header('Location: ' . $_SERVER['REQUEST_URI']);
		die;
	}
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>MetaComment | <?php echo linkify(strip_tags($_GET['meta'])); ?></title>
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body style="font-family: 'Roboto', sans-serif;">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <div class="container">
    <div class="page-heading">
	<h1>MetaComment | <?php echo strip_tags($_GET['meta']); ?></h1>
	</div>
	<?php echo '<small style="color:#999">This page uses cookies to store your username. By continuing to use this page, you accept the usage of cookies. Use a #hashtag to refer to an another MetaComment page.</small> <a class="pull-right" href="' . $_SERVER['REQUEST_URI'] .'">Link to this page</a>';?>

    <div class="col-md-12 col-sm-12 well well-sm">
	
    <form class="form-inline" action="<?php echo $_SERVER['SCRIPT_NAME'] . "?meta=" . $_GET['meta']; ?>" method="POST">
    <div class="form-group" style="width:15%;">
    <input <?php if (empty($author_cookie)) {echo 'autofocus="autofocus"';}?> type="text" maxlength="30" class="form-control" id="author" placeholder="Author" value="<?php echo $author_cookie;?>" name="author" style="width:100%;<?php if (!empty($author_cookie)) {echo 'background-color:#EEFFEE";';}?>">
    </div>
    <div class="form-group" style="width:60%;">
    <input style="width:100%;" <?php if (!empty($author_cookie)) {echo 'autofocus="autofocus"';}?> type="text" maxlength="5000" class="form-control" id="comment" placeholder="Comment (max. 5000 characters)" name="comment">
    </div>
    <button type="submit" class="btn btn-info pull-right" style="width:65px;">+ Add</button>
    </form>
    </div>
    <br>
    <br>

<?php 
$printlines = array_reverse($lines, true);
foreach ($printlines as $line) {
    foreach ($line as $key => $value) {
		echo '<div class="well well-sm col-sm-12 col-md-12"><b>' . $key . ":</b> " . linkify($value) . '</div>';
    }
}
?>
    <form class="form-inline" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="GET">
    <div class="form-group">
    <input type="text" class="form-control" id="meta" placeholder="Page" name="meta" style="width:200;">
    </div>
    <button type="submit" class="btn btn-success" style="width:65px;">Go</button> <a href="?meta=<?php echo random(); ?>" class="btn btn-warning">random page</a>
    </form>
</div> <!-- container -->
</body>
</html>
