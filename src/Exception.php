<?php

namespace Okta;

/**
 * Okta\Exception class to pass through Okta responses
 *
 * @author Chris Kankiewicz <ckankiewicz@io.com>
 */
class Exception extends \Exception
{
    /** @var object The response object to handle */
    private $responseObject;

    /**
     * OKta\Exception constructor method
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($responseObject, Exception $previous = null, $code = 0)
    {
        parent::__construct('', $code, $previous);
        $this->responseObject = $responseObject;
    }

    /**
     * Return response object
     *
     * @return object Okta response object
     */
    public function getResponseObject()
    {
        return $this->responseObject;
    }

    /**
     * Return response error code
     *
     * @return string Error code
     */
    public function getErrorCode()
    {
        return $this->responseObject->errorCode;
    }

    /**
     * Return response error summary
     *
     * @return string Error summary
     */
    public function getErrorSummary()
    {
        return $this->responseObject->errorSummary;
    }

    /**
     * Return response error link
     *
     * @return string Error link
     */
    public function getErrorLink()
    {
        return $this->responseObject->errorLink;
    }

    /**
     * Return response error ID
     *
     * @return string Error ID
     */
    public function getErrorId()
    {
        return $this->responseObject->errorId;
    }

    /**
     * Return response error causes
     *
     * @param string        $key Specific key of error to fetch
     *
     * @return array|string      Array of error causes or specific error string
     */
    public function getErrorCauses($key = null)
    {
        if ($key >= 0) {
            return $this->responseObject->errorCauses[$key]->errorSummary;
        }

        return $this->responseObject->errorCauses;
    }
}
