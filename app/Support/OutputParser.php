<?php

namespace App\Support;

/**
 * Turns the plain-text output of the remote commands into rows. Every
 * remote command runs under LC_ALL=C, so the formats below are stable.
 */
class OutputParser
{
    private const TYPES = ['d' => 'dir', 'f' => 'file', 'l' => 'link'];

    /**
     * `find -printf '%y\t%s\t%TY-%Tm-%Td %TH:%TM\t%u\t%m\t%f\n'`
     *
     * @return list<array{name: string, type: string, size: int, modified: string, owner: string, mode: string}>
     */
    public static function listing(string $output): array
    {
        $rows = [];

        foreach (self::lines($output) as $line) {
            $fields = explode("\t", $line, 6);

            if (count($fields) < 6) {
                continue;
            }

            [$type, $size, $modified, $owner, $mode, $name] = $fields;

            $rows[] = [
                'name' => $name,
                'type' => self::TYPES[$type] ?? $type,
                'size' => (int) $size,
                'modified' => $modified,
                'owner' => $owner,
                'mode' => $mode,
            ];
        }

        usort($rows, static fn (array $a, array $b) => strcmp($a['name'], $b['name']));

        return $rows;
    }

    /**
     * `find -printf '%d\t%y\t%P\n'`, sorted so children follow their parent.
     *
     * @return list<array{path: string, type: string, depth: int}>
     */
    public static function tree(string $output): array
    {
        $rows = [];

        foreach (self::lines($output) as $line) {
            $fields = explode("\t", $line, 3);

            if (count($fields) < 3) {
                continue;
            }

            $rows[] = [
                'path' => $fields[2],
                'type' => self::TYPES[$fields[1]] ?? $fields[1],
                'depth' => (int) $fields[0],
            ];
        }

        // "/" → "\0" so "a/b" sorts right after "a", before "a-b".
        usort($rows, static fn (array $a, array $b) => strcmp(
            str_replace('/', "\0", $a['path']),
            str_replace('/', "\0", $b['path']),
        ));

        return $rows;
    }

    /**
     * `ps -eo pid,user,pcpu,pmem,etime,args`
     *
     * @return list<array{pid: int, user: string, cpu: float, mem: float, elapsed: string, command: string}>
     */
    public static function processes(string $output): array
    {
        $rows = [];

        foreach (array_slice(self::lines($output), 1) as $line) {
            $fields = preg_split('/\s+/', trim($line), 6) ?: [];

            if (count($fields) < 6) {
                continue;
            }

            $rows[] = [
                'pid' => (int) $fields[0],
                'user' => $fields[1],
                'cpu' => (float) $fields[2],
                'mem' => (float) $fields[3],
                'elapsed' => $fields[4],
                'command' => $fields[5],
            ];
        }

        return $rows;
    }

    /**
     * `df -hP`
     *
     * @return list<array{filesystem: string, size: string, used: string, available: string, use: string, mounted_on: string}>
     */
    public static function disks(string $output): array
    {
        $rows = [];

        foreach (array_slice(self::lines($output), 1) as $line) {
            $fields = preg_split('/\s+/', trim($line), 6) ?: [];

            if (count($fields) < 6) {
                continue;
            }

            $rows[] = [
                'filesystem' => $fields[0],
                'size' => $fields[1],
                'used' => $fields[2],
                'available' => $fields[3],
                'use' => $fields[4],
                'mounted_on' => $fields[5],
            ];
        }

        return $rows;
    }

    /**
     * `free -h`: a header row of column names, then `Mem:` / `Swap:` rows
     * (Swap has fewer values, the missing ones come back null).
     *
     * @return list<array<string, string|null>>
     */
    public static function memory(string $output): array
    {
        $lines = self::lines($output);
        $columns = preg_split('/\s+/', trim(array_shift($lines) ?? '')) ?: [];
        $rows = [];

        foreach ($lines as $line) {
            $fields = preg_split('/\s+/', trim($line)) ?: [];
            $row = ['type' => rtrim((string) array_shift($fields), ':')];

            foreach ($columns as $i => $column) {
                $row[$column] = $fields[$i] ?? null;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * `grep -H -n`: `file:line:text`.
     *
     * @return list<array{file: string, line: int, text: string}>
     */
    public static function matches(string $output): array
    {
        $rows = [];

        foreach (self::lines($output) as $line) {
            if (preg_match('/^(.*?):(\d+):(.*)$/', $line, $m) === 1) {
                $rows[] = ['file' => $m[1], 'line' => (int) $m[2], 'text' => $m[3]];
            }
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private static function lines(string $output): array
    {
        return array_values(array_filter(
            preg_split('/\r?\n/', $output) ?: [],
            static fn (string $line) => trim($line) !== '',
        ));
    }
}
