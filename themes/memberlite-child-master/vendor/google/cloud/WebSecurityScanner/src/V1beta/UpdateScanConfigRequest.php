<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/websecurityscanner/v1beta/web_security_scanner.proto

namespace Google\Cloud\WebSecurityScanner\V1beta;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request for the `UpdateScanConfigRequest` method.
 *
 * Generated from protobuf message <code>google.cloud.websecurityscanner.v1beta.UpdateScanConfigRequest</code>
 */
class UpdateScanConfigRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The ScanConfig to be updated. The name field must be set to identify the
     * resource to be updated. The values of fields not covered by the mask
     * will be ignored.
     *
     * Generated from protobuf field <code>.google.cloud.websecurityscanner.v1beta.ScanConfig scan_config = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $scan_config = null;
    /**
     * Required. The update mask applies to the resource. For the `FieldMask` definition,
     * see
     * https://developers.google.com/protocol-buffers/docs/reference/google.protobuf#fieldmask
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $update_mask = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Cloud\WebSecurityScanner\V1beta\ScanConfig $scan_config
     *           Required. The ScanConfig to be updated. The name field must be set to identify the
     *           resource to be updated. The values of fields not covered by the mask
     *           will be ignored.
     *     @type \Google\Protobuf\FieldMask $update_mask
     *           Required. The update mask applies to the resource. For the `FieldMask` definition,
     *           see
     *           https://developers.google.com/protocol-buffers/docs/reference/google.protobuf#fieldmask
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Websecurityscanner\V1Beta\WebSecurityScanner::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The ScanConfig to be updated. The name field must be set to identify the
     * resource to be updated. The values of fields not covered by the mask
     * will be ignored.
     *
     * Generated from protobuf field <code>.google.cloud.websecurityscanner.v1beta.ScanConfig scan_config = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Cloud\WebSecurityScanner\V1beta\ScanConfig
     */
    public function getScanConfig()
    {
        return $this->scan_config;
    }

    /**
     * Required. The ScanConfig to be updated. The name field must be set to identify the
     * resource to be updated. The values of fields not covered by the mask
     * will be ignored.
     *
     * Generated from protobuf field <code>.google.cloud.websecurityscanner.v1beta.ScanConfig scan_config = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Cloud\WebSecurityScanner\V1beta\ScanConfig $var
     * @return $this
     */
    public function setScanConfig($var)
    {
        GPBUtil::checkMessage($var, \Google\Cloud\WebSecurityScanner\V1beta\ScanConfig::class);
        $this->scan_config = $var;

        return $this;
    }

    /**
     * Required. The update mask applies to the resource. For the `FieldMask` definition,
     * see
     * https://developers.google.com/protocol-buffers/docs/reference/google.protobuf#fieldmask
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\FieldMask
     */
    public function getUpdateMask()
    {
        return $this->update_mask;
    }

    /**
     * Required. The update mask applies to the resource. For the `FieldMask` definition,
     * see
     * https://developers.google.com/protocol-buffers/docs/reference/google.protobuf#fieldmask
     *
     * Generated from protobuf field <code>.google.protobuf.FieldMask update_mask = 3 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Protobuf\FieldMask $var
     * @return $this
     */
    public function setUpdateMask($var)
    {
        GPBUtil::checkMessage($var, \Google\Protobuf\FieldMask::class);
        $this->update_mask = $var;

        return $this;
    }

}

