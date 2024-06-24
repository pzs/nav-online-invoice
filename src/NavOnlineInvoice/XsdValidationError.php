<?php

namespace NavOnlineInvoice;

use Exception;


class XsdValidationError extends Exception
{

    /**
     * @var array<int,string>
     */
    protected static array $levelMap = array(
        LIBXML_ERR_WARNING => "Warning",
        LIBXML_ERR_ERROR => "Error",
        LIBXML_ERR_FATAL => "Fatal Error"
    );


    function __construct(
        /**
         * @var \LibXMLError[]
         */
        protected array $errors,
    ) {
        $message = $this->createErrorMessage();
        parent::__construct($message);
    }


    /**
     * @return \LibXMLError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }


    protected function createErrorMessage(): string
    {
        $messages = array();

        foreach ($this->errors as $error) {
            $messages[] = self::$levelMap[$error->level] . ": " . $error->message;
        }

        return implode("\n", $messages);
    }
}
