<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/COVID19Helper.php';
    class DistrictStats extends IPSModule
    {
        use COVID19Helper;
        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->RegisterPropertyInteger('province', 0);
            $this->RegisterPropertyString('district', '-');
            $this->RegisterPropertyString('LKSK', 'LK');
            $this->RegisterPropertyBoolean('cases7_per_100k_txt', true);
            $this->RegisterPropertyBoolean('cases7_lk', true);
            $this->RegisterPropertyBoolean('death7_lk', true);

            $this->RegisterPropertyBoolean('cases', true);
            $this->RegisterPropertyBoolean('cases_per_population', true);
            $this->RegisterPropertyBoolean('cases_per_100k', true);
            $this->RegisterPropertyBoolean('deaths', true);
            $this->RegisterPropertyBoolean('death_rate', true);

            $this->RegisterPropertyBoolean('cases7_bl_per_100k', false);
            $this->RegisterPropertyBoolean('cases7_bl', false);
            $this->RegisterPropertyBoolean('death7_bl', false);

            $this->SetBuffer('districtOptions', '{}');

            $this->RegisterPropertyInteger('UpdateInterval', 10);
            $this->RegisterTimer('COVID_DistrictUpdateStats', 0, 'COVID_updateDistrictStats($_IPS[\'TARGET\']);');
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

            $this->RegisterVariableString('District', $this->Translate('District'), '', 0);
            $this->SetValue('District', $this->ReadPropertyString('district'));
            $this->RegisterVariableString('last_update', $this->Translate('Last Update'), '', 12);

            $this->MaintainVariable('cases7_per_100k_txt', $this->Translate('7 Days Incidence'), 2, '', 1, $this->ReadPropertyBoolean('cases7_per_100k_txt') == true);
            $this->MaintainVariable('cases7_lk', $this->Translate('Cases last 7 Days'), 2, '', 2, $this->ReadPropertyBoolean('cases7_lk') == true);
            $this->MaintainVariable('death7_lk', $this->Translate('Deaths last 7 Days'), 2, '', 3, $this->ReadPropertyBoolean('death7_lk') == true);

            $this->MaintainVariable('cases', $this->Translate('Total cases'), 2, '', 4, $this->ReadPropertyBoolean('cases') == true);
            $this->MaintainVariable('cases_per_population', $this->Translate('Cases per population'), 2, '', 5, $this->ReadPropertyBoolean('cases_per_population') == true);
            $this->MaintainVariable('cases_per_100k', $this->Translate('Cases per 100k'), 2, '', 6, $this->ReadPropertyBoolean('cases_per_100k') == true);

            $this->MaintainVariable('deaths', $this->Translate('Deaths'), 2, '', 7, $this->ReadPropertyBoolean('deaths') == true);
            $this->MaintainVariable('death_rate', $this->Translate('Death rate'), 2, '', 8, $this->ReadPropertyBoolean('death_rate') == true);

            $this->MaintainVariable('cases7_bl_per_100k', $this->Translate('7 Days Incidence BL'), 2, '', 9, $this->ReadPropertyBoolean('cases7_bl_per_100k') == true);
            $this->MaintainVariable('cases7_bl', $this->Translate('Cases last 7 Days BL'), 2, '', 10, $this->ReadPropertyBoolean('cases7_bl') == true);
            $this->MaintainVariable('death7_bl', $this->Translate('Deaths last 7 Days BL'), 2, '', 11, $this->ReadPropertyBoolean('death7_bl') == true);

            if ($this->ReadPropertyInteger('province') != 0) {
                $this->updateDistricts($this->ReadPropertyInteger('province'));
            }

            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->updateDistrictStats();
            }

            $this->SetTimerInterval('COVID_DistrictUpdateStats', $this->ReadPropertyInteger('UpdateInterval') * 1000);
        }

        public function RequestAction($Ident, $Value)
        {
            if ($Ident == 'updateDistricts') {
                return $this->updateDistricts($Value);
            }
        }

        public function GetConfigurationForm()
        {
            $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
            $DistrictOptions = json_decode($this->GetBuffer('districtOptions'), true);
            $Form['elements'][1]['options'] = $DistrictOptions;

            return json_encode($Form);
        }

        public function updateDistrictStats()
        {
            $where = "GEN = '" . $this->ReadPropertyString('district') . "'";
            $outFields = 'county,cases7_per_100k_txt,BL_ID,GEN,last_update,cases7_bl_per_100k,cases7_lk,death7_lk,cases,cases_per_population,cases_per_100k,deaths,death_rate,cases7_bl,death7_bl';
            $data = $this->DistrictRequest($where, $outFields);

            foreach ($data as $item) {
                $SKLK = explode(' ', $item['attributes']['county']);

                if ($SKLK[0] == $this->ReadPropertyString('LKSK')) {
                    if ($this->ReadPropertyBoolean('cases7_per_100k_txt')) {
                        $this->SetValue('cases7_per_100k_txt', $item['attributes']['cases7_per_100k_txt']);
                    }
                    if ($this->ReadPropertyBoolean('cases7_lk')) {
                        $this->SetValue('cases7_lk', $item['attributes']['cases7_lk']);
                    }
                    if ($this->ReadPropertyBoolean('death7_lk')) {
                        $this->SetValue('death7_lk', $item['attributes']['death7_lk']);
                    }

                    if ($this->ReadPropertyBoolean('cases')) {
                        $this->SetValue('cases', $item['attributes']['cases']);
                    }
                    if ($this->ReadPropertyBoolean('cases_per_population')) {
                        $this->SetValue('cases_per_population', round($item['attributes']['cases_per_population'], 2));
                    }
                    if ($this->ReadPropertyBoolean('cases_per_100k')) {
                        $this->SetValue('cases_per_100k', round($item['attributes']['cases_per_100k'], 2));
                    }
                    if ($this->ReadPropertyBoolean('deaths')) {
                        $this->SetValue('deaths', $item['attributes']['deaths']);
                    }
                    if ($this->ReadPropertyBoolean('death_rate')) {
                        $this->SetValue('death_rate', round($item['attributes']['death_rate'], 2));
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

        private function updateDistricts($BL_ID)
        {
            $districts = $this->getDistricts($BL_ID);
            $Options = [];

            if ($BL_ID != 0) {
                foreach ($districts as $district) {
                    $Option['caption'] = $district['attributes']['GEN'];
                    $Option['value'] = $district['attributes']['GEN'];
                    array_push($Options, $Option);
                }
                $this->UpdateFormField('district', 'options', json_encode($Options));
                $this->SetBuffer('districtOptions', json_encode($Options));
            }
        }
    }
