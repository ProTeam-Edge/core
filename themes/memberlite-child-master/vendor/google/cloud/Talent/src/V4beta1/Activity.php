<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/talent/v4beta1/profile.proto

namespace Google\Cloud\Talent\V4beta1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Resource that represents an individual or collaborative activity participated
 * in by a candidate, for example, an open-source project, a class assignment,
 * and so on.
 *
 * Generated from protobuf message <code>google.cloud.talent.v4beta1.Activity</code>
 */
class Activity extends \Google\Protobuf\Internal\Message
{
    /**
     * Activity display name.
     * Number of characters allowed is 100.
     *
     * Generated from protobuf field <code>string display_name = 1;</code>
     */
    private $display_name = '';
    /**
     * Activity description.
     * Number of characters allowed is 100,000.
     *
     * Generated from protobuf field <code>string description = 2;</code>
     */
    private $description = '';
    /**
     * Activity URI.
     * Number of characters allowed is 4,000.
     *
     * Generated from protobuf field <code>string uri = 3;</code>
     */
    private $uri = '';
    /**
     * The first creation date of the activity.
     *
     * Generated from protobuf field <code>.google.type.Date create_date = 4;</code>
     */
    private $create_date = null;
    /**
     * The last update date of the activity.
     *
     * Generated from protobuf field <code>.google.type.Date update_date = 5;</code>
     */
    private $update_date = null;
    /**
     * A list of team members involved in this activity.
     * Number of characters allowed is 100.
     * The limitation for max number of team members is 50.
     *
     * Generated from protobuf field <code>repeated string team_members = 6;</code>
     */
    private $team_members;
    /**
     * A list of skills used in this activity.
     * The limitation for max number of skills used is 50.
     *
     * Generated from protobuf field <code>repeated .google.cloud.talent.v4beta1.Skill skills_used = 7;</code>
     */
    private $skills_used;
    /**
     * Output only. Activity name snippet shows how the [display_name][google.cloud.talent.v4beta1.Activity.display_name] is related to a search
     * query. It's empty if the [display_name][google.cloud.talent.v4beta1.Activity.display_name] isn't related to the search
     * query.
     *
     * Generated from protobuf field <code>string activity_name_snippet = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $activity_name_snippet = '';
    /**
     * Output only. Activity description snippet shows how the
     * [description][google.cloud.talent.v4beta1.Activity.description] is related to a search query. It's empty if the
     * [description][google.cloud.talent.v4beta1.Activity.description] isn't related to the search query.
     *
     * Generated from protobuf field <code>string activity_description_snippet = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $activity_description_snippet = '';
    /**
     * Output only. Skill used snippet shows how the corresponding
     * [skills_used][google.cloud.talent.v4beta1.Activity.skills_used] are related to a search query. It's empty if the
     * corresponding [skills_used][google.cloud.talent.v4beta1.Activity.skills_used] are not related to the search query.
     *
     * Generated from protobuf field <code>repeated string skills_used_snippet = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    private $skills_used_snippet;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $display_name
     *           Activity display name.
     *           Number of characters allowed is 100.
     *     @type string $description
     *           Activity description.
     *           Number of characters allowed is 100,000.
     *     @type string $uri
     *           Activity URI.
     *           Number of characters allowed is 4,000.
     *     @type \Google\Type\Date $create_date
     *           The first creation date of the activity.
     *     @type \Google\Type\Date $update_date
     *           The last update date of the activity.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $team_members
     *           A list of team members involved in this activity.
     *           Number of characters allowed is 100.
     *           The limitation for max number of team members is 50.
     *     @type \Google\Cloud\Talent\V4beta1\Skill[]|\Google\Protobuf\Internal\RepeatedField $skills_used
     *           A list of skills used in this activity.
     *           The limitation for max number of skills used is 50.
     *     @type string $activity_name_snippet
     *           Output only. Activity name snippet shows how the [display_name][google.cloud.talent.v4beta1.Activity.display_name] is related to a search
     *           query. It's empty if the [display_name][google.cloud.talent.v4beta1.Activity.display_name] isn't related to the search
     *           query.
     *     @type string $activity_description_snippet
     *           Output only. Activity description snippet shows how the
     *           [description][google.cloud.talent.v4beta1.Activity.description] is related to a search query. It's empty if the
     *           [description][google.cloud.talent.v4beta1.Activity.description] isn't related to the search query.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $skills_used_snippet
     *           Output only. Skill used snippet shows how the corresponding
     *           [skills_used][google.cloud.talent.v4beta1.Activity.skills_used] are related to a search query. It's empty if the
     *           corresponding [skills_used][google.cloud.talent.v4beta1.Activity.skills_used] are not related to the search query.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Cloud\Talent\V4Beta1\Profile::initOnce();
        parent::__construct($data);
    }

    /**
     * Activity display name.
     * Number of characters allowed is 100.
     *
     * Generated from protobuf field <code>string display_name = 1;</code>
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Activity display name.
     * Number of characters allowed is 100.
     *
     * Generated from protobuf field <code>string display_name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setDisplayName($var)
    {
        GPBUtil::checkString($var, True);
        $this->display_name = $var;

        return $this;
    }

    /**
     * Activity description.
     * Number of characters allowed is 100,000.
     *
     * Generated from protobuf field <code>string description = 2;</code>
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Activity description.
     * Number of characters allowed is 100,000.
     *
     * Generated from protobuf field <code>string description = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setDescription($var)
    {
        GPBUtil::checkString($var, True);
        $this->description = $var;

        return $this;
    }

    /**
     * Activity URI.
     * Number of characters allowed is 4,000.
     *
     * Generated from protobuf field <code>string uri = 3;</code>
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Activity URI.
     * Number of characters allowed is 4,000.
     *
     * Generated from protobuf field <code>string uri = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setUri($var)
    {
        GPBUtil::checkString($var, True);
        $this->uri = $var;

        return $this;
    }

    /**
     * The first creation date of the activity.
     *
     * Generated from protobuf field <code>.google.type.Date create_date = 4;</code>
     * @return \Google\Type\Date
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * The first creation date of the activity.
     *
     * Generated from protobuf field <code>.google.type.Date create_date = 4;</code>
     * @param \Google\Type\Date $var
     * @return $this
     */
    public function setCreateDate($var)
    {
        GPBUtil::checkMessage($var, \Google\Type\Date::class);
        $this->create_date = $var;

        return $this;
    }

    /**
     * The last update date of the activity.
     *
     * Generated from protobuf field <code>.google.type.Date update_date = 5;</code>
     * @return \Google\Type\Date
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * The last update date of the activity.
     *
     * Generated from protobuf field <code>.google.type.Date update_date = 5;</code>
     * @param \Google\Type\Date $var
     * @return $this
     */
    public function setUpdateDate($var)
    {
        GPBUtil::checkMessage($var, \Google\Type\Date::class);
        $this->update_date = $var;

        return $this;
    }

    /**
     * A list of team members involved in this activity.
     * Number of characters allowed is 100.
     * The limitation for max number of team members is 50.
     *
     * Generated from protobuf field <code>repeated string team_members = 6;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getTeamMembers()
    {
        return $this->team_members;
    }

    /**
     * A list of team members involved in this activity.
     * Number of characters allowed is 100.
     * The limitation for max number of team members is 50.
     *
     * Generated from protobuf field <code>repeated string team_members = 6;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setTeamMembers($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->team_members = $arr;

        return $this;
    }

    /**
     * A list of skills used in this activity.
     * The limitation for max number of skills used is 50.
     *
     * Generated from protobuf field <code>repeated .google.cloud.talent.v4beta1.Skill skills_used = 7;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getSkillsUsed()
    {
        return $this->skills_used;
    }

    /**
     * A list of skills used in this activity.
     * The limitation for max number of skills used is 50.
     *
     * Generated from protobuf field <code>repeated .google.cloud.talent.v4beta1.Skill skills_used = 7;</code>
     * @param \Google\Cloud\Talent\V4beta1\Skill[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setSkillsUsed($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Cloud\Talent\V4beta1\Skill::class);
        $this->skills_used = $arr;

        return $this;
    }

    /**
     * Output only. Activity name snippet shows how the [display_name][google.cloud.talent.v4beta1.Activity.display_name] is related to a search
     * query. It's empty if the [display_name][google.cloud.talent.v4beta1.Activity.display_name] isn't related to the search
     * query.
     *
     * Generated from protobuf field <code>string activity_name_snippet = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getActivityNameSnippet()
    {
        return $this->activity_name_snippet;
    }

    /**
     * Output only. Activity name snippet shows how the [display_name][google.cloud.talent.v4beta1.Activity.display_name] is related to a search
     * query. It's empty if the [display_name][google.cloud.talent.v4beta1.Activity.display_name] isn't related to the search
     * query.
     *
     * Generated from protobuf field <code>string activity_name_snippet = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setActivityNameSnippet($var)
    {
        GPBUtil::checkString($var, True);
        $this->activity_name_snippet = $var;

        return $this;
    }

    /**
     * Output only. Activity description snippet shows how the
     * [description][google.cloud.talent.v4beta1.Activity.description] is related to a search query. It's empty if the
     * [description][google.cloud.talent.v4beta1.Activity.description] isn't related to the search query.
     *
     * Generated from protobuf field <code>string activity_description_snippet = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getActivityDescriptionSnippet()
    {
        return $this->activity_description_snippet;
    }

    /**
     * Output only. Activity description snippet shows how the
     * [description][google.cloud.talent.v4beta1.Activity.description] is related to a search query. It's empty if the
     * [description][google.cloud.talent.v4beta1.Activity.description] isn't related to the search query.
     *
     * Generated from protobuf field <code>string activity_description_snippet = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setActivityDescriptionSnippet($var)
    {
        GPBUtil::checkString($var, True);
        $this->activity_description_snippet = $var;

        return $this;
    }

    /**
     * Output only. Skill used snippet shows how the corresponding
     * [skills_used][google.cloud.talent.v4beta1.Activity.skills_used] are related to a search query. It's empty if the
     * corresponding [skills_used][google.cloud.talent.v4beta1.Activity.skills_used] are not related to the search query.
     *
     * Generated from protobuf field <code>repeated string skills_used_snippet = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getSkillsUsedSnippet()
    {
        return $this->skills_used_snippet;
    }

    /**
     * Output only. Skill used snippet shows how the corresponding
     * [skills_used][google.cloud.talent.v4beta1.Activity.skills_used] are related to a search query. It's empty if the
     * corresponding [skills_used][google.cloud.talent.v4beta1.Activity.skills_used] are not related to the search query.
     *
     * Generated from protobuf field <code>repeated string skills_used_snippet = 10 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setSkillsUsedSnippet($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->skills_used_snippet = $arr;

        return $this;
    }

}

