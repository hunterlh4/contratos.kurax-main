<?php

use Twilio\Rest\Client;

class whatsapp_api_helper
{
    protected $sid = "";
    protected $token = "";
    protected $priority = 10;
    protected $provider_name;
    protected $phone_number;

    public function __construct(string $provider_name, string $sid, string $token, $phone_number)
    {
        $this->provider_name = $provider_name;
        $this->sid = $sid;
        $this->token = $token;
        $this->phone_number = $phone_number;
    }


    function send_message($destination, $message)
    {
        $response = null;
        $provder_name = strtoupper($this->provider_name);
        if ($provder_name == 'ULTRAMSG') {
            $response = $this->send_ultramsg_message($destination, $message);
        } else if ($provder_name == 'TWILIO') {
            $response = $this->send_twilio_message($destination, $message);
        }
        return $response;
    }

    private function send_ultramsg_message(string $destination, string $message)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.ultramsg.com/" . $this->sid . "/messages/chat",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "token=" . $this->token . "&to=" . $destination . "&body=" . $message . "&priority=" . $this->priority . "&referenceId=",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        }

        return $response;
    }

    private function send_meta_message(string $destination, string $template)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v15.0/' . $this->sid . '/messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"messaging_product": "whatsapp", "to":  "' . $destination . '", "type": "template", "template": { "name": "' . $template . '", "language": { "code": "en_US" } } }');

        $headers = array();
        $headers[] = 'Authorization: Bearer ' . $this->token;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            return $error;
        }
        curl_close($ch);
        return $response;
    }


    function send_twilio_message(string $destination, string $message)
    {
        require_once '../sys/Twilio/autoload.php';

        $sid = $this->sid;
        $token = $this->token;
        $response =  [
            "error" => null,
            "sent" => true,
            "message" => null,
        ];
        /*$response = new stdClass();
        $response->sent = true;
        $response->message = '';*/

        try {
            $twilio = new Client($sid, $token);


           $twilio->messages->create("whatsapp:+" . $destination,
                array(
                    "from" => "whatsapp:+" . $this->phone_number,
                    "body" => $message
                )
            );

            //$response->message = "ok";
            $response["message"] = "ok";
        } catch (Exception $e) {
            //$response->error = $e->getMessage();
            //$response->sent = false;
            $response["error"] = $e->getMessage();
            $response["sent"] = false;
        }

        return json_encode($response);
    }
}