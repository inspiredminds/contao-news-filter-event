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
    private array $archives;
    private ?bool $featured;
    private ?int $limit;
    private ?int $offset;
    private Module $module;
    private bool $countOnly;
    /** @var list<string> */
    private array $columns = [];
    /** @var list<mixed> */
    private array $values = [];
    private array $options = [];
    private bool $addDefaults = true;

    public function __construct(array $archives, ?bool $featured, ?int $limit, ?int $offset, Module $module, bool $countOnly)
    {
        $this->archives = $archives;
        $this->featured = $featured;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->module = $module;
        $this->countOnly = $countOnly;
    }

    public function getArchives(): array
    {
        return $this->archives;
    }

    public function getFeatured(): ?bool
    {
        return $this->featured;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
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
        $this->columns = array_merge($this->columns, $columns);

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
        $this->values = array_merge($this->values, $values);

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
        return !empty($this->columns) || !empty($this->options);
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
            $this->options = array_merge($this->options, $options);
        } else {
            $this->options = array_merge($options, $this->options);
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
}
