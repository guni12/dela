<?php

namespace Anax\View;

/**
 * View to delete a member.
 */
// Show all incoming variables/functions
//var_dump(get_defined_functions());
//echo showEnvironment(get_defined_vars());

$members = url("user");

?>

<?= $form ?>

<p><span class="button"><a href="<?= $members ?>">Alla medlemmar</a></span></p>
