<?php Kirby::plugin('mirthe/setlist', [
    'options' => [
        'apiKey' => option('setlistfm.apiKey'),
        'cache' => true
    ],
    'translations' => [
        'nl' => [
            'mirthe.setlist.not-found' => 'Setlist niet gevonden',
            'mirthe.setlist.view-on-setlist' => 'Bekijken op Setlist.fm',
            'mirthe.setlist.no-setlist' => 'De setlist is (nog) niet ingevoerd.'
        ],
        'en' => [
            'mirthe.setlist.not-found' => 'Setlist not found',
            'mirthe.setlist.view-on-setlist' => 'View on Setlist.fm',
            'mirthe.setlist.no-setlist' => 'The setlist has not been entered yet.'
        ]
    ],
    'tags' => [
        'setlist' => [
            'attr' => [
                'show'
            ],
            'html' => function($tag) {
                $showid = $tag->show;
                $api_key = option('mirthe.setlist.apiKey');

                $cache = kirby()->cache('mirthe.setlist');
                $cacheKey = 'setlist-' . $showid;
                $setlistjson = $cache->get($cacheKey);
                $force = isset($_GET['forcecache']);

                if ($setlistjson === null || $force) {
                    $url = "https://api.setlist.fm/rest/1.0/setlist/" . $showid;
                    $headers = [
                        'x-api-key: ' . $api_key,
                        'Accept: application/json'
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_USERAGENT, kirby()->site()->title());
                    $rawdata = curl_exec($ch);
                    curl_close($ch);

                    $setlistjson = json_decode($rawdata, true);
                    $cache->set($cacheKey, $setlistjson, 604800);
                }

                if (empty($setlistjson) || !is_array($setlistjson) || isset($setlistjson['error'])) {
                    return '<div class="well well--clean"><div class="well-body">'.t('mirthe.setlist.not-found').'</div></div>';
                }

                $mijnoutput = '<div class="well well--clean">' . "\n";
                $mijnoutput .= '<p><a href="'.($setlistjson['url'] ?? '#').'" title="'.t('mirthe.setlist.view-on-setlist').'">'.htmlspecialchars($setlistjson['artist']['name'] ?? '', ENT_QUOTES)."</a>";
                $mijnoutput .= ' in '.htmlspecialchars($setlistjson['venue']['name'] ?? '', ENT_QUOTES).' ('.htmlspecialchars($setlistjson['venue']['city']['name'] ?? '', ENT_QUOTES).')';
                $mijnoutput .= ' op '.htmlspecialchars($setlistjson['eventDate'] ?? '', ENT_QUOTES).':</p>' . "\n";

                foreach ($setlistjson['sets'] as $sets) {
                    if (count($sets) > 0) {
                        $mijnoutput .= '<ul class="songs">';
                        for ($i = 0; $i < count($sets); $i++) {
                            $liedjes = $sets[$i]['song'] ?? [];
                            for ($j = 0; $j < count($liedjes); $j++) {
                                if (!empty($liedjes[$j]['name'])) {
                                    $mijnoutput .= '<li>'.htmlspecialchars($liedjes[$j]['name'], ENT_QUOTES)."</li>";
                                }
                            }
                        }
                        $mijnoutput .= "</ul>\n";
                    } else {
                        $mijnoutput .= "<p><em>" . t('mirthe.setlist.no-setlist') . "</em></p>\n";
                    }
                }

                $mijnoutput .= "</div>\n";
                return $mijnoutput;
            }
        ]
    ]
]);

