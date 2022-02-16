<?php

declare(strict_types=1);

    class ProvinceStats extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyBoolean('Active', true);
            $this->RegisterPropertyString('province', '-');
            $this->RegisterPropertyString('URL', 'https://api.corona-zahlen.org');

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

            $this->RegisterVariableInteger('last_update', $this->Translate('Last Update'), '~UnixTimestamp', 14);

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

            $this->RegisterVariableFloat('hospitalizationCases7Days', $this->Translate('Hospitalization Cases 7 Days'), '', 12);
            $this->RegisterVariableFloat('hospitalizationIncidence7Days', $this->Translate('Hospitalization Incidence 7 Days'), '', 13);

            if (IPS_GetKernelRunlevel() == KR_READY) {
                if (!$this->updateProvinceStats()) {
                    $this->SetStatus(200);
                    return;
                }
            }

            if ($this->ReadPropertyBoolean('Active')) {
                $this->SetTimerInterval('COVID_ProvinceUpdateStats', $this->ReadPropertyInteger('UpdateInterval') * 1000);
                $this->SetStatus(102);
            } else {
                $this->SetTimerInterval('COVID_ProvinceUpdateStats', 0);
                $this->SetStatus(104);
            }
        }

        public function updateProvinceStats()
        {
            if ($this->ReadPropertyString('province') != '-') {
                $dataJSON = file_get_contents($this->ReadPropertyString('URL') . '/states/' . $this->ReadPropertyString('province'));
                $this->SendDebug('Data :: JSON', $dataJSON, 0);
                $JSON = json_decode($dataJSON, true);

                if (array_key_exists('data', $JSON)) {
                    $data = $JSON['data'][$this->ReadPropertyString('province')];
                    if (array_key_exists('cases', $data)) {
                        $this->SetValue('Province', $data['name']);
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

                        $this->SetValue('hospitalizationCases7Days', $data['hospitalization']['cases7Days']);
                        $this->SetValue('hospitalizationIncidence7Days', $data['hospitalization']['incidence7Days']);

                        if (array_key_exists('meta', $JSON)) {
                            $meta = $JSON['meta'];
                            $this->SetValue('last_update', date('U', strtotime($meta['lastUpdate'])));
                        }
                    }
                } else {
                    return false;
                }
            }
        }
    }
