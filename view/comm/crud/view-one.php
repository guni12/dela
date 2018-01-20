<?php

namespace Anax\View;

/**
 * View to display a post and comments.
 */

$data = isset($data) ? $data : null;

?>

<?php if (!$items) : ?>
    <p>Inga kommentarer i databasen.</p>
<?php
    return;
endif;
?>

<?= $items ?>
<p></p>
