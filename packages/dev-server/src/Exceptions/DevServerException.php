<?php

declare(strict_types=1);

namespace Marko\DevServer\Exceptions;

use Marko\Core\Exceptions\MarkoException;

class DevServerException extends MarkoException
{
    public static function processFailedToStart(string $name, string $command): self
    {
        return new self("Process '{$name}' failed to start with command: {$command}. Check the command and run 'marko status' for details.");
    }

    public static function portInUse(int $port): self
    {
        return new self("Port {$port} is already in use. Please use --port=XXXX to pick a different port.");
    }

    public static function missingEntryPoint(): self
    {
        $bootstrapCode = <<<'PHP'
<?php

declare(strict_types=1);

use Marko\Core\Application;

require dirname(__DIR__) . '/vendor/autoload.php';

Application::boot(dirname(__DIR__))->handleRequest();
PHP;

        return new self("Missing public/index.php. Please create it with the following bootstrap code:\n\n{$bootstrapCode}");
    }
}
