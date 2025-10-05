<?php

declare(strict_types=1);

namespace Bring{
    class Api
    {
        public const BringRestURL = 'https://api.getbring.com/rest/v2/';
        public const BringImagesURL = 'https://web.getbring.com/assets/images/';
        public const BringLocalesURL = 'https://web.getbring.com/locale/articles.%s.json';
        public const Login = 'Login';
        public static $CURL_error_codes = [
            0  => 'UNKNOWN ERROR',
            1  => 'CURLE_UNSUPPORTED_PROTOCOL',
            2  => 'CURLE_FAILED_INIT',
            3  => 'CURLE_URL_MALFORMAT',
            4  => 'CURLE_URL_MALFORMAT_USER',
            5  => 'CURLE_COULDNT_RESOLVE_PROXY',
            6  => 'CURLE_COULDNT_RESOLVE_HOST',
            7  => 'CURLE_COULDNT_CONNECT',
            8  => 'CURLE_FTP_WEIRD_SERVER_REPLY',
            9  => 'CURLE_REMOTE_ACCESS_DENIED',
            11 => 'CURLE_FTP_WEIRD_PASS_REPLY',
            13 => 'CURLE_FTP_WEIRD_PASV_REPLY',
            14 => 'CURLE_FTP_WEIRD_227_FORMAT',
            15 => 'CURLE_FTP_CANT_GET_HOST',
            17 => 'CURLE_FTP_COULDNT_SET_TYPE',
            18 => 'CURLE_PARTIAL_FILE',
            19 => 'CURLE_FTP_COULDNT_RETR_FILE',
            21 => 'CURLE_QUOTE_ERROR',
            22 => 'CURLE_HTTP_RETURNED_ERROR',
            23 => 'CURLE_WRITE_ERROR',
            25 => 'CURLE_UPLOAD_FAILED',
            26 => 'CURLE_READ_ERROR',
            27 => 'CURLE_OUT_OF_MEMORY',
            28 => 'CURLE_OPERATION_TIMEDOUT',
            30 => 'CURLE_FTP_PORT_FAILED',
            31 => 'CURLE_FTP_COULDNT_USE_REST',
            33 => 'CURLE_RANGE_ERROR',
            34 => 'CURLE_HTTP_POST_ERROR',
            35 => 'CURLE_SSL_CONNECT_ERROR',
            36 => 'CURLE_BAD_DOWNLOAD_RESUME',
            37 => 'CURLE_FILE_COULDNT_READ_FILE',
            38 => 'CURLE_LDAP_CANNOT_BIND',
            39 => 'CURLE_LDAP_SEARCH_FAILED',
            41 => 'CURLE_FUNCTION_NOT_FOUND',
            42 => 'CURLE_ABORTED_BY_CALLBACK',
            43 => 'CURLE_BAD_FUNCTION_ARGUMENT',
            45 => 'CURLE_INTERFACE_FAILED',
            47 => 'CURLE_TOO_MANY_REDIRECTS',
            48 => 'CURLE_UNKNOWN_TELNET_OPTION',
            49 => 'CURLE_TELNET_OPTION_SYNTAX',
            51 => 'CURLE_PEER_FAILED_VERIFICATION',
            52 => 'CURLE_GOT_NOTHING',
            53 => 'CURLE_SSL_ENGINE_NOTFOUND',
            54 => 'CURLE_SSL_ENGINE_SETFAILED',
            55 => 'CURLE_SEND_ERROR',
            56 => 'CURLE_RECV_ERROR',
            58 => 'CURLE_SSL_CERTPROBLEM',
            59 => 'CURLE_SSL_CIPHER',
            60 => 'CURLE_SSL_CACERT',
            61 => 'CURLE_BAD_CONTENT_ENCODING',
            62 => 'CURLE_LDAP_INVALID_URL',
            63 => 'CURLE_FILESIZE_EXCEEDED',
            64 => 'CURLE_USE_SSL_FAILED',
            65 => 'CURLE_SEND_FAIL_REWIND',
            66 => 'CURLE_SSL_ENGINE_INITFAILED',
            67 => 'CURLE_LOGIN_DENIED',
            68 => 'CURLE_TFTP_NOTFOUND',
            69 => 'CURLE_TFTP_PERM',
            70 => 'CURLE_REMOTE_DISK_FULL',
            71 => 'CURLE_TFTP_ILLEGAL',
            72 => 'CURLE_TFTP_UNKNOWNID',
            73 => 'CURLE_REMOTE_FILE_EXISTS',
            74 => 'CURLE_TFTP_NOSUCHUSER',
            75 => 'CURLE_CONV_FAILED',
            76 => 'CURLE_CONV_REQD',
            77 => 'CURLE_SSL_CACERT_BADFILE',
            78 => 'CURLE_REMOTE_FILE_NOT_FOUND',
            79 => 'CURLE_SSH',
            80 => 'CURLE_SSL_SHUTDOWN_FAILED',
            81 => 'CURLE_AGAIN',
            82 => 'CURLE_SSL_CRL_BADFILE',
            83 => 'CURLE_SSL_ISSUER_ERROR',
            84 => 'CURLE_FTP_PRET_FAILED',
            84 => 'CURLE_FTP_PRET_FAILED',
            85 => 'CURLE_RTSP_CSEQ_ERROR',
            86 => 'CURLE_RTSP_SESSION_ERROR',
            87 => 'CURLE_FTP_BAD_FILE_LIST',
            88 => 'CURLE_CHUNK_FAILED'
        ];

        /**
         * GetLists
         *
         * @return string
         */
        public static function GetLists(): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::GET,
                \Bring\FlowToParent::Url        => 'bringusers/%%%UserUuid%%%/lists',
                \Bring\FlowToParent::Payload    => []
            ]);
        }

        /**
         * GetUserSettings
         *
         * @return string
         */
        public static function GetUserSettings(): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::GET,
                \Bring\FlowToParent::Url        => 'bringusersettings/%%%UserUuid%%%',
                \Bring\FlowToParent::Payload    => []
            ]);
        }

        /**
         * LoadList
         *
         * @param  string $ListUuid
         * @return string
         */
        public static function GetList(string $ListUuid): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::GET,
                \Bring\FlowToParent::Url        => 'bringlists/' . $ListUuid,
                \Bring\FlowToParent::Payload    => []
            ]);
        }

        /**
         * GetListDetail
         *
         * @param  string $ListUuid
         * @return string
         */
        public static function GetListDetail(string $ListUuid): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::GET,
                \Bring\FlowToParent::Url        => 'bringlists/' . $ListUuid . '/details',
                \Bring\FlowToParent::Payload    => []
            ]);
        }

        /**
         * SendNotification
         *
         * @param  string $ListUuid
         * @param  string $NotificationType
         * @param  string $ItemName
         * @return string
         */
        public static function SendNotification(string $ListUuid, string $NotificationType, string $ItemName = ''): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::POST,
                \Bring\FlowToParent::Url        => 'bringnotifications/lists/' . $ListUuid,
                \Bring\FlowToParent::Payload    => [
                    json_encode([
                        'arguments'                     => [$ItemName],
                        'listNotificationType'          => $NotificationType,
                        'senderPublicUserUuid'          => '%%%PublicUserUuid%%%'])
                ]
            ]);
        }

        /**
         * AddItem
         *
         * @param  string $ListUuid
         * @param  string $ItemName
         * @param  string $Specification
         * @return string
         */
        public static function AddItem(string $ListUuid, string $ItemName, string $Specification): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::PUT,
                \Bring\FlowToParent::Url        => 'bringlists/' . $ListUuid,
                \Bring\FlowToParent::Payload    => [
                    'purchase'     => $ItemName,
                    'recently'     => '',
                    'specification'=> $Specification,
                    'remove'       => '',
                    'sender'       => '%%%PublicUserUuid%%%'
                ]
            ]);
        }

        /**
         * ChangeMultipleItems
         *
         * @param  string $ListUuid
         * @param  array $Items
         * @return string
         */
        public static function ChangeMultipleItems(string $ListUuid, array $Items): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::PUT,
                \Bring\FlowToParent::Url        => 'bringlists/' . $ListUuid . '/items',
                \Bring\FlowToParent::Payload    => [
                    json_encode([
                        /*
                    "accuracy": "0.0",
                    "altitude" => "0.0",
                    "latitude"=> "0.0",
                    "longitude"=> "0.0",
                         */
                        'changes'      => $Items,
                        'sender'       => '%%%PublicUserUuid%%%'
                    ])
                ]
            ]);
        }

        /**
         * RemoveItem
         *
         * @param  string $ListUuid
         * @param  string $ItemName
         * @return string
         */
        /*
        public static function RemoveItem(string $ListUuid, string $ItemName): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::PUT,
                \Bring\FlowToParent::Url        => 'bringlists/' . $ListUuid,
                \Bring\FlowToParent::Payload    => [
                    'purchase'     => '',
                    'recently'     => '',
                    'specification'=> '',
                    'remove'       => $ItemName,
                    'sender'       => '%%%PublicUserUuid%%%'
                ]
            ]);
        }
         */

        /**
         * AddToRecentlyItem
         *
         * @param  string $ListUuid
         * @param  string $ItemName
         * @param  string $Specification
         * @return string
         */
        public static function AddToRecentlyItem(string $ListUuid, string $ItemName, string $Specification): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::PUT,
                \Bring\FlowToParent::Url        => 'bringlists/' . $ListUuid,
                \Bring\FlowToParent::Payload    => [
                    'purchase'     => '',
                    'recently'     => $ItemName,
                    'specification'=> $Specification,
                    'remove'       => '',
                    'sender'       => '%%%PublicUserUuid%%%'
                ]
            ]);
        }

        /**
         * GetAllUsersFromList
         *
         * @param  string $ListUuid
         * @return string
         */
        /*
        public static function GetAllUsersFromList(string $ListUuid): string
        {
            return json_encode([
                \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
                \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::GET,
                \Bring\FlowToParent::Url        => 'bringlists/' . $ListUuid . '/users',
                \Bring\FlowToParent::Payload    => []
            ]);
        }
        /*

        /**
         * GetApiHeader
         *
         * @return array Header
         */
        public static function GetApiHeader(): array
        {
            return [
                'X-BRING-API-KEY: cof4Nc6D8saplXjE3h3HXqHH8m7VU2i1Gs0g85Sp',
                'X-BRING-CLIENT: webApp',
                'X-BRING-COUNTRY: de',
            ];
        }

        /**
         * GetWebHeader
         *
         * @return array  Header
         */
        public static function GetWebHeader(): array
        {
            return [
                'pragma: no-cache',
                'cache-control: no-cache',
                'sec-ch-ua-platform: "Windows"',
                'sec-ch-ua: "Chromium";v="130", "Google Chrome";v="130", "Not?A_Brand";v="99"',
                'sec-ch-ua-mobile: ?0',
                'accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'sec-fetch-site: same-origin',
                'sec-fetch-mode: no-cors',
                'sec-fetch-dest: image',
                'referer: https://web.getbring.com/app/lists/0',
                'accept-language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
                'cookie: bring-terms-notice=1;'
            ];
        }
    }

    /**
     * GUID
     */
    class GUID
    {
        public const WSClient = '{D68FD31F-0E90-7019-F16C-1949BD3079EF}';
        public const ReceiveFromWS = '{018EF6B5-AB94-40C6-AA53-46943E824ACF}';
        public const SendToWS = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
        public const Account = '{C6D2590B-D9DB-113F-5EF1-9323E7B9DBDA}';
        public const List = '{44D63530-0E14-8B8F-3E1A-A79728240524}';
        public const SendToIO = '{63EFC3AC-198A-DE2F-1770-F98D7E61A5D0}';
    }

    /**
     * Property
     */
    class Property
    {
        public const ListUuid = 'listUuid';
        public const RefreshInterval = 'RefreshInterval';
        public const EnableTextboxVariable = 'EnableTextboxVariable';
        public const EnableRefreshIntegerVariable = 'EnableRefreshIntegerVariable';
        public const EnableNotificationIntegerVariable = 'EnableNotificationIntegerVariable';
        public const EnableNotificationStringVariable = 'EnableNotificationStringVariable';
        public const EnableTileDisplay = 'EnableTileDisplay';
        public const AutomaticallySendNotification = 'AutomaticallySendNotification';

    }

    /**
     * Attribute
     */
    class Attribute
    {
        public const uuid = 'uuid';
        public const Name = 'name';
        public const EMail = 'email';
        public const Password = 'password';
        public const publicUuid = 'publicUuid';
        public const AccessToken = 'access_token';
        public const RefreshToken = 'refresh_token';
        public const AccessTokenExpiresIn = 'expires_in';
        public const UserImage = 'UserImage';
        public const ListLocale = 'ListLocale';
        public const AllLists = 'AllLists';
    }

    /**
     * Timer
     */
    class Timer
    {
        public const RefreshToken = 'RefreshToken';
        public const RefreshList = 'RefreshList';
        public const SendListChangeNotification = 'SendListChangeNotification';
    }

    /**
     * Variable
     */
    class Variable
    {
        public const TextBox = 'TextBox';
        public const Reload = 'Reload';
        public const Notify = 'Notify';
        public const UrgentItem = 'UrgentItem';
    }

    /**
     * FlowToParent
     */
    class FlowToParent
    {
        public const DataID = 'DataID';
        public const Url = 'Url';
        public const Method = 'Method';
        public const Payload = 'Payload';
    }

    /**
     * FlowToWebSocket
     */
    class FlowToWebSocket
    {
        public const DataID = 'DataID';
        public const Buffer = 'Buffer';
    }
}

namespace Bring\Api{

    /**
     * RequestMethod
     */
    class RequestMethod
    {
        public const GET = 'GET';
        public const POST = 'POST';
        public const PUT = 'PUT';
    }

    /**
     * NotificationTypes
     */
    class NotificationTypes
    {
        public const CHANGED_LIST = 'CHANGED_LIST';
        public const GOING_SHOPPING = 'GOING_SHOPPING';
        public const SHOPPING_DONE = 'SHOPPING_DONE';
        public const URGENT_MESSAGE = 'URGENT_MESSAGE';
    }

    /**
     * BringItemOperation
     */
    class BringItemOperation
    {
        public const ADD = 'TO_PURCHASE';
        public const COMPLETE = 'TO_RECENTLY';
        public const REMOVE = 'REMOVE';
        public const ATTRIBUTE_UPDATE = 'ATTRIBUTE_UPDATE';
    }
}

/**
 * Search for an item
 * 'bringlistitemdetails/', '?listUuid=' . $this->bringListUUID . '&itemId=' . $search
 * Hidden Icons? Don't know what this is used for
 * 'bringproducts'
 * Found Icons? Don't know what this is used for
 * 'bringusers/' . $this->bringUUID . '/features'
 * Get all users from a shopping list
 * 'bringlists/' . $listUUID . '/users'
 */
