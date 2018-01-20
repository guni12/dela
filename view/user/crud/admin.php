<?php

namespace Anax\View;

/**
 * View to display all members.
 */

// Gather incoming variables and use default values if not set
$users = isset($users) ? $users : null;

?>

<?php if (!$users) : ?>
    <p>Inga användare i databasen.</p>
<?php
    return;
endif;
?>

<?= $users ?>

<p></p>
