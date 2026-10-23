<?php

namespace App\DTOs;

class Connection
{
    public function __construct(
        public readonly string $name,
        public readonly string $host,
        public readonly ?int $port = null,
        public readonly ?string $username = null,
        public readonly ?string $privateKey = null,
    ) {}

    /**
     * `user@host`, or just `host` so ~/.ssh/config can supply the user.
     */
    public function target(): string
    {
        return $this->username !== null ? "{$this->username}@{$this->host}" : $this->host;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(string $name, array $data): self
    {
        return new self(
            name: $name,
            host: (string) ($data['host'] ?? ''),
            port: isset($data['port']) ? (int) $data['port'] : null,
            username: isset($data['username']) ? (string) $data['username'] : null,
            privateKey: isset($data['private_key']) ? (string) $data['private_key'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'private_key' => $this->privateKey,
        ];
    }
}
