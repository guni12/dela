<?php

namespace Anax\View;

/**
 * View to display all posts.
 */
?>

<?php if (!$items) : ?>
    <p>Inga kommentarer i databasen.</p>
<?php
    return;
endif;
?>

<?= $items ?>
<p></p>
