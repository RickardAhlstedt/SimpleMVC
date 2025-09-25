<?php

namespace SimpleMVC\Core\HTTP;

abstract class AbstractController
{
    protected RequestStack $requestStack;
    protected \SimpleMVC\Templating\Templating $templating;
    protected \SimpleMVC\Database\Driver\DatabaseInterface $database;

    public function __construct(RequestStack $requestStack, \SimpleMVC\Templating\Templating $templating, \SimpleMVC\Database\Driver\DatabaseInterface $database)
    {
        // Initialize templating service if needed
        $this->templating = $templating;

        // Set the request stack
        $this->setRequestStack($requestStack);

        // Set the database connection
        $this->database = $database;
    }

    public function render(string $template, array $params = []): Response
    {
        $content = $this->templating->render($template, $params);
        return new Response($content, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * Get the current request.
     */
    protected function getRequest(): RequestStack
    {
        return $this->requestStack;
    }

    /**
     * Set the request stack.
     */
    protected function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }
}
