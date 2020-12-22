<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/devtools/clouderrorreporting/v1beta1/report_errors_service.proto

namespace Google\Cloud\ErrorReporting\V1beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * An error event which is reported to the Error Reporting system.
 *
 * Generated from protobuf message <code>google.devtools.clouderrorreporting.v1beta1.ReportedErrorEvent</code>
 */
class ReportedErrorEvent extends \Google\Protobuf\Internal\Message
{
    /**
     * Optional. Time when the event occurred.
     * If not provided, the time when the event was received by the
     * Error Reporting system will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp event_time = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $event_time = null;
    /**
     * Required. The service context in which this error has occurred.
     *
     * Generated from protobuf field <code>.google.devtools.clouderrorreporting.v1beta1.ServiceContext service_context = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $service_context = null;
    /**
     * Required. The error message.
     * If no `context.reportLocation` is provided, the message must contain a
     * header (typically consisting of the exception type name and an error
     * message) and an exception stack trace in one of the supported programming
     * languages and formats.
     * Supported languages are Java, Python, JavaScript, Ruby, C#, PHP, and Go.
     * Supported stack trace formats are:
     * * **Java**: Must be the return value of
     * [`Throwable.printStackTrace()`](https://docs.oracle.com/javase/7/docs/api/java/lang/Throwable.html#printStackTrace%28%29).
     * * **Python**: Must be the return value of
     * [`traceback.format_exc()`](https://docs.python.org/2/library/traceback.html#traceback.format_exc).
     * * **JavaScript**: Must be the value of
     * [`error.stack`](https://github.com/v8/v8/wiki/Stack-Trace-API) as returned
     * by V8.
     * * **Ruby**: Must contain frames returned by
     * [`Exception.backtrace`](https://ruby-doc.org/core-2.2.0/Exception.html#method-i-backtrace).
     * * **C#**: Must be the return value of
     * [`Exception.ToString()`](https://msdn.microsoft.com/en-us/library/system.exception.tostring.aspx).
     * * **PHP**: Must start with `PHP (Notice|Parse error|Fatal error|Warning)`
     * and contain the result of
     * [`(string)$exception`](http://php.net/manual/en/exception.tostring.php).
     * * **Go**: Must be the return value of
     * [`runtime.Stack()`](https://golang.org/pkg/runtime/debug/#Stack).
     *
     * Generated from protobuf field <code>string message = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $message = '';
    /**
     * Optional. A description of the context in which the error occurred.
     *
     * Generated from protobuf field <code>.google.devtools.clouderrorreporting.v1beta1.ErrorContext context = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     */
    private $context = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Protobuf\Timestamp $event_time
     *           Optional. Time when the event occurred.
     *           If not provided, the time when the event was received by the
     *           Error Reporting system will be used.
     *     @type \Google\Cloud\ErrorReporting\V1beta1\ServiceContext $service_context
     *           Required. The service context in which this error has occurred.
     *     @type string $message
     *           Required. The error message.
     *           If no `context.reportLocation` is provided, the message must contain a
     *           header (typically consisting of the exception type name and an error
     *           message) and an exception stack trace in one of the supported programming
     *           languages and formats.
     *           Supported languages are Java, Python, JavaScript, Ruby, C#, PHP, and Go.
     *           Supported stack trace formats are:
     *           * **Java**: Must be the return value of
     *           [`Throwable.printStackTrace()`](https://docs.oracle.com/javase/7/docs/api/java/lang/Throwable.html#printStackTrace%28%29).
     *           * **Python**: Must be the return value of
     *           [`traceback.format_exc()`](https://docs.python.org/2/library/traceback.html#traceback.format_exc).
     *           * **JavaScript**: Must be the value of
     *           [`error.stack`](https://github.com/v8/v8/wiki/Stack-Trace-API) as returned
     *           by V8.
     *           * **Ruby**: Must contain frames returned by
     *           [`Exception.backtrace`](https://ruby-doc.org/core-2.2.0/Exception.html#method-i-backtrace).
     *           * **C#**: Must be the return value of
     *           [`Exception.ToString()`](https://msdn.microsoft.com/en-us/library/system.exception.tostring.aspx).
     *           * **PHP**: Must start with `PHP (Notice|Parse error|Fatal error|Warning)`
     *           and contain the result of
     *           [`(string)$exception`](http://php.net/manual/en/exception.tostring.php).
     *           * **Go**: Must be the return value of
     *           [`runtime.Stack()`](https://golang.org/pkg/runtime/debug/#Stack).
     *     @type \Google\Cloud\ErrorReporting\V1beta1\ErrorContext $context
     *           Optional. A description of the context in which the error occurred.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Devtools\Clouderrorreporting\V1Beta1\ReportErrorsService::initOnce();
        parent::__construct($data);
    }

    /**
     * Optional. Time when the event occurred.
     * If not provided, the time when the event was received by the
     * Error Reporting system will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp event_time = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Protobuf\Timestamp
     */
    public function getEventTime()
    {
        return $this->event_time;
    }

    /**
     * Optional. Time when the event occurred.
     * If not provided, the time when the event was received by the
     * Error Reporting system will be used.
     *
     * Generated from protobuf field <code>.google.protobuf.Timestamp event_time = 1 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param \Google\Protobuf\Timestamp $var
     * @return $this
     */
    public function setEventTime($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\Timestamp::class);
        $this->event_time = $var;

        return $this;
    }

    /**
     * Required. The service context in which this error has occurred.
     *
     * Generated from protobuf field <code>.google.devtools.clouderrorreporting.v1beta1.ServiceContext service_context = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\ErrorReporting\V1beta1\ServiceContext
     */
    public function getServiceContext()
    {
        return $this->service_context;
    }

    /**
     * Required. The service context in which this error has occurred.
     *
     * Generated from protobuf field <code>.google.devtools.clouderrorreporting.v1beta1.ServiceContext service_context = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\ErrorReporting\V1beta1\ServiceContext $var
     * @return $this
     */
    public function setServiceContext($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\ErrorReporting\V1beta1\ServiceContext::class);
        $this->service_context = $var;

        return $this;
    }

    /**
     * Required. The error message.
     * If no `context.reportLocation` is provided, the message must contain a
     * header (typically consisting of the exception type name and an error
     * message) and an exception stack trace in one of the supported programming
     * languages and formats.
     * Supported languages are Java, Python, JavaScript, Ruby, C#, PHP, and Go.
     * Supported stack trace formats are:
     * * **Java**: Must be the return value of
     * [`Throwable.printStackTrace()`](https://docs.oracle.com/javase/7/docs/api/java/lang/Throwable.html#printStackTrace%28%29).
     * * **Python**: Must be the return value of
     * [`traceback.format_exc()`](https://docs.python.org/2/library/traceback.html#traceback.format_exc).
     * * **JavaScript**: Must be the value of
     * [`error.stack`](https://github.com/v8/v8/wiki/Stack-Trace-API) as returned
     * by V8.
     * * **Ruby**: Must contain frames returned by
     * [`Exception.backtrace`](https://ruby-doc.org/core-2.2.0/Exception.html#method-i-backtrace).
     * * **C#**: Must be the return value of
     * [`Exception.ToString()`](https://msdn.microsoft.com/en-us/library/system.exception.tostring.aspx).
     * * **PHP**: Must start with `PHP (Notice|Parse error|Fatal error|Warning)`
     * and contain the result of
     * [`(string)$exception`](http://php.net/manual/en/exception.tostring.php).
     * * **Go**: Must be the return value of
     * [`runtime.Stack()`](https://golang.org/pkg/runtime/debug/#Stack).
     *
     * Generated from protobuf field <code>string message = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Required. The error message.
     * If no `context.reportLocation` is provided, the message must contain a
     * header (typically consisting of the exception type name and an error
     * message) and an exception stack trace in one of the supported programming
     * languages and formats.
     * Supported languages are Java, Python, JavaScript, Ruby, C#, PHP, and Go.
     * Supported stack trace formats are:
     * * **Java**: Must be the return value of
     * [`Throwable.printStackTrace()`](https://docs.oracle.com/javase/7/docs/api/java/lang/Throwable.html#printStackTrace%28%29).
     * * **Python**: Must be the return value of
     * [`traceback.format_exc()`](https://docs.python.org/2/library/traceback.html#traceback.format_exc).
     * * **JavaScript**: Must be the value of
     * [`error.stack`](https://github.com/v8/v8/wiki/Stack-Trace-API) as returned
     * by V8.
     * * **Ruby**: Must contain frames returned by
     * [`Exception.backtrace`](https://ruby-doc.org/core-2.2.0/Exception.html#method-i-backtrace).
     * * **C#**: Must be the return value of
     * [`Exception.ToString()`](https://msdn.microsoft.com/en-us/library/system.exception.tostring.aspx).
     * * **PHP**: Must start with `PHP (Notice|Parse error|Fatal error|Warning)`
     * and contain the result of
     * [`(string)$exception`](http://php.net/manual/en/exception.tostring.php).
     * * **Go**: Must be the return value of
     * [`runtime.Stack()`](https://golang.org/pkg/runtime/debug/#Stack).
     *
     * Generated from protobuf field <code>string message = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setMessage($var)
    {
        GPBUtil::checkString($var, True);
        $this->message = $var;

        return $this;
    }

    /**
     * Optional. A description of the context in which the error occurred.
     *
     * Generated from protobuf field <code>.google.devtools.clouderrorreporting.v1beta1.ErrorContext context = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @return \Google\Cloud\ErrorReporting\V1beta1\ErrorContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Optional. A description of the context in which the error occurred.
     *
     * Generated from protobuf field <code>.google.devtools.clouderrorreporting.v1beta1.ErrorContext context = 4 [(.google.api.field_behavior) = OPTIONAL];</code>
     * @param \Google\Cloud\ErrorReporting\V1beta1\ErrorContext $var
     * @return $this
     */
    public function setContext($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\ErrorReporting\V1beta1\ErrorContext::class);
        $this->context = $var;

        return $this;
    }

}

