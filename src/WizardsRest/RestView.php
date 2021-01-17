<?php

namespace WizardsRest;

/**
 * An entity that represents a REST response. Usefull to pass status code with sucessful responses.
 */
class RestView
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var string|array
     */
    private $content;

    public function __construct(int $code, $content)
    {
        $this->code = $code;
        $this->content = $content;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return array|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param array|string $content
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }
}
