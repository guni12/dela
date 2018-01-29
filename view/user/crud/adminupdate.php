<?php

namespace Anax\View;


$item = isset($item) ? $item : null;
$members = url("user");

?>
<p><span class="button"><a href="<?= $members ?>">Alla medlemmar</a></span></p>

<?= $form ?>
