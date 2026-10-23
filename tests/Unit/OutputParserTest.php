<?php

use App\Support\OutputParser;

it('parses a find listing sorted by name', function () {
    $rows = OutputParser::listing("f\t120\t2026-10-20 10:00\twww\t644\tindex.php\nd\t4096\t2026-10-19 09:30\troot\t755\tapp\n");

    expect($rows)->toBe([
        ['name' => 'app', 'type' => 'dir', 'size' => 4096, 'modified' => '2026-10-19 09:30', 'owner' => 'root', 'mode' => '755'],
        ['name' => 'index.php', 'type' => 'file', 'size' => 120, 'modified' => '2026-10-20 10:00', 'owner' => 'www', 'mode' => '644'],
    ]);
});

it('sorts a tree so children follow their parent', function () {
    $rows = OutputParser::tree("1\td\ta-b\n2\tf\ta/x.txt\n1\td\ta\n");

    expect(array_column($rows, 'path'))->toBe(['a', 'a/x.txt', 'a-b']);
});

it('parses ps output keeping spaces in the command', function () {
    $output = "    PID USER     %CPU %MEM     ELAPSED COMMAND\n   812 www-data 12.5  3.1    01:02:03 php-fpm: pool www\n";

    expect(OutputParser::processes($output))->toBe([
        ['pid' => 812, 'user' => 'www-data', 'cpu' => 12.5, 'mem' => 3.1, 'elapsed' => '01:02:03', 'command' => 'php-fpm: pool www'],
    ]);
});

it('parses df -hP output', function () {
    $output = "Filesystem      Size  Used Avail Use% Mounted on\n/dev/sda1        50G   20G   28G  42% /\n";

    expect(OutputParser::disks($output))->toBe([
        ['filesystem' => '/dev/sda1', 'size' => '50G', 'used' => '20G', 'available' => '28G', 'use' => '42%', 'mounted_on' => '/'],
    ]);
});

it('parses free -h output, padding the shorter swap row', function () {
    $output = "               total        used        free      shared  buff/cache   available\nMem:           3.8Gi       1.2Gi       400Mi        10Mi       2.2Gi       2.4Gi\nSwap:          1.0Gi          0B       1.0Gi\n";

    $rows = OutputParser::memory($output);

    expect($rows[0])->toMatchArray(['type' => 'Mem', 'total' => '3.8Gi', 'available' => '2.4Gi'])
        ->and($rows[1])->toMatchArray(['type' => 'Swap', 'total' => '1.0Gi', 'shared' => null]);
});

it('parses grep -H -n matches, keeping colons in the text', function () {
    expect(OutputParser::matches("/var/log/app.log:42:ERROR: db: timeout\n"))->toBe([
        ['file' => '/var/log/app.log', 'line' => 42, 'text' => 'ERROR: db: timeout'],
    ]);
});
