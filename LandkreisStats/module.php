<?php

declare(strict_types=1);
    class LandkreisStats extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->RegisterPropertyString('district', '');
            $this->RegisterPropertyBoolean('cases7_per_100k_txt', true);
            $this->RegisterPropertyBoolean('cases7_lk', true);
            $this->RegisterPropertyBoolean('death7_lk', true);
            $this->RegisterPropertyBoolean('cases7_bl_per_100k', false);
            $this->RegisterPropertyBoolean('cases7_bl', false);
            $this->RegisterPropertyBoolean('death7_bl', false);

            $this->RegisterPropertyInteger('UpdateInterval', 10);
            $this->RegisterTimer('COVID_UpdateStats', 0, 'COVID_updateStats($_IPS[\'TARGET\']);');
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

            $this->RegisterVariableString('District', $this->Translate('District'));
            $this->SetValue('District', $this->ReadPropertyString('district'));
            $this->RegisterVariableString('last_update', $this->Translate('Last Update'));

            $this->MaintainVariable('cases7_per_100k_txt', $this->Translate('7 Days Incidence District'), 2, '', 0, $this->ReadPropertyBoolean('cases7_per_100k_txt') == true);
            $this->MaintainVariable('cases7_lk', $this->Translate('Cases last 7 Days District'), 2, '', 0, $this->ReadPropertyBoolean('cases7_lk') == true);
            $this->MaintainVariable('death7_lk', $this->Translate('Deathes last 7 Days District'), 2, '', 0, $this->ReadPropertyBoolean('death7_lk') == true);
            $this->MaintainVariable('cases7_bl_per_100k', $this->Translate('7 Days Incidence BL'), 2, '', 0, $this->ReadPropertyBoolean('cases7_bl_per_100k') == true);
            $this->MaintainVariable('cases7_bl', $this->Translate('Cases last 7 Days BL'), 2, '', 0, $this->ReadPropertyBoolean('cases7_bl') == true);
            $this->MaintainVariable('death7_bl', $this->Translate('Deathes last 7 Days BL'), 2, '', 0, $this->ReadPropertyBoolean('death7_bl') == true);

            $this->SetTimerInterval('COVID_UpdateStats', $this->ReadPropertyInteger('UpdateInterval') * 1000);
        }

        public function updateStats()
        {
            $where = 'GEN+%3D+%27' . $this->ReadPropertyString('district') . '%27';
            $url = 'https://services7.arcgis.com/mOBPykOjAyBO2ZKk/arcgis/rest/services/RKI_Landkreisdaten/FeatureServer/0/query?where=' . $where . '&outFields=cases7_per_100k_txt%2CBL_ID%2CGEN%2Clast_update%2Ccases7_bl_per_100k%2Ccases7_lk%2Cdeath7_lk%2Ccases7_bl%2Cdeath7_bl&returnGeometry=false&returnCentroid=false&featureEncoding=esriDefault&multipatchOption=xyFootprint&maxAllowableOffset=&geometryPrecision=&outSR=&datumTransformation=&applyVCSProjection=false&returnIdsOnly=false&returnUniqueIdsOnly=false&returnCountOnly=false&returnExtentOnly=false&returnQueryGeometry=false&returnDistinctValues=false&cacheHint=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&having=&resultOffset=&resultRecordCount=&returnZ=false&returnM=false&returnExceededLimitFeatures=false&quantizationParameters=&sqlFormat=none&f=pjson&token=';
            $dataJSON = file_get_contents($url);
            $data = json_decode($dataJSON, true);
            IPS_LogMessage('test', print_r($data, true));

            foreach ($data['features'] as $item) {
                if ($this->ReadPropertyBoolean('cases7_per_100k_txt')) {
                    $this->SetValue('cases7_per_100k_txt', $item['attributes']['cases7_per_100k_txt']);
                }
                if ($this->ReadPropertyBoolean('cases7_lk')) {
                    $this->SetValue('cases7_lk', $item['attributes']['cases7_lk']);
                }
                if ($this->ReadPropertyBoolean('death7_lk')) {
                    $this->SetValue('death7_lk', $item['attributes']['death7_lk']);
                }
                if ($this->ReadPropertyBoolean('cases7_bl_per_100k')) {
                    $this->SetValue('cases7_bl_per_100k', $item['attributes']['cases7_bl_per_100k']);
                }

                if ($this->ReadPropertyBoolean('cases7_bl')) {
                    $this->SetValue('cases7_bl', $item['attributes']['cases7_bl']);
                }

                if ($this->ReadPropertyBoolean('death7_bl')) {
                    $this->SetValue('death7_bl', $item['attributes']['death7_bl']);
                }
                $this->SetValue('last_update', $item['attributes']['last_update']);
            }
        }
    }