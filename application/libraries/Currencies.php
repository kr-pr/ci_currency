<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Currencies
{
    private $CI, $currency_table;

    public function __construct()
    {
        function fetch_rates($url, $codes)
        {
            print_r($url);
            $code_string = implode(',',$codes);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url."&currencies={$code_string}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            $result = json_decode(curl_exec($ch), true);
            curl_close($ch);
            if (array_key_exists("quotes", $result))
            {
                $rates_USD = $result["quotes"];
                $rates_EUR = array();
                foreach($codes as $code)
                {
                    $key = "USD".$code;
                    $rates_EUR[$code] = $rates_USD[$key]/$rates_USD["USDEUR"];
                }
                return $rates_EUR;
            }
            else return [];
        }

        $this->CI =& get_instance();
        $time_query = $this->CI->db->query("SELECT MIN(time_set) FROM currencies");
        $time_since_update = time()-get_object_vars($time_query->result()[0])['MIN(time_set)'];
        if ($time_since_update>432000)
        {
            $code_query = $this->CI->db->query("SELECT code FROM currencies");
            $codes = array_map(function($row)
            {
                return $row['code'];
            }, $code_query->result_array());

            $url = $this->CI->config->item('FX_API_URL');
            $key = $this->CI->config->item('FX_API_KEY');
            $rates = fetch_rates($url.'?access_key='.$key, $codes);
            if (!empty($rates))
            {
                foreach($rates as $key => $val)
                {
                    $this->CI->db->simple_query("UPDATE currencies SET rate={$val} WHERE code=\"{$key}\"");
                }
            }
            $now = time();
            $this->CI->db->simple_query("UPDATE currencies SET time_set = {$now}");
        }
        $currency_table_query = $this->CI->db->query("SELECT * FROM currencies");
        $this->currency_table = $currency_table_query->result_array();
    }

    private function current_currency()
    {
        $code = $this->CI->session->userdata('currency') ?: "EUR";
        $currency = array_values(array_filter($this->currency_table, function($row) use ($code)
        {
            return ($row['code']==$code);
        }
        ))[0];

        return $currency;
    }

    public function render_currency_toggle($id)
    {
        $switch_text = array(
            'en' => 'Change shown currency',
            'pt' => 'Mudar a moeda mostrada',
            'es' => 'Cambiar moneda mostrada',
            'fr' => 'Modifier la devise affichée'
        );
        $lang = $this->CI->session->userdata('site_lang') ?: 'en';
        $link_text = $switch_text[$lang];
        $html = "<a data-id=\"{$id}\" class=\"toggle-currency-selector\">{$link_text}?</a>";
        return $html;
    }

    public function render_price($base_price)
    {
        $current = $this->current_currency();
        $price = $base_price * $current['rate'];
        $symbol = $current['symbol'];

        $value = number_format($price / 100, 2);
        $html = "<p class=\"price\" data-price=\"{$base_price}\">{$value}{$symbol}</p>";

        return $html;
    }

    public function render_currency_selector($id)
    {
        $lang = $this->CI->session->userdata('site_lang') ?: 'en';
        $name = 'name_'.$lang;

        $current_code = $this->current_currency()['code'];

        $options = array_reduce($this->currency_table, function($html, $option) use ($current_code, $name) {
            
            $code = $option['code'];
            $rate = $option['rate'];
            $symbol = $option['symbol'];
            $cur_name = $option[$name];
            $selected = ($code==$current_code)?"selected":"";

            return $html."<option ".$selected." value=\"{$code}\" data-rate=\"{$rate}\"data-symbol=\"{$symbol}\">{$cur_name}</option>";
        });
        $wrapped_html = "<select data-id=\"{$id}\" style=\"display: none;\" class=\"currency\">{$options}</select>";

        return $wrapped_html;
    }

}