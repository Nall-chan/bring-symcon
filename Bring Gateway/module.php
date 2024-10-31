<?php

declare(strict_types=1);

eval('declare(strict_types=1);namespace BringList {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/DebugHelper.php') . '}');
require_once dirname(__DIR__) . '/libs/BringApi.php';

/**
 * BringGateway
 *
 * @method bool SendDebug(string $Message, mixed $Data, int $Format)
 */
class BringGateway extends IPSModuleStrict
{
    use \BringList\DebugHelper;

    private static $http_error =
    [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Server error'
    ];

    /**
     * Create
     *
     * @return void
     */
    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
        $this->RegisterAttributeString(\Bring\Api::UserUuid, '');
    }

    /**
     * Destroy
     *
     * @return void
     */
    public function Destroy(): void
    {
        //Never delete this line!
        parent::Destroy();
    }

    /**
     * ApplyChanges
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();
    }

    /**
     * RequestAction
     *
     * @param  string $Ident
     * @param  mixed $Value
     * @return void
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'LoginPopup':
                $this->UpdateFormField('LoginPopup', 'visible', true);
                break;
            case 'ClearLogin':
                $this->WriteAttributeString(\Bring\Api::UserUuid, '');
                $this->UpdateFormField('ClearLogin', 'visible', false);
                $this->UpdateFormField('Login', 'visible', true);
                $this->UpdateFormField('LoginPopup', 'visible', true);
                break;
        }
    }

    /**
     * SendLogin
     *
     * @param  string $Username
     * @param  string $Password
     * @return bool
     */
    public function SendLogin(string $Username, string $Password): string
    {
        $Result = $this->DoRequest(
            \Bring\Api\RequestMethod::GET,
            'bringlists',
            [
                'email'    => $Username,
                'password' => $Password
            ],
            true
        );
        if ($Result === false) {
            $this->WriteAttributeString(\Bring\Api::UserUuid, '');
            return 'email password combination not existing';
        }
        $this->WriteAttributeString(\Bring\Api::UserUuid, $Result['uuid']);
        $this->UpdateFormField('ClearLogin', 'visible', true);
        $this->UpdateFormField('Login', 'visible', false);
        return 'MESSAGE:' . $this->Translate('Login okay.');
    }

    /**
     * GetLists
     *
     * @return array
     */
    public function GetLists(): array
    {
        return $this->DoRequest(
            \Bring\Api\RequestMethod::GET,
            'bringusers/' . $this->ReadAttributeString(\Bring\Api::UserUuid) . '/lists'
        );
    }

    /**
     * GetItems
     *
     * @param  string $bringListUUID
     * @return array
     */
    public function GetItems(string $bringListUUID): array
    {
        return $this->DoRequest(
            \Bring\Api\RequestMethod::GET,
            'bringlists/' . $bringListUUID
        );
    }

    /**
     * GetConfigurationForm
     *
     * @return string
     */
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        if ($this->GetStatus() == IS_CREATING) {
            return json_encode($Form);
        }
        if ($this->ReadAttributeString(\Bring\Api::UserUuid)) {
            $Form['actions'][0]['visible'] = true;
            $Form['actions'][1]['visible'] = false;
            $Form['actions'][2]['visible'] = false;
        } else {
            $Form['actions'][0]['visible'] = false;
            $Form['actions'][1]['visible'] = true;
            $Form['actions'][2]['visible'] = true;
        }
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    /**
     * ForwardData
     *
     * @param  string $JSONString
     * @return string
     */
    public function ForwardData(string $JSONString): string
    {
        switch ($this->GetStatus()) {
            case IS_ACTIVE:
                break;
            case IS_INACTIVE:
                set_error_handler([$this, 'ModulErrorHandler']);
                trigger_error($this->Translate('Instance is inactive'), E_USER_WARNING);
                restore_error_handler();
                return serialize(false);
            default:
                set_error_handler([$this, 'ModulErrorHandler']);
                trigger_error($this->Translate('Instance is in error state'), E_USER_WARNING);
                restore_error_handler();
                return serialize(false);
        }
        $Data = json_decode($JSONString, true);
        $Data[\Bring\FlowToParent::Url] = str_replace('%%%UserUuid%%%', $this->ReadAttributeString(\Bring\Api::UserUuid), $Data[\Bring\FlowToParent::Url]);
        $Result = $this->DoRequest(
            $Data[\Bring\FlowToParent::Method],
            $Data[\Bring\FlowToParent::Url],
            $Data[\Bring\FlowToParent::Payload]
        );
        return serialize($Result);
    }

    /**
     * ModulErrorHandler
     *
     * @param  int $errno
     * @param  string $errstr
     * @return true
     */
    protected function ModulErrorHandler(int $errno, string $errstr): bool
    {
        echo $errstr . PHP_EOL;
        return true;
    }

    /**
     *   Handles the request to the server
     *
     *   @param string   $RequestMethod   The HTTP request type.
     *   @param string   $RequestURL      contains the request URL
     *   @param array    $Payload    The parameters we send with the request
     *   @param bool     $IsLogin   True on login command, otherwise false (That is necessary because it sends the API-KEY with the request)
     *   @return array|false  The answer string from the server
     */
    private function DoRequest(string $RequestMethod = \Bring\Api\RequestMethod::GET, string $RequestURL, array $Payload = [], bool $IsLogin = false): array|false
    {
        $Parameter = '';
        if (count($Payload)) {
            $Parameter = http_build_query($Payload);
        }
        $ch = curl_init();
        $AdditionalHeader = '';
        switch ($RequestMethod) {
            case \Bring\Api\RequestMethod::GET:
                curl_setopt($ch, CURLOPT_URL, \Bring\Api::BringRestURL . $RequestURL . ($Parameter ? '?' . $Parameter : ''));
                break;
            case \Bring\Api\RequestMethod::POST:
                curl_setopt($ch, CURLOPT_POST, true);
                // no break. Add additional comment above this line if intentional
            case \Bring\Api\RequestMethod::PUT:
                curl_setopt($ch, CURLOPT_URL, \Bring\Api::BringRestURL . $RequestURL);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $Parameter);
                $AdditionalHeader = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
                break;
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $RequestMethod);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        /** @var array $_IPS */
        $this->SendDebug('RequestMethod:' . $_IPS['THREAD'], $RequestMethod, 0);
        $this->SendDebug('RequestURL:' . $_IPS['THREAD'], $RequestURL, 0);
        if (!$IsLogin) {
            $RequestHeader = self::GetHeader(
                $this->ReadAttributeString(\Bring\Api::UserUuid),
                $AdditionalHeader
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $RequestHeader);
            $this->SendDebug('RequestHeader:' . $_IPS['THREAD'], $RequestHeader, 0);
        }
        $this->SendDebug('RequestData:' . $_IPS['THREAD'], $Payload, 0);
        $response = curl_exec($ch);

        $HttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($HttpCode != 0) {
            $this->SendDebug('Request Headers:' . $_IPS['THREAD'], curl_getinfo($ch)['request_header'], 0);
        }
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        $Header = '';
        $Result = '';
        if (!is_bool($response)) {
            $Parts = explode("\r\n\r\n", $response);
            $Header = array_shift($Parts);
            $Result = implode("\r\n\r\n", $Parts);
            if (is_null($Result)) {
                $Result = '';
            }
        }
        $this->SendDebug('Result Headers:' . $_IPS['THREAD'], $Header, 0);
        $this->SendDebug('Result Body:' . $_IPS['THREAD'], $Result, 0);

        set_error_handler([$this, 'ModulErrorHandler']);
        switch ($HttpCode) {
            case 0:
                $this->SendDebug('CURL ERROR', \Bring\Api::$CURL_error_codes[$curl_errno], 0);
                trigger_error(\Bring\Api::$CURL_error_codes[$curl_errno], E_USER_WARNING);
                $Result = false;
                break;
            case 202:
            case 204:
                if ($RequestMethod == \Bring\Api\RequestMethod::PUT) {
                    $Result = true;
                }
                break;
            case 400:
            case 401:
            case 403:
            case 404:
            case 405:
            case 500:
                $this->SendDebug(self::$http_error[$HttpCode], $HttpCode, 0);
                trigger_error(self::$http_error[$HttpCode], E_USER_WARNING);
                $Result = false;
                break;
        }
        restore_error_handler();
        return json_decode($Result, true);
    }

    /**
     * GetHeader
     *
     * @param  string $UserUuid
     * @param  string $AdditionalHeader   additional field that you want to add to the header
     * @return array  Header
     */
    private static function GetHeader(string $UserUuid, string $AdditionalHeader = ''): array
    {
        $Header = [
            'X-BRING-API-KEY: cof4Nc6D8saplXjE3h3HXqHH8m7VU2i1Gs0g85Sp',
            'X-BRING-CLIENT: android',
            'X-BRING-USER-UUID: ' . $UserUuid,
            'X-BRING-VERSION: 303070050',
            'X-BRING-COUNTRY: de',
        ];
        if ($AdditionalHeader) {
            $Header[] = $AdditionalHeader;
        }
        return $Header;
    }
}
