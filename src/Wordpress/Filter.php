<?php

namespace Vulkhan\Toolbox\Wordpress;

abstract class Filter
{
    /**
     * @param string $hookName
     * @param int $priority
     * @param int $acceptedArgs
     */
    public function __construct(
        protected string $hookName,
        protected int $priority = 10,
        protected int $acceptedArgs = 1
    ) { }

    /**
     * Add the current filter to the WP filter list.
     * @return bool
     */
    public function add() : bool
    {
        return \add_filter($this->hookName, $this->callback(), $this->priority, $this->acceptedArgs);
    }

    /**
     * Remove the current filter from the WP filter list.
     * @return bool
     */
    public function remove() : bool
    {
        return \remove_filter($this->hookName, $this->callback(), $this->priority);
    }

    /**
     * You must implement this method, so it returns a callable that
     * Will be called by WordPress when the given filter fires.
     */
    abstract public function callback() : callable;
}