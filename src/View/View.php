<?php

namespace Migliori\PowerLitePdo\View;

class View
{
    private string $htmlContent = '';

    /**
     * Adds content to the view.
     *
     * @param string $content The content to be added.
     */
    /**
     * Adds content to the view.
     *
     * @param string $content The content to be added.
     * @return self Returns an instance of the View class.
     */
    public function add(string $content):self
    {
        $this->htmlContent .= $content;
        return $this;
    }

    /**
     * Sets the content of the view.
     *
     * @param string $content The content to set.
     * @return self Returns the current instance of the View class.
     */
    public function set(string $content):self
    {
        $this->htmlContent = $content;
        return $this;
    }

    public function get(): string
    {
        return $this->htmlContent;
    }

    /**
     * Renders the view.
     *
     * This method is responsible for rendering the view and displaying it to the user.
     * It should be called after setting all the necessary data for the view.
     */
    public function render(): self
    {
        echo $this->htmlContent;
        return $this;
    }

    /**
     * Clears the view.
     *
     * This method clears the view by removing all the content and resetting any internal state.
     */
    public function clear(): void
    {
        $this->htmlContent = '';
    }
}
