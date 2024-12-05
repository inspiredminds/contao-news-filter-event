<?php

declare(strict_types=1);

/*
 * This file is part of the Contao News Filter Event extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoNewsFilterEvent\Event;

use Contao\Module;
use Symfony\Contracts\EventDispatcher\Event;

class NewsFilterEvent extends Event
{
    /**
     * @var list<string>
     */
    private array $columns = [];

    /**
     * @var list<mixed>
     */
    private array $values = [];

    private array $options = [];

    private bool $addDefaults = true;

    private bool $forceEmptyResult = false;

    public function __construct(
        private readonly array $archives,
        private readonly bool|null $featured,
        private readonly int|null $limit,
        private readonly int|null $offset,
        private readonly Module $module,
        private readonly bool $countOnly,
    ) {
    }

    public function getArchives(): array
    {
        return $this->archives;
    }

    public function getFeatured(): bool|null
    {
        return $this->featured;
    }

    public function getLimit(): int|null
    {
        return $this->limit;
    }

    public function getOffset(): int|null
    {
        return $this->offset;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * Whether this query is for counting the total amount of news items.
     */
    public function isCountOnly(): bool
    {
        return $this->countOnly;
    }

    public function addColumn(string $column): self
    {
        $this->columns[] = $column;

        return $this;
    }

    public function addColumns(array $columns): self
    {
        $this->columns = [...$this->columns, ...$columns];

        return $this;
    }

    /**
     * @param list<string> $columns
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function addValue($value): self
    {
        $this->values[] = $value;

        return $this;
    }

    public function addValues(array $values): self
    {
        $this->values = [...$this->values, ...$values];

        return $this;
    }

    /**
     * @param list<mixed> $values
     */
    public function setValues(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return list<mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function hasData(): bool
    {
        return [] !== $this->columns || [] !== $this->options;
    }

    public function addOption(string $key, $option, bool $overwrite = false): self
    {
        if (!isset($this->options[$key]) || $overwrite) {
            $this->options[$key] = $option;
        }

        return $this;
    }

    public function addOptions(array $options, bool $overwrite = false): self
    {
        if ($overwrite) {
            $this->options = [...$this->options, ...$options];
        } else {
            $this->options = [...$options, ...$this->options];
        }

        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Whether the default news list parameters should be added.
     */
    public function setAddDefaults(bool $addDefaults): self
    {
        $this->addDefaults = $addDefaults;

        return $this;
    }

    public function getAddDefaults(): bool
    {
        return $this->addDefaults;
    }

    /**
     * Whether the news list should not output any news at all.
     */
    public function setForceEmptyResult(bool $forceEmptyResult): self
    {
        $this->forceEmptyResult = $forceEmptyResult;

        return $this;
    }

    public function getForceEmptyResult(): bool
    {
        return $this->forceEmptyResult;
    }
}
