<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A lightweight MVC framework for quick PHP web development and prototyping.">
    <meta name="author" content="Boris Ding Poh Hing">
    <title><?= $this->e($title); ?></title>
    <?
    $baseUrl = $this->baseUrl();
    $version = $this->version();
    ?>
    <link href="<?= $baseUrl; ?>assets/components/bootstrap/dist/css/bootstrap.min.css?v=<?= $version; ?>" rel="stylesheet">
    <link href="<?= $baseUrl; ?>assets/css/dist/style.min.css?v=<?= $version; ?>" rel="stylesheet">
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
          <a class="navbar-brand" href="<?= $baseUrl; ?>">Project Name</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="<?= $baseUrl; ?>"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
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
            <p class="text-muted"><a href="https://github.com/borisding/avenue">Avenue framework</a> (v<?= AVENUE_FRAMEWORK_VERSION ;?>)
        </div>
    </footer>

    <script src="<?= $baseUrl; ?>assets/components/jquery/dist/jquery.min.js?v=<?= $version; ?>"></script>
    <script src="<?= $baseUrl; ?>assets/components/bootstrap/dist/js/bootstrap.min.js?v=<?= $version; ?>"></script>
  </body>
</html>
