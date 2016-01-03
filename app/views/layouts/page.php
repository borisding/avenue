<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="A lightweight MVC framework for quick PHP web development and prototyping.">
    <meta name="author" content="Boris Ding Poh Hing">
    <title><?= $this->e($title); ?></title>
    <?php 
    for ($i = 0, $len = count($css); $i < $len; $i++) {
        $file = $this->baseUrl() . '/public/css/' . $css[$i] . '.css';
        echo '<link href="' . $file . '" rel="stylesheet">';
        echo PHP_EOL;
    }
    ?>
  </head>

  <body>
    <!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?= $this->baseUrl(); ?>">Avenue</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="<?= $this->baseUrl(); ?>"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <!-- Begin page content -->
    <div class="container">
    <?= $content; ?>
    </div>
    
    <footer class="footer">
      <div class="container">
        <p class="text-muted">Bootstrap demo page for Avenue, <?= date('Y'); ?></p>
      </div>
    </footer>
    
    <? 
    for ($i = 0, $len = count($scripts); $i < $len; $i++) {
        $file = $this->baseUrl() . '/public/js/' . $scripts[$i] . '.js';
        echo '<script src="' . $file . '"></script>';
        echo PHP_EOL;
    }
    ?>
  </body>
</html>