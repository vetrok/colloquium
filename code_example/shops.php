<?php

class ModelModuleShops extends Model
{
    //Dir where all my templates stored
    protected $tplDir = '/admin/view/template/module/shops/';
    protected $shopsList = 'list.tpl';
    protected $pointers = 'pointers.tpl';
    protected $point = 'point.tpl';
    protected $input = 'frontend-input.tpl';

    /*
     * Returns api map script
     * */
    public function getMapScript()
    {
        return '<script src="http://api-maps.yandex.ru/2.0-stable/?load=package.full&lang=ru-RU" type="text/javascript"></script>';
    }

    /*
     * Returns path to .tpl file
     * */
    public function getShopFile($file)
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->tplDir . $file;
    }

    /*
     * Save array(default from POST request) to JSON string
     * */
    public function saveShops($post)
    {
        $post = $this->cleanArray($post);
        if(($post = json_encode($post)) !== false) {
            file_put_contents($this->getShopFile($this->shopsList), strip_tags($post));
            return true;
        }
        return false;
    }

    /*
     * Returns Array from JSON string saved on HD
     * */
    public function getShops()
    {
        if ($shops = $this->getTpl($this->getShopFile($this->shopsList))) {
            if (($shops = json_decode(strip_tags($shops))) !== false) {
                return $shops;
            }
        }
        return false;
    }

    /*
     * Returns config string for Maps settings + all pointers
     * using 2 templates - 1 main config template, 2 - template for All points
     * */
    public function getMapSettings()
    {
        //Main template access
        if ($layout = $this->getTpl($this->getShopFile($this->pointers))) {
            //Points template Access
            if ($point = $this->getTpl($this->getShopFile($this->point))) {
                //Put all points in config string
                if ($shops = $this->getShops()) {
                    $allPointers = '';
                    foreach ($shops as $val) {
                        $allPointers .= str_replace(
                            array(
                                '{{x}}',
                                '{{y}}',
                                '{{text}}',
                            ),
                            array(
                                $val->x,
                                $val->y,
                                $val->text,
                            ),
                            $point);
                    }
                    return str_replace("{{points}}", $allPointers, $layout);
                }
            }
        }
        return false;
    }

    /*
     * Check if file is readable , and if success return its content
     * */
    public function getTpl($addr)
    {
        if (is_readable($addr)) {
            return file_get_contents($addr);
        }
        return false;
    }

    /*
     * Cleaning array from empty variables
     * */
    public function cleanArray($arr)
    {
        $newArr = [];
        $counter = 0;
        foreach ($arr as $num => $point) {
            if (empty($point['x']) || empty($point['y'])) {
                continue;
            }
            $newArr[$counter] = $arr[$num];
            $counter++;
        }
        return $newArr;
    }

    /*
     * String to create input fields for shops coordinates in Admin part
     * */
    public function getInput()
    {
        if ($html = $this->getTpl($this->getShopFile($this->input))) {
            return $html;
        }
        return false;
    }

    /*
     * Drawing All shops as HTML inputs
     * */
    public function drawShops()
    {
        if (($tpl = $this->getInput()) and ($shops = $this->getShops())) {
            //Resulted HTML string
            $html = '';
            foreach ($shops as $num => $data) {
                $html .= str_replace(
                    array(
                        "{{num}}][x]",
                        "{{num}}][y]",
                        "{{num}}][text]"
                    ),
                    array(
                        $num . "][x] value='$data->x'",
                        $num . "][y] value='$data->y'",
                        $num . "][text] value='$data->text'"
                    ),
                    $tpl
                );
            }
            return $html;
        }
        return false;
    }
}