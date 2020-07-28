<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/secretmanager/v1/service.proto

namespace Google\Cloud\SecretManager\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for [SecretManagerService.CreateSecret][google.cloud.secretmanager.v1.SecretManagerService.CreateSecret].
 *
 * Generated from protobuf message <code>google.cloud.secretmanager.v1.CreateSecretRequest</code>
 */
class CreateSecretRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The resource name of the project to associate with the
     * [Secret][google.cloud.secretmanager.v1.Secret], in the format `projects/&#42;`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     */
    private $parent = '';
    /**
     * Required. This must be unique within the project.
     *
     * Generated from protobuf field <code>string secret_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $secret_id = '';
    /**
     * Required. A [Secret][google.cloud.secretmanager.v1.Secret] with initial field values.
     *
     * Generated from protobuf field <code>.google.cloud.secretmanager.v1.Secret secret = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $secret = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $parent
     *           Required. The resource name of the project to associate with the
     *           [Secret][google.cloud.secretmanager.v1.Secret], in the format `projects/&#42;`.
     *     @type string $secret_id
     *           Required. This must be unique within the project.
     *     @type \Google\Cloud\SecretManager\V1\Secret $secret
     *           Required. A [Secret][google.cloud.secretmanager.v1.Secret] with initial field values.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Secretmanager\V1\Service::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The resource name of the project to associate with the
     * [Secret][google.cloud.secretmanager.v1.Secret], in the format `projects/&#42;`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Required. The resource name of the project to associate with the
     * [Secret][google.cloud.secretmanager.v1.Secret], in the format `projects/&#42;`.
     *
     * Generated from protobuf field <code>string parent = 1 [(.google.api.field_behavior) = REQUIRED, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setParent($var)
    {
        GPBUtil::checkString($var, True);
        $this->parent = $var;

        return $this;
    }

    /**
     * Required. This must be unique within the project.
     *
     * Generated from protobuf field <code>string secret_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getSecretId()
    {
        return $this->secret_id;
    }

    /**
     * Required. This must be unique within the project.
     *
     * Generated from protobuf field <code>string secret_id = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setSecretId($var)
    {
        GPBUtil::checkString($var, True);
        $this->secret_id = $var;

        return $this;
    }

    /**
     * Required. A [Secret][google.cloud.secretmanager.v1.Secret] with initial field values.
     *
     * Generated from protobuf field <code>.google.cloud.secretmanager.v1.Secret secret = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\SecretManager\V1\Secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Required. A [Secret][google.cloud.secretmanager.v1.Secret] with initial field values.
     *
     * Generated from protobuf field <code>.google.cloud.secretmanager.v1.Secret secret = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\SecretManager\V1\Secret $var
     * @return $this
     */
    public function setSecret($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\SecretManager\V1\Secret::class);
        $this->secret = $var;

        return $this;
    }

}

