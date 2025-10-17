<?php

// TODO opschonen en aanbieden?
// https://getkirby.com/docs/guide/plugins/best-practices

// TODO Aparte git repo of submodule van maken?

// zie https://api.setlist.fm/docs/1.0/resource__1.0_setlist__setlistId_.html

Kirby::plugin('mirthe/setlist', [
    'options' => [
        'cache' => true
    ],
    'tags' => [
        'setlist' => [
            'attr' =>[
                'show'
            ],
            'html' => function($tag) {
                
                $showid = $tag->show;
                $api_key = option('setlistfm.apiKey');
               
                $url = "https://api.setlist.fm/rest/1.0/setlist/" . $showid;
                $headers = array(
                    'x-api-key: '.$api_key, 
                    'Accept: application/json');
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_USERAGENT, kirby()->site()->title());
                $rawdata = curl_exec($ch);
                curl_close($ch);
                
                // print_r($rawdata);
                // exit();

                // TODO nog iets met lastFmEventId?
                
                $setlistjson = json_decode($rawdata,true);
                
                $mijnoutput = '<div class="well well--clean">' ."\n";
                $mijnoutput .= '<p><a href="'.$setlistjson['url'].'" title="Bekijken op Setlist.fm">'.$setlistjson['artist']['name']."</a>";
                $mijnoutput .= " in ".$setlistjson['venue']['name']." (".$setlistjson['venue']['city']['name'].")";
                $mijnoutput .= " op ". $setlistjson['eventDate'].":</p>\n";
                
                foreach ($setlistjson['sets'] as $sets) {
                    if( count($sets) > 0){
                        $mijnoutput .= "<ul class=\"songs\">";
                        for($i = 0; $i < count($sets); $i++) {
                            $liedjes = $sets[$i]['song'];
                            for($j = 0; $j < count($liedjes); $j++) {
                                if ($liedjes[$j]['name'] !== ''){
                                    $mijnoutput .= '<li>'. ($liedjes[$j]['name']) . "</li>";
                                }
                            }
                        }
                        $mijnoutput .= "</ul>\n";
                    }
                    else {
                        $mijnoutput .= "<p><em>De setlist is (nog) niet ingevoerd.</em></p>\n";
                    }
                }
                $mijnoutput .= "</div>\n";

                return $mijnoutput;
            }
        ]
    ]
]);

?>