<?php

namespace LemonSqueezy\Laravel\Concerns;

trait Prorates
{
    /**
     * Indicates if the plan change should be prorated.
     */
    protected bool $prorate = true;

    /**
     * Indicate that the plan change should not be prorated.
     */
    public function noProrate(): self
    {
        $this->prorate = false;

        return $this;
    }

    /**
     * Indicate that the plan change should be prorated.
     */
    public function prorate(): self
    {
        $this->prorate = true;

        return $this;
    }

    /**
     * Set the prorating behavior for the plan change.
     */
    public function setProration(bool $prorate = true): self
    {
        $this->prorate = $prorate;

        return $this;
    }
}
