<?php

declare(strict_types=1);

    class CountryStats extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyInteger('UpdateInterval', 10);
            $this->RegisterPropertyString('URL', 'https://api.corona-zahlen.org');
            $this->RegisterTimer('COVID_CountryUpdateStats', 0, 'COVID_updateCountryStats($_IPS[\'TARGET\']);');
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

            $this->RegisterVariableString('country', $this->Translate('Country'), '', 0);
            $this->SetValue('country', 'Deutschland');
            $this->RegisterVariableInteger('last_update', $this->Translate('Last Update'), '~UnixTimestamp', 15);

            $this->RegisterVariableInteger('cases', $this->Translate('Cases'), '', 1);
            $this->RegisterVariableInteger('deaths', $this->Translate('Deaths'), '', 2);
            $this->RegisterVariableInteger('recovered', $this->Translate('Recovered'), '', 3);
            $this->RegisterVariableFloat('weekIncidence', $this->Translate('7 Days Incidence'), '', 4);
            $this->RegisterVariableFloat('casesPer100k', $this->Translate('Cases per 100k'), '', 5);
            $this->RegisterVariableInteger('casesPerWeek', $this->Translate('Cases per Week'), '', 6);

            $this->RegisterVariableFloat('deltaCases', $this->Translate('Delta Cases'), '', 7);
            $this->RegisterVariableFloat('deltaDeaths', $this->Translate('Delta Deaths'), '', 8);
            $this->RegisterVariableFloat('deltaRecovered', $this->Translate('Delta Recovered'), '', 9);

            $this->RegisterVariableFloat('r', $this->Translate('R Value'), '', 10);
            $this->RegisterVariableFloat('r4Days', $this->Translate('R Value 4 Days'), '', 11);
            $this->RegisterVariableFloat('r7Days', $this->Translate('R Value 7 Days'), '', 12);

            $this->RegisterVariableFloat('hospitalizationCases7Days', $this->Translate('Hospitalization Cases 7 Days'), '', 13);
            $this->RegisterVariableFloat('hospitalizationIncidence7Days', $this->Translate('Hospitalization Incidence 7 Days'), '', 14);

            if (IPS_GetKernelRunlevel() == KR_READY) {
                $this->updateCountryStats();
            }

            $this->SetTimerInterval('COVID_CountryUpdateStats', $this->ReadPropertyInteger('UpdateInterval') * 1000);
        }

        public function updateCountryStats()
        {
            $dataJSON = file_get_contents($this->ReadPropertyString('URL') . '/germany');
            $data = json_decode($dataJSON, true);

            if (array_key_exists('cases', $data)) {
                $this->SetValue('cases', $data['cases']);
                $this->SetValue('deaths', $data['deaths']);
                $this->SetValue('recovered', $data['recovered']);
                $this->SetValue('weekIncidence', $data['weekIncidence']);
                $this->SetValue('casesPer100k', $data['casesPer100k']);
                $this->SetValue('casesPerWeek', $data['casesPerWeek']);

                $this->SetValue('deltaCases', $data['delta']['cases']);
                $this->SetValue('deltaDeaths', $data['delta']['deaths']);
                $this->SetValue('deltaRecovered', $data['delta']['recovered']);

                $this->SetValue('r', $data['r']['value']);
                $this->SetValue('r4Days', $data['r']['rValue4Days']['value']);
                $this->SetValue('r7Days', $data['r']['rValue7Days']['value']);

                $this->SetValue('hospitalizationCases7Days', $data['hospitalization']['cases7Days']);
                $this->SetValue('hospitalizationIncidence7Days', $data['hospitalization']['incidence7Days']);

                $this->SetValue('last_update', date('U', strtotime($data['meta']['lastUpdate'])));
            }
        }
    }
