<?php

declare(strict_types=1);

/**
 * Fail fast when PHPUnit coverage is requested but PHP has no coverage driver.
 * PCOV is preferred for speed; Xdebug works if mode includes coverage (e.g. xdebug.mode=coverage).
 */
if (extension_loaded('pcov')) {
    exit(0);
}

if (extension_loaded('xdebug')) {
    exit(0);
}

fwrite(STDERR, "Code coverage requires the PCOV or Xdebug PHP extension.\n");
fwrite(STDERR, "Enable one of them in php.ini, then run this command again.\n");
fwrite(STDERR, "See README → Development & Testing → Generate code coverage report.\n");
exit(1);
