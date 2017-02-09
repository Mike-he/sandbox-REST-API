<?php

namespace Sandbox\ApiBundle\Constants;

class LocationConstants
{
    const LOCATION_CITY_PREFIX = 'location.city.';
    const TRANS_BUILDING_SERVICE = 'building.service.';
    const TRANS_BUILDING_SORT = 'building.sort.';
    const TRANS_BUILDING_TAG = 'building.tag.';
    const TRANS_BUILDING_FILTER = 'building.filter.';
    const TRANS_BUILDING_SUB_FILTER = 'building.subFilter.';
    const TRANS_BUILDING_FILTER_ALL_TITLE = 'building.filter.all_title';

    public static $pi = 3.1415926535897932384626;

    // FILTER NAME
    const FILTER_SPACE_TYPE = 'space_type';
    const FILTER_SORT_BY = 'sort_by';
    const FILTER_FILTER = 'filter';

    // FILTER TYPE
    const TAG = 'tag';
    const RADIO = 'radio';

    // SUB FILTER NAME
    const SUB_FILTER_SPACE_TYPE = 'space_type';
    const SUB_FILTER_SORT_BY = 'sort_by';
    const SUB_FILTER_CONFIGURE = 'configure';
    const SUB_FILTER_TAG = 'tag';

    // FILTER QUERY PARAM KEY
    const QUERY_ROOM_TYPES = 'room_types[]';
    const QUERY_SORT_BY = 'sort_by';
    const QUERY_BUILDING_TAGS = 'building_tags[]';
    const QUERY_BUILDING_SERVICES = 'building_services[]';

    // SORT BY KEY
    const SORT_BY_SMART = 'smart';
    const SORT_BY_DISTANCE = 'distance';
    const SORT_BY_START = 'star';
    const SORT_BY_DEFAULT_KEY = self::SORT_BY_SMART;

    public static $plainTextSortKeys = [
        self::SORT_BY_SMART,
        self::SORT_BY_DISTANCE,
        self::SORT_BY_START,
    ];
}
