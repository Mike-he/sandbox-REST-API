<?php

namespace Sandbox\ApiBundle\Constants;

class ProductOrderMessage
{
    const MEETING_START_MESSAGE = '您的会议室将在10分钟后开始使用';
    const MEETING_END_MESSAGE = '您的会议室将在10分钟后到期';

    const WORKSPACE_START_MESSAGE = '您的工位将在明天开始使用';
    const WORKSPACE_END_MESSAGE = '您的工位将在明天到期';

    const OFFICE_START_MESSAGE = '您的办公室将在明天开始使用';
    const OFFICE_END_MESSAGE = '您的办公室将在七天后到期';

    const APPOINT_MESSAGE_PART1 = '您已被授权进入“';
    const APPOINT_MESSAGE_PART2 = '”房间了，你可以用Sandbox3的卡片开启该房间的门禁。';

    const CANCEL_ORDER_MESSAGE_PART1 = '您在“';
    const CANCEL_ORDER_MESSAGE_PART2 = '”的授权已经被取消了。';
}
