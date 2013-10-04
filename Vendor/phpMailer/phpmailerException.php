<?php
namespace PhpMailer;

use Exception;

/**
 * Exception handler for PHPMailer
 *
 * @package PHPMailer
 */
class phpmailerException extends Exception
{
    /**
     * Prettify error message output
     *
     * @return string
     */
    public function errorMessage()
    {
        $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";

        return $errorMsg;
    }
}
