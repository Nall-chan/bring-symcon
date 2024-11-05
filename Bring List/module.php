<?php

declare(strict_types=1);

eval('declare(strict_types=1);namespace BringList {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/DebugHelper.php') . '}');
eval('declare(strict_types=1);namespace BringList {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/BufferHelper.php') . '}');
eval('declare(strict_types=1);namespace BringList {?>' . file_get_contents(__DIR__ . '/../libs/helper/ParentIOHelper.php') . '}');
eval('declare(strict_types=1);namespace BringList {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/AttributeArrayHelper.php') . '}');
eval('declare(strict_types=1);namespace BringList {?>' . file_get_contents(dirname(__DIR__) . '/libs/helper/VariableProfileHelper.php') . '}');
require_once dirname(__DIR__) . '/libs/BringApi.php';

/**
 * BringList
 *
 * @property int $ParentID
 * @property array $UserDefinedItems
 * @property array $ListUsers
 * @method void RegisterParent()
 * @method void RegisterAttributeArray(string $name, mixed $Value, int $Size = 0)
 * @method array ReadAttributeArray(string $name)
 * @method void WriteAttributeArray(string $name, mixed $value)
 * @method bool SendDebug(string $Message, mixed $Data, int $Format)
 * @method void UnregisterProfile(string $Name)
 * @method void RegisterProfileIntegerEx(string $Name, string $Icon, string $Prefix, string $Suffix, array $Associations, int $MaxValue = -1, float $StepSize = 0)
 * @method void RegisterProfileStringEx(string $Name, string $Icon, string $Prefix, string $Suffix, array $Associations)
 */
class BringList extends IPSModuleStrict
{
    use \BringList\DebugHelper;
    use \BringList\BufferHelper;
    use \BringList\AttributeArrayHelper;
    use \BringList\VariableProfileHelper;
    use \BringList\InstanceStatus {
        \BringList\InstanceStatus::MessageSink as IOMessageSink;
        \BringList\InstanceStatus::RequestAction as IORequestAction;
    }
    /**
     * Create
     *
     * @return void
     */
    public function Create(): void
    {
        //Never delete this line!
        parent::Create();
        $this->ParentID = 0;
        $this->UserDefinedItems = [];
        $this->ListUsers = [];
        $this->RegisterAttributeArray(\Bring\Attribute::AllLists, []);
        $this->RegisterAttributeString(\Bring\Attribute::ListTheme, '');

        $this->RegisterPropertyString(\Bring\Property::ListUuid, '');
        $this->RegisterPropertyInteger(\Bring\Property::RefreshInterval, 0);
        $this->RegisterPropertyInteger(\Bring\Property::AutomaticallySendNotification, -1);
        $this->RegisterPropertyBoolean(\Bring\Property::EnableNotificationIntegerVariable, true);
        $this->RegisterPropertyBoolean(\Bring\Property::EnableNotificationStringVariable, true);
        $this->RegisterPropertyBoolean(\Bring\Property::EnableRefreshIntegerVariable, true);
        $this->RegisterPropertyBoolean(\Bring\Property::EnableTextboxVariable, true);
        $this->RegisterPropertyBoolean(\Bring\Property::EnableTileDisplay, true);

        $this->RegisterTimer(\Bring\Timer::RefreshList, 0, 'BRING_UpdateList(' . $this->InstanceID . ');');
    }

    /**
     * Destroy
     *
     * @return void
     */
    public function Destroy(): void
    {
        if (!IPS_InstanceExists($this->InstanceID)) {
            $this->UnregisterProfile('BRING.Reload');
            $this->UnregisterProfile('BRING.Notify');
        }
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

        $this->RegisterProfileIntegerEx(
            'BRING.Reload',
            '',
            '',
            '',
            [
                [0, 'Reload', '', 0xff0000]
            ]
        );
        $this->RegisterProfileStringEx(
            'BRING.Notify',
            '',
            '',
            '',
            [
                [\Bring\Api\NotificationTypes::SHOPPING_DONE, 'Shopping done', '', 0x00ff00],
                [\Bring\Api\NotificationTypes::GOING_SHOPPING, 'Go shopping', '', 0xff0000],
                [\Bring\Api\NotificationTypes::CHANGED_LIST, 'List changed', '', 0x0000ff]
            ]
        );
        if ($this->ReadPropertyBoolean(\Bring\Property::EnableTextboxVariable)) {
            $this->RegisterVariableString(\Bring\Variable::TextBox, $this->Translate('Purchase'), '~TextBox', 1);
            $this->EnableAction(\Bring\Variable::TextBox);
        } else {
            $this->UnregisterVariable(\Bring\Variable::TextBox);
        }

        if ($this->ReadPropertyBoolean(\Bring\Property::EnableRefreshIntegerVariable)) {
            $this->RegisterVariableInteger(\Bring\Variable::Reload, $this->Translate('Reload List'), 'BRING.Reload', 2);
            $this->EnableAction(\Bring\Variable::Reload);
        } else {
            $this->UnregisterVariable(\Bring\Variable::Reload);
        }

        if ($this->ReadPropertyBoolean(\Bring\Property::EnableNotificationIntegerVariable)) {
            $this->RegisterVariableString(\Bring\Variable::Notify, $this->Translate('Send Notification'), 'BRING.Notify', 3);
            $this->EnableAction(\Bring\Variable::Notify);
        } else {
            $this->UnregisterVariable(\Bring\Variable::Notify);
        }

        if ($this->ReadPropertyBoolean(\Bring\Property::EnableNotificationStringVariable)) {
            $this->RegisterVariableString(\Bring\Variable::UrgentItem, $this->Translate('Send Urgent Item Notification'), '', 4);
            $this->EnableAction(\Bring\Variable::UrgentItem);
        } else {
            $this->UnregisterVariable(\Bring\Variable::UrgentItem);
        }

        $SendNotifyTimeout = $this->ReadPropertyInteger(\Bring\Property::AutomaticallySendNotification);

        if (IPS_GetKernelRunlevel() != KR_READY) {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
            return;
        }

        $this->RegisterParent();

        if ($this->HasActiveParent()) {
            $AllLists = $this->GetLists();
            $this->WriteAttributeArray(\Bring\Attribute::AllLists, $AllLists);
            $Values = [];
            foreach ($AllLists as $List) {
                $Values[] = [
                    'caption'=> $List[\Bring\Attribute::Name],
                    'value'  => $List[\Bring\Property::ListUuid]
                ];
            }
            $this->UpdateFormField(\Bring\Property::ListUuid, 'options', json_encode($Values));
            $this->WriteAttributeString(\Bring\Attribute::ListTheme, $this->GetTheme());
            $this->ListUsers = $this->GetAllUsersFromList();
            $this->UserDefinedItems = $this->GetListDetail();
            $this->UpdateList();
            $this->SetTimerInterval(\Bring\Timer::RefreshList, $this->ReadPropertyInteger(\Bring\Property::RefreshInterval) * 1000);
        } else {
            $this->SetTimerInterval(\Bring\Timer::RefreshList, 0);
        }
    }

    /**
     * MessageSink
     * Interne Funktion des SDK.
     *
     * @param  int $TimeStamp
     * @param  int $SenderID
     * @param  int $Message
     * @param  array $Data
     * @return void
     */
    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        $this->IOMessageSink($TimeStamp, $SenderID, $Message, $Data);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
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
        if ($this->IORequestAction($Ident, $Value)) {
            return;
        }
        switch ($Ident) {
            case \Bring\Variable::TextBox:
                $OldItems = preg_split('/\r\n|\r|\n/', $this->GetValue(\Bring\Variable::TextBox));
                $NewItems = preg_split('/\r\n|\r|\n/', $Value);
                $DeleteItems = array_diff($OldItems, $NewItems);
                $AddItems = array_diff($NewItems, $OldItems);
                $this->SendDebug('Add', $AddItems, 0);
                $this->SendDebug('Delete', $DeleteItems, 0);
                $Items = [];
                foreach ($DeleteItems as $DeleteItem) {
                    $DeleteItem = trim($DeleteItem);
                    $Result = self::DecodeItemString($DeleteItem);
                    if ($Result['itemId']) {
                        $Result['operation'] = \Bring\Api\BringItemOperation::REMOVE;
                        $Items[] = $Result;
                    }
                }
                foreach ($AddItems as $AddItem) {
                    $AddItem = trim($AddItem);
                    $Result = self::DecodeItemString($AddItem);
                    if ($Result['itemId']) {
                        $Result['operation'] = \Bring\Api\BringItemOperation::ADD;
                        $Items[] = $Result;
                    }
                }
                if (count($Items)) {
                    if ($this->ChangeMultipleItems($Items)) {
                        $this->UpdateList();
                        /**
                         * @todo timer für senden Item Added Notification setzen.
                         */
                    } else {
                        echo $this->Translate('Error on change the list');
                    }
                }
                break;
            case \Bring\Variable::Reload:
                $this->UpdateList();
                break;
            case \Bring\Variable::Notify:
                $this->SendNotify($Value);
                break;
            case \Bring\Variable::UrgentItem:
                $this->SendUrgentItemNotify($Value);
                break;
        }
    }

    /**
     * UpdateList
     *
     * @return bool
     */
    public function UpdateList(): bool
    {
        $Result = $this->GetList();
        if (!$Result) {
            return false;
        }

        /**
         * @todo weiter an Funktion für Darstellung der Werte
         */
        $Purchase = [];
        foreach ($Result['purchase'] as $Item) {
            $Purchase[] = $Item['name'] . ($Item['specification'] ? ' (' . trim($Item['specification']) . ')' : '');

        }
        $this->SetValue(\Bring\Variable::TextBox, implode("\r\n", $Purchase));
        return true;
    }

    /**
     * GetList
     *
     * Lädt die Liste neu, und gibt sie alls Array zurück.
     * Public, damit eigene Umsetzungen z.B. mit HTML-Boxen möglich sind.
     *
     * @return array|false
     */
    public function GetList(): array|false
    {
        $JSON = \Bring\Api::GetList($this->ReadPropertyString(\Bring\Property::ListUuid));
        $Result = @$this->SendDataToParent($JSON);
        if (!$Result) {
            return false;
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return false;
        }
        $this->SendDebug(__FUNCTION__, $Result, 0);
        return $Result;
    }

    /**
     * GetLists
     *
     * @return array
     */
    public function GetLists(): array
    {
        $Result = @$this->SendDataToParent(\Bring\Api::GetLists());
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
     * GetTheme
     *
     * @return string
     */
    public function GetTheme(): string
    {
        $Lists = $this->ReadAttributeArray(\Bring\Attribute::AllLists);
        if (!count($Lists)) {
            return '';
        }
        $Themes = array_column($Lists, 'theme', \Bring\Property::ListUuid);
        $this->SendDebug(__FUNCTION__, $Themes, 0);
        return $Themes[$this->ReadPropertyString(\Bring\Property::ListUuid)] ?? '';
    }

    /**
     * GetAllUsersFromList
     *
     * @return array
     */
    public function GetAllUsersFromList(): array
    {
        $Result = @$this->SendDataToParent(\Bring\Api::GetAllUsersFromList($this->ReadPropertyString(\Bring\Property::ListUuid)));
        if (!$Result) {
            return [];
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return [];
        }
        $this->SendDebug(__FUNCTION__, $Result, 0);
        return $Result['users'];
    }

    /**
     * GetListDetail
     *
     * @return array
     */
    public function GetListDetail(): array
    {

        $JSON = \Bring\Api::GetListDetail($this->ReadPropertyString(\Bring\Property::ListUuid));
        $Result = @$this->SendDataToParent($JSON);
        if (!$Result) {
            return [];
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return [];
        }
        $this->SendDebug(__FUNCTION__, $Result, 0);
        return $Result;
    }

    /**
     * SendNotify
     *
     * @param  string $NotificationType
     * @return bool
     */
    public function SendNotify(string $NotificationType): bool
    {
        $this->SendDebug(__FUNCTION__, '', 0);
        $JSON = \Bring\Api::SendNotification(
            $this->ReadPropertyString(\Bring\Property::ListUuid),
            $NotificationType
        );
        $Result = @$this->SendDataToParent($JSON);
        if (!$Result) {
            return false;
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return false;
        }
        $this->SendDebug(__FUNCTION__, $Result, 0);
        return $Result;
    }

    /**
     * SendUrgentItemNotify
     *
     * @param  string $NotificationType
     * @return bool
     */
    public function SendUrgentItemNotify(string $ItemName): bool
    {
        $this->SendDebug(__FUNCTION__, '', 0);
        $JSON = \Bring\Api::SendNotification(
            $this->ReadPropertyString(\Bring\Property::ListUuid),
            \Bring\Api\NotificationTypes::URGENT_MESSAGE,
            $ItemName
        );
        $Result = @$this->SendDataToParent($JSON);
        if (!$Result) {
            return false;
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return false;
        }
        $this->SendDebug(__FUNCTION__, $Result, 0);
        return $Result;
    }

    /**
     * AddItem
     *
     * @param  string $ItemName
     * @param  string $Specification
     * @return bool
     */
    public function AddItem(string $ItemName, string $Specification): bool
    {
        $JSON = \Bring\Api::AddItem(
            $this->ReadPropertyString(\Bring\Property::ListUuid),
            $ItemName,
            $Specification
        );
        $Result = @$this->SendDataToParent($JSON);
        if (!$Result) {
            return false;
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return false;
        }
        $this->SendDebug(__FUNCTION__, $Result, 0);
        return true;
    }

    /**
     * RemoveItem
     *
     * @param  string $ItemName
     * @return bool
     */
    public function RemoveItem(string $ItemName): bool
    {
        $JSON = \Bring\Api::RemoveItem(
            $this->ReadPropertyString(\Bring\Property::ListUuid),
            $ItemName
        );
        $Result = @$this->SendDataToParent($JSON);
        if (!$Result) {
            return false;
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return false;
        }
        $this->SendDebug(__FUNCTION__, $Result, 0);
        return true;
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
        $Lists = $this->ReadAttributeArray(\Bring\Attribute::AllLists);
        $Values = [];
        foreach ($Lists as $List) {
            $Values[] = [
                'caption'=> $List[\Bring\Attribute::Name],
                'value'  => $List[\Bring\Property::ListUuid]
            ];
        }
        $Form['elements'][0]['options'] = $Values;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    /**
     * KernelReady
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     *
     * @return void
     */
    protected function KernelReady(): void
    {
        $this->UnregisterMessage(0, IPS_KERNELSTARTED);
        $this->ApplyChanges();
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState(int $State): void
    {
        switch ($State) {
            case IS_ACTIVE:
                $this->ApplyChanges();
                break;
        }
    }

    private function ChangeMultipleItems(array $Items): bool
    {
        $this->SendDebug(__FUNCTION__, $Items, 0);
        $JSON = \Bring\Api::ChangeMultipleItems($this->ReadPropertyString(\Bring\Property::ListUuid), $Items);
        $this->SendDebug(__FUNCTION__, $JSON, 0);
        $Result = @$this->SendDataToParent($JSON);
        $this->SendDebug(__FUNCTION__, $Result, 0);
        if (!$Result) {
            return false;
        }
        $Result = unserialize($Result);
        if ($Result === false) {
            return false;
        }
        return $Result;
    }

    private static function DecodeItemString(string $Line): array
    {
        preg_match_all("/(?J)((?<ItemName>.*)\((?<ItemDesc>.*)\))|(?<ItemName>.*)/", $Line, $Result, PREG_SET_ORDER);
        $Item['itemId'] = trim($Result[0]['ItemName']);
        $Item['specification'] = trim($Result[0]['ItemDesc']);
        return $Item;
    }
}