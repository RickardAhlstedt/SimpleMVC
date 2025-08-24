<?php

namespace SimpleMVC\Core\HTTP;

abstract class AbstractController
{
    protected RequestStack $requestStack;
    protected \SimpleMVC\Templating\Templating $templating;

    public function __construct(RequestStack $requestStack, \SimpleMVC\Templating\Templating $templating)
    {
        // Initialize templating service if needed
        $this->templating = $templating;
        
        // Set the request stack
        $this->setRequestStack($requestStack);
    }

    public function render(string $template, array $params = []): string
    {
        return $this->templating->render($template, $params);
    }

    /**
     * Get the current request.
     */
    protected function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * Set the request stack.
     */
    protected function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;    
    }
}