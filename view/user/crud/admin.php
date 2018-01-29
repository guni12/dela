<?php

namespace Anax\View;

/**
 * View to display all members.
 */

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
