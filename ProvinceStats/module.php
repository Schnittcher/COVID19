<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/COVID19Helper.php';
    class ProvinceStats extends IPSModule
    {
        use COVID19Helper;
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyString('province', '-');

            $this->RegisterPropertyBoolean('cases7_bl_per_100k', true);
            $this->RegisterPropertyBoolean('cases7_bl', true);
            $this->RegisterPropertyBoolean('death7_bl', true);

            $this->RegisterPropertyBoolean('Fallzahl', true);
            $this->RegisterPropertyBoolean('faelle_100000_EW', true);
            $this->RegisterPropertyBoolean('Death', true);

            $this->RegisterPropertyInteger('UpdateInterval', 10);
            $this->RegisterTimer('COVID_ProvinceUpdateStats', 0, 'COVID_updateProvinceStats($_IPS[\'TARGET\']);');
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();

            $this->RegisterVariableString('Province', $this->Translate('Province'), '', 0);
            $this->RegisterVariableInteger('last_update', $this->Translate('Last Update'), '~UnixTimestamp', 12);

            $this->MaintainVariable('cases7_bl_per_100k', $this->Translate('7 Days Incidence'), 2, '', 1, $this->ReadPropertyBoolean('cases7_bl_per_100k') == true);
            $this->MaintainVariable('cases7_bl', $this->Translate('Cases last 7 Days'), 2, '', 2, $this->ReadPropertyBoolean('cases7_bl') == true);
            $this->MaintainVariable('death7_bl', $this->Translate('Deaths last 7 Days'), 2, '', 3, $this->ReadPropertyBoolean('death7_bl') == true);

            $this->MaintainVariable('Fallzahl', $this->Translate('Total cases'), 2, '', 4, $this->ReadPropertyBoolean('Fallzahl') == true);
            $this->MaintainVariable('faelle_100000_EW', $this->Translate('Cases per 100k'), 2, '', 6, $this->ReadPropertyBoolean('faelle_100000_EW') == true);

            $this->MaintainVariable('Death', $this->Translate('Deaths'), 2, '', 7, $this->ReadPropertyBoolean('Death') == true);
            $this->SetTimerInterval('COVID_ProvinceUpdateStats', $this->ReadPropertyInteger('UpdateInterval') * 1000);
        }

        public function updateProvinceStats()
        {
            $where = "LAN_ew_GEN = '" . $this->ReadPropertyString('province') . "'";
            $outFields = 'LAN_ew_GEN,Fallzahl,Aktualisierung,faelle_100000_EW,Death,cases7_bl_per_100k,cases7_bl,death7_bl';
            $data = $this->ProvinceRequest($where, $outFields);
            foreach ($data as $item) {
                if ($this->ReadPropertyBoolean('cases7_bl_per_100k')) {
                    $this->SetValue('cases7_bl_per_100k', round($item['attributes']['cases7_bl_per_100k'], 2));
                }
                if ($this->ReadPropertyBoolean('cases7_bl')) {
                    $this->SetValue('cases7_bl', $item['attributes']['cases7_bl']);
                }
                if ($this->ReadPropertyBoolean('death7_bl')) {
                    $this->SetValue('death7_bl', $item['attributes']['death7_bl']);
                }

                if ($this->ReadPropertyBoolean('Fallzahl')) {
                    $this->SetValue('Fallzahl', $item['attributes']['Fallzahl']);
                }
                if ($this->ReadPropertyBoolean('faelle_100000_EW')) {
                    $this->SetValue('faelle_100000_EW', round($item['attributes']['faelle_100000_EW'], 2));
                }
                if ($this->ReadPropertyBoolean('Death')) {
                    $this->SetValue('Death', round($item['attributes']['Death'], 2));
                }
                $this->SetValue('last_update', substr(strval($item['attributes']['Aktualisierung']), 0, -3));
                $this->SetValue('Province', $item['attributes']['LAN_ew_GEN']);
            }
        }
    }