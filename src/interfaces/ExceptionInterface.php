<?php
namespace Avenue\Interfaces;

interface ExceptionInterface
{
    /**
     * Get the exception error message.
     */
    public function getMessage();

    /**
     * Get the exception error code.
     */
    public function getCode();

    /**
     * Get the file name.
     */
    public function getFile();

    /**
     * Get the line number in file.
     */
    public function getLine();

    /**
     * Get the backtrace in array.
     */
    public function getTrace();

    /**
     * Get the trace as string format.
     */
    public function getTraceAsString();

    /**
     * String magic method to render the formatted exception details.
     */
    public function __toString();

    /**
     * Get current app ID.
     */
    public function getAppId();
}