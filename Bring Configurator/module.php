<?php

declare(strict_types=1);

eval('declare(strict_types=1);namespace BringConfigurator {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/DebugHelper.php') . '}');
require_once dirname(__DIR__) . '/libs/BringApi.php';


/**
 * BringConfigurator
 * @method bool SendDebug(string $Message, mixed $Data, int $Format)
 */
class BringConfigurator extends IPSModuleStrict
{
    use \BringConfigurator\DebugHelper;

    /**
     * Create
     *
     * @return void
     */
    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent(\Bring\GUID::Gateway);
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
        if (!$this->HasActiveParent() || (IPS_GetInstance($this->InstanceID)['ConnectionID'] == 0)) {
            $Form['actions'][] = [
                'type'  => 'PopupAlert',
                'popup' => [
                    'items' => [[
                        'type'    => 'Label',
                        'caption' => 'Instance has no active parent.'
                    ]]
                ]
            ];
            $this->SendDebug('FORM', json_encode($Form), 0);
            $this->SendDebug('FORM', json_last_error_msg(), 0);
            return json_encode($Form);
        }
        $Form['actions'][0]['values'] = $this->LoadLists();
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    private function LoadLists(): array
    {
        $Lists = $this->GetLists();
        $IPSDevices = $this->GetIPSInstances(\Bring\GUID::List, \Bring\Property::ListUuid);
        $Values = [];
        foreach ($Lists as $List) {
            $this->SendDebug('List', $List, 0);
            $InstanceID = array_search($List[\Bring\Property::ListUuid], $IPSDevices);
            $Values[] = [
                'name'             => ($InstanceID ? IPS_GetName($InstanceID) : $List['name']),
                'instanceID'       => ($InstanceID ? $InstanceID : 0),
                'create'           => [
                    'moduleID'         => \Bring\GUID::List,
                    'configuration'    => [
                        \Bring\Property::ListUuid => $List[\Bring\Property::ListUuid]
                    ]
                ]
            ];
            if ($InstanceID !== false) {
                unset($IPSDevices[$InstanceID]);
            }
        }
        foreach ($IPSDevices as $InstanceID => $DeviceId) {
            $Values[] = [
                'name'             => IPS_GetName($InstanceID),
                'instanceID'       => $InstanceID,
            ];
        }
        return $Values;
    }

    private function GetLists(): array
    {
        $JSON = json_encode([
            \Bring\FlowToParent::DataID     => \Bring\GUID::SendToIO,
            \Bring\FlowToParent::Method     => \Bring\Api\RequestMethod::GET,
            \Bring\FlowToParent::Url       => 'bringusers/%%%UserUuid%%%/lists',
            \Bring\FlowToParent::Payload    => []
        ]);
        $Result = $this->SendDataToParent($JSON);
        if (!$Result) {
            return [];
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return [];
        }
        $this->SendDebug(__FUNCTION__, $Result, 0);
        return $Result['lists'];
    }

    /**
     * FilterInstances
     *
     * @param  int $InstanceID
     * @return bool
     */
    protected function FilterInstances(int $InstanceID): bool
    {
        return IPS_GetInstance($InstanceID)['ConnectionID'] == IPS_GetInstance($this->InstanceID)['ConnectionID'];
    }

    /**
     * GetConfigParam
     *
     * @param  mixed $item1
     * @param  int $InstanceID
     * @param  string $ConfigParam
     * @return void
     */
    protected function GetConfigParam(mixed &$item1, int $InstanceID, string $ConfigParam): void
    {
        $item1 = IPS_GetProperty($InstanceID, $ConfigParam);
    }

    /**
     * GetIPSInstances
     *
     * @param  string $GUID
     * @param  string $ConfigParam
     * @return array
     */
    private function GetIPSInstances(string $GUID, string $ConfigParam = ''): array
    {
        $InstanceIDList = array_filter(IPS_GetInstanceListByModuleID($GUID), [$this, 'FilterInstances']);
        $InstanceIDList = array_flip(array_values($InstanceIDList));
        if ($ConfigParam) {
            array_walk($InstanceIDList, [$this, 'GetConfigParam'], $ConfigParam);
        }
        return $InstanceIDList;
    }
}
