<?php

namespace Anax\View;

/**
 * View to create a new book.
 */
// Show all incoming variables/functions
//var_dump(get_defined_functions());
//echo showEnvironment(get_defined_vars());

$members = url("user");

?>

<?= $data['content'] ?>

<p><span class="button"><a href="<?= $members ?>">Alla medlemmar</a></span></p>
