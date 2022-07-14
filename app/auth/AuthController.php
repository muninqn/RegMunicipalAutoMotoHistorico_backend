<?php

class AuthController
{

    public function __construct()
    {
    }

    public function getUserData($sessionkey, $appID = -1)
    {
        
        $dataUser = null;
        $url = WEBLOGIN . '/api/getUserByToken/';
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url . $sessionkey,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_SSL_VERIFYPEER => false,
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            
            $usuario = json_decode($response, true);
            if ($response && $usuario['error'] == null) {
                $datos_personales = $usuario['datosPersonales'];
                $dataUser["referenciaID"] = $datos_personales["referenciaID"];
                //! Posiblemente un array_filter sea mas limpio
                foreach ($usuario["apps"] as $numero => $app) {
                    if ($app['id'] == $appID) {
                        //if ($app['userProfiles'] != $app['standardType']) {
                        if (isset($app['userProfiles'])) {
                            $dataUser['perfilUsuario'] = $app['userProfiles'];
                        }
                        //}
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        
        return $dataUser;
    }
    public function getUsuarioDniGenero($token,$dni,$genero)
    {   
        $dataUser = null;
        // $url = WEBLOGIN . '/api/Renaper/';
        $url = 'https://weblogin.muninqn.gov.ar/api/Renaper/';
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url .$token."/".$genero.$dni,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_SSL_VERIFYPEER => false,
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            $dataUser = json_decode($response, true);
            // if ($response && $usuario['docInfo'] !== null) {
            //     $datos_personales = $usuario['datosPersonales'];
            //     $dataUser["referenciaID"] = $datos_personales["referenciaID"];
            //     //! Posiblemente un array_filter sea mas limpio
            //     foreach ($usuario["apps"] as $numero => $app) {
            //         if ($app['id'] == $appID) {
            //             //if ($app['userProfiles'] != $app['standardType']) {
            //             if (isset($app['userProfiles'])) {
            //                 $dataUser['perfilUsuario'] = $app['userProfiles'];
            //             }
            //             //}
            //             break;
            //         }
            //     }
            // }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $dataUser;
    }
}
