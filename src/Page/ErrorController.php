<?php

namespace Guni\Page;

use \Anax\DI\InjectionAwareInterface;
use \Anax\DI\InjectionAwareTrait;

/**
 * A sample class for routes dealing with error situations.
 */
class ErrorController implements InjectionAwareInterface
{
    use InjectionAwareTrait;



    /**
     * Render an error page with a message and a status code.
     *
     * @param integer $status   code to use for response.
     * @param string  $title    to use for the page.
     * @param string  $message  to display in the view.
     *
     * @return void
     */
    public function errorPage($status, $title, $message)
    {
        $this->di->get("view")->add("default1/http_status_code", [
            "title" => $title,
            "message" => $message,
        ]);
        $tempfix = "";
        $this->di->get("pageRender")->renderPage($tempfix, ["title" => $title], $status);
    }



    /**
     * Render a 403 forbidden page using the default page renderer.
     *
     * @return void
     */
    public function page403()
    {
        $this->errorPage(
            403,
            "403 Forbidden",
            "You are not permitted to do this."
        );
    }



    /**
     * Render a 404 Page not found using the default page renderer.
     *
     * @return void
     */
    public function page404()
    {
        $this->errorPage(
            404,
            "404 Page not found",
            "The page you are looking for is not here."
        );
    }



    /**
     * Render a 404 Page not found using the default page renderer.
     *
     * @return void
     */
    public function page500()
    {
        $this->errorPage(
            500,
            "500 Internal Server Error",
            "An unexpected condition was encountered."
        );
    }
}
