<?php

namespace Anax\View;

/**
 * View to create a new book.
 */

$members = url("user");

?>

<?= $data['content'] ?>

<p><span class="button"><a href="<?= $members ?>">Alla medlemmar</a></span></p>
