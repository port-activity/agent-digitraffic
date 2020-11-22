<?php
namespace SMA\PAA\AGENT;

use exception;

class ApiConfig
{
    public function __construct(string $key, string $url, array $parameters)
    {

        if (!$key) {
            throw new Exception("No key set!");
        }

        if (!$url) {
            throw new Exception("No url set in!");
        }

        if (!preg_match("/^http/", $url)) {
            throw new Exception("Invalid url: " . $url);
        }

        $this->key = $key;
        $this->url = $url;
        $this->parameters = $parameters;
    }
    public function key(): string
    {
        return $this->key;
    }
    public function url(): string
    {
        return $this->url;
    }
    public function parameters(): array
    {
        return $this->parameters;
    }
}
