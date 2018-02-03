<?php

namespace Guni\Page;

use \Anax\DI\InjectionAwareInterface;
use \Anax\DI\InjectionAwareTrait;

/**
 * A default page rendering class.
 */
class FlatFileContentController implements InjectionAwareInterface
{
    use InjectionAwareTrait;



    /**
     * Render a page using flat file content.
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return void
     */
    public function render()
    {
        $req = $this->di->get("request");
        $path = $req->getRoute();
        if ($path == "") {
            $this->di->get("response")->redirect("comm/front");
        }

        $file1 = ANAX_INSTALL_PATH . "/content/${path}.md";
        $file2 = ANAX_INSTALL_PATH . "/content/${path}/index.md";

        $file = is_file($file1) ? $file1 : null;
        $file = is_file($file2) ? $file2 : $file;

        if (!$file) {
            return;
        }

        $real = realpath($file);
        $base = realpath(ANAX_INSTALL_PATH . "/content/");
        if (strncmp($base, $real, strlen($base))) {
            return;
        }

        $content = file_get_contents($file);
        $content = $this->di->get("textfilter")->parse(
            $content,
            ["yamlfrontmatter", "shortcode", "markdown", "titlefromheader"]
        );

        $this->di->get("pageRender")->renderPage($content->text, $content->frontmatter);
    }
}
