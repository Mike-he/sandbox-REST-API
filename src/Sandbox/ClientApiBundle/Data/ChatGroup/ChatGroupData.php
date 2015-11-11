<?php

namespace Sandbox\ClientApiBundle\Data\ChatGroup;

/**
 * Chat Group Incoming Data.
 */
class ChatGroupData
{
    /**
     * @var array
     */
    private $memberIds;

    /**
     * @var string
     */
    private $name;

    /**
     * Set memberIds.
     *
     * @param array $memberIds
     *
     * @return ChatGroupData
     */
    public function setMemberIds($memberIds)
    {
        $this->memberIds = $memberIds;

        return $this;
    }

    /**
     * Get memberIds.
     *
     * @return array
     */
    public function getMemberIds()
    {
        return $this->memberIds;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ChatGroupData
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
