<?php

namespace spicyweb\fieldfield\collections;

use Illuminate\Support\Collection;

/**
 * A Collection containing fields.
 *
 * @package spicyweb\fieldfield\collections
 * @author Spicy Web <plugins@spicyweb.com.au>
 * @since 1.0.0
 */
class FieldCollection extends Collection
{
    /**
     * Gets the IDs for the fields in this collection.
     *
     * @return Collection
     */
    public function ids(): Collection
    {
        return $this->map(fn($field) => $field->id);
    }
}
