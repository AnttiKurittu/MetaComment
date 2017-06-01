<?php
if (empty($_GET['meta'])) {
    header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?meta=default');
    die;
}

function linkify($text) {
    return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_BLANK">$1</a>', $text);
}
$lines = "";
$filename = md5($_GET['meta']) . "-lines.json";
if (file_exists($filename)) {
    $lines = file_get_contents($filename);
}
if (empty($lines)) {
    $lines = '{ "0":{"MetaComment":"Welcome. MetaComment shows the 50 latest comments for this reference." }}';
    echo '<div class="well well-sm col-sm-12 col-md-12"><b>You created a new page.</b></div>';
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
        $newline['<small style="color:#888;">[' . date("Y-m-d H:i") . ']</small> ' . trim(substr(strip_tags($_POST['author']), 0, 30))] = trim(substr(strip_tags($_POST['comment']), 0, 280));
        $lines[] = $newline;
        if (count($lines) >= 51) {
            array_splice($lines, 0, 1);
        }
		file_put_contents($filename, json_encode($lines));
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
    <title>MetaComment | <?php echo strip_tags($_GET['meta']); ?></title>

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

    <div class="col-md-12 col-sm-12 well well-sm">
    <form class="form-inline" action="<?php echo $_SERVER['SCRIPT_NAME'] . "?meta=" . $_GET['meta']; ?>" method="POST">
    <div class="form-group">
    <input <?php if (empty($author_cookie)) {echo 'autofocus="autofocus"';}?> type="text" maxlength="30" class="form-control" id="author" placeholder="Author" value="<?php echo $author_cookie;?>" name="author" style="width:100px;<?php if (!empty($author_cookie)) {echo 'background-color:#EEFFEE";';}?>">
    </div>
    <div class="form-group">
    <input <?php if (!empty($author_cookie)) {echo 'autofocus="autofocus"';}?> type="text" maxlength="280" class="form-control" id="comment" placeholder="Comment (max. 280 characters)" name="comment" style="width:600px;">
    </div>
    <button type="submit" class="btn btn-default pull-right">+ Add</button>
    </form>
    </div>
    <br>
    <br>

<?php 
$printlines = array_reverse($lines, true);
$default = "https://www.somewhere.com/homestar.jpg";
$size = 40;
$counter = 0;
foreach ($printlines as $line) {
	$counter++;
    foreach ($line as $key => $value) {
        echo '<div class="well well-sm col-sm-12 col-md-12"><b>' . $key . ":</b> " . linkify($value) . '</div>';
    }
}
echo '<small style="color:#999">This page uses cookies to store your username. By continuing to use this page, you accept the usage of cookies.</small> <a class="pull-right" href="' . $_SERVER['REQUEST_URI'] .'">Link to this page</a>';
?>

</div> <!-- container -->
</body>
</html
