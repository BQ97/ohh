<?php

namespace App;

use League\Plates\Engine;
use Laminas\Diactoros\Response\HtmlResponse;

class View
{
    private Engine $templates;

    public function __construct()
    {
        $this->templates = new Engine(VIEW_PATH, 'phtml');
    }

    /**
     * Create a new template and render it.
     * @param  string $name
     * @param  array  $data
     * @return string  $Html
     */
    public function parse(string $name, array $data = []): string
    {
        if ($this->templates->exists($name)) {
            return $this->templates->render($name, $data);
        }

        return $this->templates->render('not-found', ['name' => $name]);
    }

    public function display(string $name, array $data = [], int $status = 200, array $headers = []): HtmlResponse
    {
        return new HtmlResponse($this->parse($name, $data), $status, $headers);
    }
}
