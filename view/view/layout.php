<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <link rel="stylesheet" 
      href="//cdn.rawgit.com/balpha/pagedown/master/demo/browser/demo.css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <?php foreach ($stylesheets as $stylesheet) : ?>
    <link rel="stylesheet" type="text/css" href="<?= $this->asset($stylesheet) ?>">
    <?php endforeach; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://use.fontawesome.com/a2ad53588d.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pagedown/1.0/Markdown.Converter.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pagedown/1.0/Markdown.Editor.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/pagedown/1.0/Markdown.Sanitizer.js"></script>

</head>
<body>
<div class='shade'>

<?php if ($this->regionHasContent("header")) : ?>
<div class="header-wrap">
    <?php $this->renderRegion("header") ?>
</div>
<?php endif; ?>

<?php if ($this->regionHasContent("navbar")) : ?>
<div class="navbar-wrap">
    <?php $this->renderRegion("navbar") ?>
</div>
<?php endif; ?>
<div class="container">
<?php if ($this->regionHasContent("flash")) : ?>
<div class="flash-wrap col-sm-12 col-xs-12 col-lg-12 col-md-12">
    <?php $this->renderRegion("flash") ?>
</div>
<?php endif; ?>

<?php if ($this->regionHasContent("main")) : ?>
<div class="main-wrap col-sm-12 col-xs-12 col-lg-12 col-md-12">
    <?php $this->renderRegion("main") ?>
</div>
<?php endif; ?>

<?php if ($this->regionHasContent("mainleft")) : ?>
<div class="row content">
<div class="col-sm-7 col-xs-12">
    <?php $this->renderRegion("mainleft") ?>
</div>
<?php endif; ?>

<?php if ($this->regionHasContent("blogleft")) : ?>
<div class="row content">
<div class="col-sm-3 col-xs-12 sidenav">
    <?php $this->renderRegion("blogleft") ?>
</div>
<?php endif; ?>

<?php if ($this->regionHasContent("mainright")) : ?>
<div class="col-sm-5 col-xs-12">
    <?php $this->renderRegion("mainright") ?>
</div>
</div>
<?php endif; ?>

<?php if ($this->regionHasContent("blogright")) : ?>
<div class="col-sm-9 col-xs-12">
    <?php $this->renderRegion("blogright") ?>
</div>
</div>
<?php endif; ?>
</div>

<?php if ($this->regionHasContent("footer")) : ?>
<div class="footer-wrap col-sm-12 col-xs-12 col-lg-12 col-md-12">
    <?php $this->renderRegion("footer") ?>
</div>
<?php endif; ?>
</div>
</body>
</html>
<script>
    var converter = Markdown.getSanitizingConverter();
    var editor = new Markdown.Editor(converter);
    editor.run();
</script>
