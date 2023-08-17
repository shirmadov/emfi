<?php

class ControllerAmo {

        CONST SUB_DOMAIN = 'sapashirmadov2501';

        CONST REDIRECT_URI = 'https://wagt.space/home/emfi/index.php';

        private $client_secret = 'ztJNjxXGRSDwkV5LHC4nrTRnPZAOruQ6Y6VVzvyh8am41phc33LbpPk9xdbVvfoK';

        private $client_id = '415a5ef1-c13c-4452-a076-ac05b2f054ce';

        private $code = 'def502007ea7fb2930a8fc170b0ca744721893a9587432f7617d2c8072c6d8f5398394295c9c897c3f98caebb67327145a7a02ecbd44d5c7b22257c63be0ba59fc2d02e76d7e9445e5d4c2c802723a4959301c695245f1d575b67a794d0f4ce859161c7037519a5e33f7db6446c9e52afc01ccfe1781bf64eb7a0742525f965f97b97b36e2bfd7342d13f89d8a513c2529ef269b692cef2e53e03204fe47961ec5c6c1536393eaa31d157588c912b994ce4a61169f81dde5777d3cbd6fc5a38fdc7b04b56c9ffadaceb73bc3828e10107535c4c07e4ea3bb2b8b3e05a30e390483733d8e7dec7bc4719267097e1f4c790ad0b6451727da660b7b089a20ae31dca618cc61b0ad15cf5797534c41ec9e36ead01626e2177d0b33d6afb8287548e49a8e34580bc6eaa390b548847c083b76cbbfe0e182c40a79fe56a09765d3b5baee843e2c777a750939d74d07c24ed0e11c88c363433fed934ed350ddbc4eeeaf79e1d994bb3ca6a9e41fcd382388cc69582c830f7f43f9d68ba58bca8c54e215463eb24f211c146a92f4432b505e84c9c6990cda156ac2b367f6176c9ea3b429b467497a5cf47bca4f74494059cf9b8ac89c368e06fe8f2d72e1ee73620158ab07faad73096d28a9fba2c32ec0aa0cecabf83ba748e98abb74fcf0a8d6c2eaa8ffc4f2df894da6484a99de9bd0db2b1201991cbb523c532286';

        private $token_file = 'tokens.txt';

        private $access_token = '';


        protected function sendRequest($method, $path, $params = null, $headers = null){

            try {
                $curl = curl_init();
                curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
                curl_setopt($curl,CURLOPT_URL, $path);
                curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl,CURLOPT_HEADER, false);
                curl_setopt($curl,CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($params));
                curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
                $output = curl_exec($curl);
                $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                return $output;
            }catch(\Exception $e){
                return $e->getMessage();
            }
        }

        public function authAmo(){
            $params = [
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type'    => 'authorization_code',
                'code'          => $this->code,
                'redirect_uri'  => self::REDIRECT_URI,
            ];

            $path = "https://".self::SUB_DOMAIN.".amocrm.ru/oauth2/access_token";

            $headers = [
                'Content-Type: application/json',
            ];

            $output = $this->sendRequest('POST', $path, $params, $headers );
            $response = json_decode($output, true);
            $arrParamsAmo = [
                "access_token"  => $response['access_token'],
                "refresh_token" => $response['refresh_token'],
                "token_type"    => $response['token_type'],
                "expires_in"    => $response['expires_in'],
                "endTokenTime"  => $response['expires_in'] + time(),
            ];

            $arrParamsAmo = json_encode($arrParamsAmo);
            $f = fopen('/home/emfi/includes/'.$this->token_file, 'w');
            fwrite($f, $arrParamsAmo);
            fclose($f);
        }


        public function accessAmo(){
            $dataToken = file_get_contents('/home/emfi/includes/'.$this->token_file );
            $dataToken = json_decode($dataToken, true);

            if ($dataToken["endTokenTime"] - 60 < time()) {

                $path = "https://".self::SUB_DOMAIN.".amocrm.ru/oauth2/access_token";

                $params = [
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $dataToken["refresh_token"],
                    'redirect_uri'  => self::REDIRECT_URI,
                ];

                $headers = [
                    'Content-Type: application/json',
                ];

                $output = $this->sendRequest('POST', $path, $params, $headers);
                $response = json_decode($output, true);

                $arrParamsAmo = [
                    "access_token"  => $response['access_token'],
                    "refresh_token" => $response['refresh_token'],
                    "token_type"    => $response['token_type'],
                    "expires_in"    => $response['expires_in'],
                    "endTokenTime"  => $response['expires_in'] + time(),
                ];

                $arrParamsAmo = json_encode($arrParamsAmo);
                $f = fopen('/home/emfi/includes/'.$this->token_file, 'w');
                fwrite($f, $arrParamsAmo);
                fclose($f);
                $this->access_token = $response['access_token'];
            } else {
                $this->access_token = $dataToken["access_token"];
            }

        }

        public function sendForm($name, $phone, $email, $target, $company){
            $this->accessAmo();
            $custom_field_id = 868787;
            $custom_field_value = 'тест';

            $ip = '45.9.43.228';
            $domain = 'wagt.space';
            $price = 0;
            $pipeline_id = 7141794;
            $user_amo = 0;

            $params = [
                [
                    "name" => $phone,
                    "price" => $price,
                    "responsible_user_id" => (int) $user_amo,
                    "pipeline_id" => (int) $pipeline_id,
                    "_embedded" => [
                        "metadata" => [
                            "category" => "forms",
                            "form_id" => 1,
                            "form_name" => "Форма на сайте",
                            "form_page" => $target,
                            "form_sent_at" => strtotime(date("Y-m-d H:i:s")),
                            "ip" => $ip,
                            "referer" => $domain
                        ],
                        "contacts" => [
                            [
                                "first_name" => $name,
                                "custom_fields_values" => [
                                    [
                                        "field_code" => "EMAIL",
                                        "values" => [
                                            [
                                                "enum_code" => "WORK",
                                                "value" => $email
                                            ]
                                        ]
                                    ],
                                    [
                                        "field_code" => "PHONE",
                                        "values" => [
                                            [
                                                "enum_code" => "WORK",
                                                "value" => $phone
                                            ]
                                        ]
                                    ],
                                    [
                                        "field_id" => (int) $custom_field_id,
                                        "values" => [
                                            [
                                                "value" => $custom_field_value
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "companies" => [
                            [
                                "name" => $company
                            ]
                        ]
                    ],
                    "custom_fields_values" => [
                        [
                            "field_code" => 'UTM_SOURCE',
                            "values" => [
                                [
                                    "value" => ''
                                ]
                            ]
                        ],
                        [
                            "field_code" => 'UTM_CONTENT',
                            "values" => [
                                [
                                    "value" => ''
                                ]
                            ]
                        ],
                        [
                            "field_code" => 'UTM_MEDIUM',
                            "values" => [
                                [
                                    "value" => ''
                                ]
                            ]
                        ],
                        [
                            "field_code" => 'UTM_CAMPAIGN',
                            "values" => [
                                [
                                    "value" => ''
                                ]
                            ]
                        ],
                        [
                            "field_code" => 'UTM_TERM',
                            "values" => [
                                [
                                    "value" => ''
                                ]
                            ]
                        ],
                        [
                            "field_code" => 'UTM_REFERRER',
                            "values" => [
                                [
                                    "value" => ''
                                ]
                            ]
                        ],
                    ],
                ]
            ];


            $path = "https://".self::SUB_DOMAIN.".amocrm.ru/api/v4/leads/complex";

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->access_token,
            ];

           $this->sendRequest('POST', $path, $params, $headers);

           return true;
        }



}
