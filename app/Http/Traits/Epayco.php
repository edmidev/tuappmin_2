<?php

namespace App\Http\Traits;

trait Epayco
{
    public static function get_token($public_key = null, $private_key = null)
    {
        /** Enviamos los datos a la api de epayco para obtener un token */
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apify.epayco.co/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($public_key . ':' . $private_key)
            ),
        ));

        /** Obtenemos una respuesta */
        $response = curl_exec($curl);
        return json_decode($response);
    }

    public static function payment_card($token = null, $fields = null)
    {
        /** Enviamos los datos del pago */
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://apify.epayco.co/payment/process',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);

        /** Obtenemos una respuesta */
        curl_close($curl);
        return json_decode($response);
    }

    public static function payment_pse($token = null, $fields = null)
    {
        /** Enviamos los datos del pago */
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://apify.epayco.co/payment/process/pse',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);

        /** Obtenemos una respuesta */
        curl_close($curl);
        return json_decode($response);
    }

    public static function get_banks($token = null)
    {
        /** Obtenemos los bancos */
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apify.epayco.co/payment/pse/banks',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);

        /** Obtenemos una respuesta */
        curl_close($curl);
        return json_decode($response);
    }

    public static function confirm_transaction($token = null, $transactionID = null)
    {
        /** Obtenemos los bancos */
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://apify.epayco.co/payment/pse/transaction',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "transactionID":' . $transactionID . '
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);

        /** Obtenemos una respuesta */
        curl_close($curl);
        return json_decode($response);
    }
}