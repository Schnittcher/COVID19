<?php

declare(strict_types=1);

trait COVID19Helper
{
    protected function getDistricts($BL_ID)
    {
        $where = 'BL_ID = ' . $BL_ID;
        $outFields = 'GEN';
        return $this->DistrictRequest($where, $outFields);
    }

    protected function ProvinceRequest($where, $outFields)
    {
        $where = urlencode($where);
        $outFields = urlencode($outFields);
        $url = 'https://services7.arcgis.com/mOBPykOjAyBO2ZKk/ArcGIS/rest/services/Coronaf%C3%A4lle_in_den_Bundesl%C3%A4ndern/FeatureServer/0/query?where=' . $where . '&outFields=' . $outFields . '&returnGeometry=false&returnCentroid=false&featureEncoding=esriDefault&multipatchOption=xyFootprint&maxAllowableOffset=&geometryPrecision=&outSR=&datumTransformation=&applyVCSProjection=false&returnIdsOnly=false&returnUniqueIdsOnly=false&returnCountOnly=false&returnExtentOnly=false&returnQueryGeometry=false&returnDistinctValues=false&cacheHint=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&having=&resultOffset=&resultRecordCount=&returnZ=false&returnM=false&returnExceededLimitFeatures=false&quantizationParameters=&sqlFormat=none&f=pjson&token=';

        $this->SendDebug('Province Request URL', $url, 0);
        $dataJSON = file_get_contents($url);
        $data = json_decode($dataJSON, true);
        $this->SendDebug('API Result', $dataJSON, 0);
        return $data['features'];
    }

    protected function DistrictRequest($where, $outFields)
    {
        $where = urlencode($where);
        $outFields = urlencode($outFields);
        $url = 'https://services7.arcgis.com/mOBPykOjAyBO2ZKk/arcgis/rest/services/RKI_Landkreisdaten/FeatureServer/0/query?where=' . $where . '&outFields=' . $outFields . '&returnGeometry=false&returnCentroid=false&featureEncoding=esriDefault&multipatchOption=xyFootprint&maxAllowableOffset=&geometryPrecision=&outSR=&datumTransformation=&applyVCSProjection=false&returnIdsOnly=false&returnUniqueIdsOnly=false&returnCountOnly=false&returnExtentOnly=false&returnQueryGeometry=false&returnDistinctValues=false&cacheHint=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&having=&resultOffset=&resultRecordCount=&returnZ=false&returnM=false&returnExceededLimitFeatures=false&quantizationParameters=&sqlFormat=none&f=pjson&token=';

        $this->SendDebug('District Request URL', $url, 0);
        $dataJSON = file_get_contents($url);
        $data = json_decode($dataJSON, true);
        $this->SendDebug('API Result', $dataJSON, 0);
        return $data['features'];
    }
}