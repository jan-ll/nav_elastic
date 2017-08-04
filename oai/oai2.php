<?php

include('../inc/config.php');
require_once('oai2server.php');

/**
 * Identifier settings. It needs to have proper values to reflect the settings of the data provider.
 * Is MUST be declared in this order
 *
 * - $identifyResponse['repositoryName'] : compulsory. A human readable name for the repository;
 * - $identifyResponse['baseURL'] : compulsory. The base URL of the repository;
 * - $identifyResponse['protocolVersion'] : compulsory. The version of the OAI-PMH supported by the repository;
 * - $identifyResponse['earliestDatestamp'] : compulsory. A UTCdatetime that is the guaranteed lower limit of all datestamps recording changes, modifications, or deletions in the repository. A repository must not use datestamps lower than the one specified by the content of the earliestDatestamp element. earliestDatestamp must be expressed at the finest granularity supported by the repository.
 * - $identifyResponse['deletedRecord'] : the manner in which the repository supports the notion of deleted records. Legitimate values are no ; transient ; persistent with meanings defined in the section on deletion.
 * - $identifyResponse['granularity'] : the finest harvesting granularity supported by the repository. The legitimate values are YYYY-MM-DD and YYYY-MM-DDThh:mm:ssZ with meanings as defined in ISO8601.
 *
 */
$identifyResponse = array();
$identifyResponse["repositoryName"] = 'BDPI OAI2 PMH';
$identifyResponse["baseURL"] = 'http://bdpife2.sibi.usp.br/bdpi/oai/oai2.php';
$identifyResponse["protocolVersion"] = '2.0';
$identifyResponse['adminEmail'] = 'tiago.murakami@dt.sibi.usp.br';
$identifyResponse["earliestDatestamp"] = '2016-01-01T12:00:00Z';
$identifyResponse["deletedRecord"] = 'no'; // How your repository handles deletions
                                           // no:             The repository does not maintain status about deletions.
                                           //                It MUST NOT reveal a deleted status.
                                           // persistent:    The repository persistently keeps track about deletions
                                           //                with no time limit. It MUST consistently reveal the status
                                           //                of a deleted record over time.
                                           // transient:   The repository does not guarantee that a list of deletions is
                                           //                maintained. It MAY reveal a deleted status for records.
$identifyResponse["granularity"] = 'YYYY-MM-DDThh:mm:ssZ';

/* unit tests ;) */
if (!isset($args)) {
    $args = $_GET;
}
if (!isset($uri)) {
    $uri = 'http://bdpife2.sibi.usp.br/bdpi/oai/oai2.php';
}
$oai2 = new OAI2Server($uri, $args, $identifyResponse,
    array(
        'ListMetadataFormats' =>
        function($identifier = '') {
            if (!empty($identifier) && $identifier != 'a.b.c') {
                throw new OAI2Exception('idDoesNotExist');
            }
            return
                array( 'oai_dc' => array('metadataPrefix'=>'oai_dc',
                                        'schema'=>'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
                                        'metadataNamespace'=>'http://www.openarchives.org/OAI/2.0/oai_dc/',
                                        'record_prefix'=>'dc',
                                        'record_namespace' => 'http://purl.org/dc/elements/1.1/'));
        },

        'ListSets' =>
        function($resumptionToken = '') {
            return
                array (
                    array('setSpec'=>'ECA', 'setName'=>'Escola de Comunicações e Artes',
                          'setDescription'=>
                              'Conjunto de Teses e Produção Científica da Escola de Comunicações e Artes'));
        },

        'ListRecords' =>
        function($metadataPrefix, $from = '', $until = '', $set = '', $count = false, $deliveredRecords = 0, $maxItems = 0) {
            global $client;
        
            if ($metadataPrefix != 'oai_dc') {
                throw new OAI2Exception('noRecordsMatch');
            }
            if (!empty($set)) {

                $query["query"]["query_string"]["query"] = '+unidadeUSP.keyword:"'.$set.'"';
                
                $filter[] = '{"term":{"unidadeUSP.keyword":"'.$set.'"}}';                 
            } 
    
            if (!empty($from)||!empty($until)){
                $filter[]= '{ "range" : { "datestamp" : { "gte" : "'.$from.'", "lt" :  "'.$until.'" } } }';
            }
    
            if (!empty($filter)){
                $filter_query = ''.implode(",", $filter).'';
            } else {
                $filter_query = "";
            }
            
            $query["query"]["query_string"]["query"] = "*";


            // $query = '{
            
            // "query": {    
            //     "bool": {
            //       "must": {
            //         "match_all": {}
            //       },
            //       "filter":[
            //         '.$filter_query.'        
            //         ]
            //       }
            // },
            //     "sort" : [
            //         {"_uid" : {"order" : "desc"}}
            //         ]
            //     }';               
            
            $params = [];
            $params["index"] = 'sibi';
            $params["type"] = 'producao';
            $params["size"] = $maxItems;
            $params["from"] = $deliveredRecords;
            $params["body"] = $query;

            $record = $client->search($params);
     
            if ($count) {
                return $record["hits"]["total"];
            }  
    
            $records = array();
            $now = date('Y-m-d-H:s');
            $i = 0;
            foreach ($record["hits"]["hits"] as $hit) {

                if (!empty($hit['_source']['name'])) {
                    $fields['dc:title'] = $hit['_source']['name'];
                } 

                if (!empty($hit['_source']['type'])) {    
                    $fields['dc:type'] = $hit['_source']['type'];
                }

                if (!empty($hit['_source']['language'][0])) {
                    $fields['dc:language'] = $hit['_source']['language'][0];
                }    

                if (!empty($hit['_source']['author'])) {    
                    foreach ($hit['_source']['author'] as $k => $authors){
                        $fields['dc:creator_'.$k] = $authors["person"]["name"];
                    }
                }

                if (!empty($hit['_source']['about'])) {
                    foreach ($hit['_source']['about'] as $k => $subject){
                        $fields['dc:subject_'.$k] = $subject;
                    }  
                }

                $records[$i]["identifier"] = $hit['_id'];
                $records[$i]["datestamp"] = $now;
                $records[$i]["set"] = 'all';
                $records[$i]["metadata"]["container_name"] = 'oai_dc:dc';
                $records[$i]["metadata"]["container_attributes"]["xmlns:oai_dc"] = "http://www.openarchives.org/OAI/2.0/oai_dc/";
                $records[$i]["metadata"]["container_attributes"]["xmlns:dc"] = "http://purl.org/dc/elements/1.1/";
                $records[$i]["metadata"]["container_attributes"]["xmlns:xsi"] = "http://www.w3.org/2001/XMLSchema-instance";
                $records[$i]["metadata"]["container_attributes"]["xsi:schemaLocation"] = 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd';                
                if (!empty($fields)) {
                    $records[$i]["metadata"]["fields"] = $fields;
                } else {
                    $records[$i]["metadata"]["fields"]['dc:title'] = "";
                    $records[$i]["metadata"]["fields"]['dc:type'] = "";
                    $records[$i]["metadata"]["fields"]['dc:language'] = "";
                    $records[$i]["metadata"]["fields"]['dc:creator'] = "";
                }
                $i++;
                unset($fields);
            }
            return $records;                              

        },

        'GetRecord' =>
        function($identifier, $metadataPrefix) {
            global $client;

//            if ($metadataPrefix != 'oai_dc') {
//                throw new OAI2Exception('noRecordsMatch');
//            }

            $params = [
                'index' => 'sibi',
                'type' => 'producao',
                'id' => ''.$identifier.''
            ];
            $record = $client->get($params);

            if (!empty($record['_source']['name'])) {
                $fields['dc:title'] = $record['_source']['name'];
            }
    
            $fields['dc:type'] = $record['_source']['type'];
    
            $fields['dc:language'] = $record['_source']['language'][0];    
            
            //foreach ($record['_source']['authors'] as $k => $authors){
            //    $fields['dc:creator_'.$k] = $authors;
            //}
    
            foreach ($record['_source']['about'] as $k => $subject){
                $fields['dc:subject_'.$k] = $subject;
            }

            if ($record["found"] === false) {
                throw new OAI2Exception('idDoesNotExist');
            }
            $now = date('Y-m-d-H:s');
            return array('identifier' => $record['_id'],
                         'datestamp' => $now,
                         'set' => 'all',
                         'metadata' => array(
                             'container_name' => 'oai_dc:dc',
                             'container_attributes' => array(
                                  'xmlns:oai_dc' => "http://www.openarchives.org/OAI/2.0/oai_dc/",
                                  'xmlns:dc' => "http://purl.org/dc/elements/1.1/",
                                  'xmlns:xsi' => "http://www.w3.org/2001/XMLSchema-instance",
                                  'xsi:schemaLocation' =>
                                  'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd'
                              ),
                              'fields' => $fields
                          ));
        },
    )
);

$response = $oai2->response();
if (isset($return)) {        
    return $response;
} else {
    $response->formatOutput = true;
    $response->preserveWhiteSpace = false;
    header('Content-Type: text/xml');
    echo $response->saveXML();
}
