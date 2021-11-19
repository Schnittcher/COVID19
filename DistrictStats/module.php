<?php

declare(strict_types=1);

    class DistrictStats extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->RegisterPropertyString('URL', 'https://api.corona-zahlen.org');
            $this->RegisterPropertyString('province', '-');
            $this->RegisterPropertyString('district', '-');

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

            $this->RegisterVariableInteger('population', $this->Translate('Population'), '', 1);
            $this->RegisterVariableInteger('cases', $this->Translate('Cases'), '', 2);
            $this->RegisterVariableInteger('deaths', $this->Translate('Deaths'), '', 3);
            $this->RegisterVariableInteger('casesPerWeek', $this->Translate('Cases per Week'), '', 4);
            $this->RegisterVariableInteger('deathsPerWeek', $this->Translate('Deaths per Week'), '', 5);
            $this->RegisterVariableInteger('recovered', $this->Translate('Recovered'), '', 6);
            $this->RegisterVariableFloat('weekIncidence', $this->Translate('7 Days Incidence'), '', 7);
            $this->RegisterVariableFloat('casesPer100k', $this->Translate('Cases per 100k'), '', 8);

            $this->RegisterVariableFloat('deltaCases', $this->Translate('Delta Cases'), '', 9);
            $this->RegisterVariableFloat('deltaDeaths', $this->Translate('Delta Deaths'), '', 10);
            $this->RegisterVariableFloat('deltaRecovered', $this->Translate('Delta Recovered'), '', 11);

            $this->RegisterVariableInteger('last_update', $this->Translate('Last Update'), '~UnixTimestamp', 15);

            if ($this->ReadPropertyString('province') != '-') {
                $this->updateDistricts($this->ReadPropertyString('province'));
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

            if (!empty($DistrictOptions)) {
                $Form['elements'][2]['options'] = $DistrictOptions;
            }
            return json_encode($Form);
        }

        public function updateDistrictStats()
        {
            if ($this->ReadPropertyString('district') != '-') {
                $dataJSON = file_get_contents($this->ReadPropertyString('URL') . '/districts/' . $this->ReadPropertyString('district'));
                $this->SendDebug('Data :: JSON', $dataJSON, 0);
                $JSON = json_decode($dataJSON, true);

                $data = $JSON['data'][$this->ReadPropertyString('district')];
                $meta = $JSON['meta'];

                if (array_key_exists('cases', $data)) {
                    $this->SetValue('population', $data['population']);
                    $this->SetValue('cases', $data['cases']);
                    $this->SetValue('deaths', $data['deaths']);
                    $this->SetValue('casesPerWeek', $data['casesPerWeek']);
                    $this->SetValue('deathsPerWeek', $data['deathsPerWeek']);
                    $this->SetValue('recovered', $data['recovered']);
                    $this->SetValue('weekIncidence', $data['weekIncidence']);
                    $this->SetValue('casesPer100k', $data['casesPer100k']);

                    $this->SetValue('deltaCases', $data['delta']['cases']);
                    $this->SetValue('deltaDeaths', $data['delta']['deaths']);
                    $this->SetValue('deltaRecovered', $data['delta']['recovered']);

                    $this->SetValue('last_update', date('U', strtotime($meta['lastUpdate'])));
                }
            }
        }

        private function updateDistricts($Province)
        {
            $dataJSON = file_get_contents($this->ReadPropertyString('URL') . '/districts');
            $this->SendDebug('Data :: JSON', $dataJSON, 0);

            $Districts = json_decode($dataJSON, true)['data'];

            $Options = [];

            $Option['caption'] = '-';
            $Option['value'] = '-';
            array_push($Options, $Option);

            $this->UpdateFormField('district', 'options', json_encode($Options));

            foreach ($Districts as $key => $District) {
                if ($District['stateAbbreviation'] == $this->ReadPropertyString('province')) {
                    $Option['caption'] = $District['name'] . ' (' . $District['county'] . ')';
                    $Option['value'] = strval($District['ags']);
                    array_push($Options, $Option);
                }
                $this->UpdateFormField('district', 'options', json_encode($Options));
                $this->SetBuffer('districtOptions', json_encode($Options));
            }
        }
    }
