<?php

namespace App\Services;

use App\DTOs\Connection;
use App\Exceptions\ConnectionNotFoundException;
use JeffersonGoncalves\LaravelZero\JsonConfig\JsonConfigService;
use JeffersonGoncalves\LaravelZero\JsonConfig\Scopes\GlobalScope;

class ConnectionService
{
    private readonly JsonConfigService $store;

    public function __construct()
    {
        $this->store = new JsonConfigService(new GlobalScope('peek-cli'));
    }

    public function path(): string
    {
        return $this->store->path();
    }

    /**
     * @return array<string, Connection>
     */
    public function all(): array
    {
        $connections = [];
        $stored = $this->store->get('connections', []);

        foreach (is_array($stored) ? $stored : [] as $name => $data) {
            $connections[(string) $name] = Connection::fromArray((string) $name, is_array($data) ? $data : []);
        }

        return $connections;
    }

    public function get(string $name): ?Connection
    {
        return $this->all()[$name] ?? null;
    }

    public function getOrFail(string $name): Connection
    {
        return $this->get($name) ?? throw ConnectionNotFoundException::named($name);
    }

    public function save(Connection $connection): void
    {
        $this->store->set("connections.{$connection->name}", $connection->toArray());
    }

    public function forget(string $name): bool
    {
        if ($this->get($name) === null) {
            return false;
        }

        $this->store->forget("connections.{$name}");

        return true;
    }
}
